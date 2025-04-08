<?php
/**
 * @copyright   (c) 2013-2025 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later
 *
 * @noinspection PhpMultipleClassDeclarationsInspection
 */
namespace Joomla\Component\Wishboxradicalmartcdek\Administrator\Service;

use Error;
use Exception;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Component\RadicalMart\Administrator\Model\OrderModel;
use Joomla\Component\Wishboxcdek\SIte\Exception\OrdersPatchRequestErrorsException;
use Joomla\Component\Wishboxcdek\Site\Service\Registrator;
use Joomla\Component\Wishboxradicalmartcdek\Administrator\Event\Service\Order\AfterRegisterAllEvent;
use Joomla\Component\Wishboxradicalmartcdek\Administrator\Event\Service\Order\AfterRegisterEvent;
use Joomla\Component\Wishboxradicalmartcdek\Administrator\Event\Service\Order\BeforeRegisterAllEvent;
use Joomla\Component\Wishboxradicalmartcdek\Administrator\Exception\OrderServiceException;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;
use stdClass;
use WishboxCdekSDK2\Exception\Api\ErrorException;
use WishboxCdekSDK2\Exception\Api\ErrorsException;
use WishboxCdekSDK2\Exception\Api\RequestErrorException;

/**
 * @property Registry|null $orderShippingMethodParams
 *
 * @since 1.0.0
 */
class OrderService
{
	/**
	 * @param   stdClass  $order  Order
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function register(stdClass $order): void
	{
		try
		{
			$componentParams = ComponentHelper::getParams('com_wishboxradicalmartcdek');
			$errorStatusId = (int) $componentParams->get('wishboxradicalmartcdekorderregistrator.error_status_id', 0);
			$completedStatusId = (int) $componentParams->get('wishboxradicalmartcdekorderregistrator.completed_status_id', 0);

			if ($order->shipping->plugin != 'wishboxcdek')
			{
				return;
			}

			$app = Factory::getApplication();

			/** @var OrderModel $orderModel */
			$orderModel = $app->bootComponent('com_radicalmart')
				->getMVCFactory()
				->createModel('order', 'Administrator', ['ignore_request' => true]);

