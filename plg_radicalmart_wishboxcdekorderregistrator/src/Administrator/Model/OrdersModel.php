<?php
/**
 * @copyright   (c) 2013-2025 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later
 */
namespace Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistrator\Administrator\Model;

use Error;
use Exception;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Component\RadicalMart\Administrator\Model\OrderModel as RadicalMartOrderModel;
use Joomla\Component\WishboxCdek\Site\Model\RegistratorModel;
use Joomla\Component\WishboxRadicalMartCdek\Administrator\Exception\OrderServiceException;
use Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistrator\Event\Model\Orders\AfterRegisterAllEvent;
use Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistrator\Event\Model\Orders\AfterRegisterEvent;
use Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistrator\Event\Model\Orders\BeforeRegisterAllEvent;
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
class OrdersModel extends BaseDatabaseModel
{
	/**
	 * The URL option for the component.
	 *
	 * @var    string
	 *
	 * @since  1.0.0
	 */
	protected $option = 'com_wishboxradicalmartcdek';

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

			/** @var RadicalMartOrderModel $orderModel */
			$orderModel = $app->bootComponent('com_radicalmart')
				->getMVCFactory()
				->createModel('order', 'Administrator', ['ignore_request' => true]);

			try
			{
				/** @var RegistratorDelegateModel $registratorDelegateModel */
				$registratorDelegateModel = $app->bootPlugin('wishboxcdekorderregistrator', 'radicalmart')
					->getMVCFactory()
					->createModel('registratorDelegate', 'Administrator');

				$registratorDelegateModel->setOrder($order);

				/** @var RegistratorModel $registratorModel */
				$registratorModel = $app->bootComponent('wishboxcdek')
					->getMVCFactory()
					->createModel('registrator', 'Site');

				$registratorModel->register($registratorDelegateModel);

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
				'onWishboxRadicalMartCdekOrderBeforeRegisterAll',
				[
					'eventClass' => BeforeRegisterAllEvent::class,
					'subject'    => $this,
					'orderIds'   => $orderIds,
				]
			);

			$app->getDispatcher()->dispatch($beforeRegisterAllEvent->getName(), $beforeRegisterAllEvent);

			/** @var RadicalMartOrderModel $orderModel */
			$orderModel = $app->bootComponent('com_radicalmart')
				->getMVCFactory()
				->createModel('order', 'Administrator', ['ignore_request' => true]);

			foreach ($orderIds as $k => $orderId)
			{
				$order = $orderModel->getItem($orderId);
				$this->register($order);

				/** @var AfterRegisterEvent $afterRegisterEvent */
				$afterRegisterEvent = AbstractEvent::create(
					'onWishboxRadicalMartCdekOrderAfterRegister',
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
				'onWishboxRadicalMartCdekOrderAfterRegisterAll',
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

		$db = $this->getDatabase();

		$query = $db->createQuery()
			->select('id')
			->from('#__radicalmart_orders')
			->whereIn('status', $allowedStatusIds)
			->where('state=1');
		$db->setQuery($query);

		return $db->loadColumn();
	}
}
