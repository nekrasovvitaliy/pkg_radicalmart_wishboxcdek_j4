<?php
/**
 * @copyright   (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license         GNU General Public License version 2 or later
 */

namespace Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistration\Service\OrderRegistration;

use ErrorException;
use Exception;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Component\RadicalMart\Administrator\Extension\RadicalMartComponent;
use Joomla\Component\RadicalMart\Administrator\Model\OrderModel as RadicalMartOrderModel;
use Joomla\Component\WishboxRadicalMartCdek\Administrator\Exception\OrderServiceException;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistration\Event\Service\Order\AfterRegisterAllEvent;
use Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistration\Event\Service\Order\AfterRegisterEvent;
use Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistration\Event\Service\Order\BeforeRegisterAllEvent;
use Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistration\Service\OrderRegistration\Adapter\OrderRegistrationAdapterAwareInterface;
use Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistration\Service\OrderRegistration\Adapter\OrderRegistrationAdapterAwareTrait;
use stdClass;
use Throwable;
use WishboxCdek\Exception\CdekException;
use WishboxCdekLibrary\Exception\Order\OrderRequestErrorsException;
use WishboxCdekLibrary\Service\Registration\OrderRegistrationServiceAwareInterface;
use WishboxCdekLibrary\Service\Registration\OrderRegistrationServiceAwareTrait;

/**
 * @since 1.0.0
 */
class RadicalMartOrderRegistrationService implements OrderRegistrationAdapterAwareInterface, OrderRegistrationServiceAwareInterface
{
	use DatabaseAwareTrait;
	use OrderRegistrationAdapterAwareTrait;
	use OrderRegistrationServiceAwareTrait;

	/**
	 * @param   stdClass  $order  Order
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function registerSavedOrder(stdClass $order): void
	{
		if (!$this->isWishboxCdekOrder($order) || !$this->hasRegistrationStatus($order))
		{
			return;
		}

		$this->register($order);
	}

	/**
	 * @param   stdClass  $order  Order
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function register(stdClass $order): void
	{
		$app = Factory::getApplication();

		/** @var RadicalMartComponent $radicalMartComponent */
		$radicalMartComponent = $app->bootComponent('com_radicalmart');

		$componentParams   = ComponentHelper::getParams('com_wishboxradicalmartcdek');
		$errorStatusId     = (int) $componentParams->get('wishboxradicalmartcdekorderregistration.error_status_id', 0);
		$completedStatusId = (int) $componentParams->get('wishboxradicalmartcdekorderregistration.completed_status_id', 0);

		if (!$this->isWishboxCdekOrder($order))
		{
			return;
		}

		/** @var RadicalMartOrderModel $orderModel */
		$orderModel = $radicalMartComponent->getMVCFactory()
			->createModel(
				'order',
				'Administrator',
				[
					'ignore_request' => true
				]
			);

		try
		{
			$orderRegistrationAdapter = $this->getOrderRegistrationAdapter();
			$orderRegistrationAdapter->setOrder($order);

			$this->getOrderRegistrationService()->register($orderRegistrationAdapter);

			$orderModel->updateStatus(
				$order->id,
				$completedStatusId
			);

			$app->enqueueMessage(
				Text::_(
					'PLG_RADICALMART_WISHBOXCDEKORDERREGISTRATION_DELIVERY_SERVICE_REGISTRATION_COMPLETED_MESSAGE'
				),
				CMSApplicationInterface::MSG_NOTICE
			);

			$orderModel->addLog(
				$order->id,
				'delivery_service_registration_completed',
				[
					'action_text' => Text::_(
						'PLG_RADICALMART_WISHBOXCDEKORDERREGISTRATION_DELIVERY_SERVICE_REGISTRATION_COMPLETED_MESSAGE'
					),
					'message'     => ''
				]
			);
		}
		catch (ErrorException|OrderRequestErrorsException|OrderServiceException|CdekException $e)
		{
			$this->handleRegistrationError($app, $orderModel, $order, $e, $errorStatusId);
		}
		catch (Throwable $e)
		{
			$this->handleRegistrationError(
				$app,
				$orderModel,
				$order,
				$e,
				$errorStatusId,
				'Exception or Error',
				$e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine()
			);
		}
	}

	/**
	 * @param   CMSApplicationInterface  $app            Application
	 * @param   RadicalMartOrderModel    $orderModel     Order model
	 * @param   stdClass                 $order          Order
	 * @param   Throwable                $exception      Exception
	 * @param   integer                  $errorStatusId  Error status id
	 * @param   string|null              $actionText     Action text
	 * @param   string|null              $message        Message
	 *
	 * @return void
	 *
	 * @throws Exception
	 * @since 1.0.0
	 */
	private function handleRegistrationError(
		CMSApplicationInterface $app,
		RadicalMartOrderModel $orderModel,
		stdClass $order,
		Throwable $exception,
		int $errorStatusId,
		?string $actionText = null,
		?string $message = null
	): void
	{
		$actionText ??= Text::_(
			'PLG_RADICALMART_WISHBOXCDEKORDERREGISTRATION_DELIVERY_SERVICE_REGISTRATION_ERROR_MESSAGE'
		);
		$message    ??= $exception->getMessage();

		$app->enqueueMessage(
			$actionText . ': ' . $message,
			CMSApplicationInterface::MSG_WARNING
		);

		$orderModel->addLog(
			$order->id,
			'delivery_service_registration_error',
			[
				'action_text' => $actionText,
				'message'     => $exception->getMessage()
			]
		);

		$orderModel->updateStatus(
			$order->id,
			$errorStatusId
		);
	}

	/**
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function registerAll(): void
	{
		$app = Factory::getApplication();

		/** @var RadicalMartComponent $radicalMartComponent */
		$radicalMartComponent = $app->bootComponent('com_radicalmart');

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

			$app->getDispatcher()
				->dispatch($beforeRegisterAllEvent->getName(), $beforeRegisterAllEvent);

			/** @var RadicalMartOrderModel $orderModel */
			$orderModel = $radicalMartComponent->getMVCFactory()
				->createModel(
					'order',
					'Administrator',
					[
						'ignore_request' => true
					]
				);

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
		$componentParams  = ComponentHelper::getParams('com_wishboxradicalmartcdek');
		$allowedStatusIds = [
			(int) $componentParams->get('wishboxradicalmartcdekorderregistration.ready_status_id', 0),
			(int) $componentParams->get('wishboxradicalmartcdekorderregistration.error_status_id', 0),
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

	/**
	 * @param   stdClass  $order  Order
	 *
	 * @return boolean
	 *
	 * @since 1.0.0
	 */
	private function isWishboxCdekOrder(stdClass $order): bool
	{
		return ($order->shipping->plugin ?? '') == 'wishboxcdek';
	}

	/**
	 * @param   stdClass  $order  Order
	 *
	 * @return boolean
	 *
	 * @since 1.0.0
	 */
	private function hasRegistrationStatus(stdClass $order): bool
	{
		$componentParams = ComponentHelper::getParams('com_wishboxradicalmartcdek');
		$allowedStatusIds = [
			(int) $componentParams->get('wishboxradicalmartcdekorderregistration.ready_status_id', 0),
			(int) $componentParams->get('wishboxradicalmartcdekorderregistration.error_status_id', 0),
			(int) $componentParams->get('wishboxradicalmartcdekorderregistration.completed_status_id', 0),
		];

		return in_array((int) ($order->status->id ?? 0), $allowedStatusIds, true);
	}
}