			try
			{
				$registratorDelegate = new RegistratorDelegate($order);

				$registrator = new Registrator($registratorDelegate);
				$registrator->register();

				$orderModel->updateStatus(
					$order->id,
					$completedStatusId
				);

				$app->enqueueMessage(
					Text::_(
						'PLG_RADICALMART_WISHBOXCDEKORDERREGISTRATOR_DELIVERY_SERVICE_REGISTRATION_COMPLETED_MESSAGE'
					),
					CMSApplicationInterface::MSG_NOTICE
				);

				$orderModel->addLog(
					$order->id,
					'delivery_service_registration_completed',
					[
						'action_text' => Text::_(
							'PLG_RADICALMART_WISHBOXCDEKORDERREGISTRATOR_DELIVERY_SERVICE_REGISTRATION_COMPLETED_MESSAGE'
						),
						'message' => ''
					]
				);
			}
			catch (ErrorException | ErrorsException | RequestErrorException $e)
			{
				$app->enqueueMessage(
					__LINE__ . Text::_(
						'PLG_RADICALMART_WISHBOXCDEKORDERREGISTRATOR_DELIVERY_SERVICE_REGISTRATION_ERROR_MESSAGE'
					) . ': ' . $e->getMessage(),
					CMSApplicationInterface::MSG_WARNING
				);

				$orderModel->addLog(
					$order->id,
					'delivery_service_registration_error',
					[
						'action_text' => Text::_(
							'PLG_RADICALMART_WISHBOXCDEKORDERREGISTRATOR_DELIVERY_SERVICE_REGISTRATION_ERROR_MESSAGE'
						),
						'message' => $e->getMessage()
					]
				);

				$orderModel->updateStatus(
					$order->id,
					$errorStatusId
				);
			}
			catch (OrderServiceException $e)
			{
				$app->enqueueMessage(
					__LINE__ . Text::_(
						'PLG_RADICALMART_WISHBOXCDEKORDERREGISTRATOR_DELIVERY_SERVICE_REGISTRATION_ERROR_MESSAGE'
					) . ': ' . $e->getMessage(),
					CMSApplicationInterface::MSG_WARNING
				);

				$orderModel->addLog(
					$order->id,
					'delivery_service_registration_error',
					[
						'action_text' => Text::_(
							'PLG_RADICALMART_WISHBOXCDEKORDERREGISTRATOR_DELIVERY_SERVICE_REGISTRATION_ERROR_MESSAGE'
						),
						'message' => $e->getMessage()
					]
				);

				$orderModel->updateStatus(
					$order->id,
					$errorStatusId
				);
			}
			catch (OrdersPatchRequestErrorsException $e)
			{
				$app->enqueueMessage(
					__FILE__
					. __LINE__
					. "<br />"
					. Text::_(
						'PLG_RADICALMART_WISHBOXCDEKORDERREGISTRATOR_DELIVERY_SERVICE_REGISTRATION_ERROR_MESSAGE'
					) . ': ' . $e->getMessage(),
					CMSApplicationInterface::MSG_WARNING
				);

				$orderModel->addLog(
					$order->id,
					'delivery_service_registration_error',
					[
						'action_text' => Text::_(
							'PLG_RADICALMART_WISHBOXCDEKORDERREGISTRATOR_DELIVERY_SERVICE_REGISTRATION_ERROR_MESSAGE'
						),
						'message' => $e->getMessage()
					]
				);

				$orderModel->updateStatus(
					$order->id,
					$errorStatusId
				);
			}
			catch (Exception | Error $e)
			{
				$app->enqueueMessage(
					$e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine(),
					CMSApplicationInterface::MSG_WARNING
				);

				$orderModel->addLog(
					$order->id,
					'delivery_service_registration_error',
					[
						'action_text'   => 'Exception or Error',
						'message'       => $e->getMessage()
					]
				);

				$orderModel->updateStatus(
					$order->id,
					$errorStatusId
				);
			}
		}
		catch (Exception | Error $e)
		{
			throw $e;
		}
	}


	/**
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function registerAll(): void
	{
		$app = Factory::getApplication();
		$orderIds = $this->getOrderIds();

		if (count($orderIds))
		{
			/** @var BeforeRegisterAllEvent $beforeRegisterAllEvent */
			$beforeRegisterAllEvent = AbstractEvent::create(
				'onWishboxRadicalMartCdekOrderServiceBeforeRegisterAll',
				[
					'eventClass' => BeforeRegisterAllEvent::class,
					'subject'    => $this,
					'orderIds'   => $orderIds,
				]
			);

			$app->getDispatcher()->dispatch($beforeRegisterAllEvent->getName(), $beforeRegisterAllEvent);

			/** @var OrderModel $orderModel */
			$orderModel = $app->bootComponent('com_radicalmart')
				->getMVCFactory()
				->createModel('order', 'Administrator', ['ignore_request' => true]);

			foreach ($orderIds as $k => $orderId)
			{
				$order = $orderModel->getItem($orderId);
				$this->register($order);

				/** @var AfterRegisterEvent $afterRegisterEvent */
				$afterRegisterEvent = AbstractEvent::create(
					'onWishboxRadicalMartCdekOrderServiceAfterRegister',
					[
						'subject'    => $this,
						'key'        => $k,
						'order'      => $order,
						'eventClass' => AfterRegisterEvent::class,
					]
				);

				$app->getDispatcher()->dispatch($afterRegisterEvent->getName(), $afterRegisterEvent);
			}

			/** @var AfterRegisterAllEvent $afterRegisterAllEvent */
			$afterRegisterAllEvent = AbstractEvent::create(
				'onWishboxRadicalMartCdekOrderServiceAfterRegisterAll',
				[
					'subject'    => $this,
					'orderIds'   => $orderIds,
					'eventClass' => AfterRegisterAllEvent::class,
				]
			);

			$app->getDispatcher()->dispatch($afterRegisterAllEvent->getName(), $afterRegisterAllEvent);
		}
	}

	/**
	 * @return integer[]
	 *
	 * @since 1.0.0
	 */
	private function getOrderIds(): array
	{
		$componentParams = ComponentHelper::getParams('com_wishboxradicalmartcdek');
		$allowedStatusIds = [
			(int) $componentParams->get('wishboxradicalmartcdekorderregistrator.ready_status_id', 0),
			(int) $componentParams->get('wishboxradicalmartcdekorderregistrator.error_status_id', 0),
		];

		/** @var DatabaseInterface $db */
		$db = Factory::getContainer()->get(DatabaseInterface::class);

		$query = $db->createQuery()
			->select('id')
			->from('#__radicalmart_orders')
			->whereIn('status', $allowedStatusIds)
			->where('state=1');
		$db->setQuery($query);

		return $db->loadColumn();
	}
}
