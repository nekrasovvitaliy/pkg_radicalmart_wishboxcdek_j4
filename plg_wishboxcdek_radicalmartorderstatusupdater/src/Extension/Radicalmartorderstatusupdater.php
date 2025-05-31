<?php
/**
 * @copyright   (c) 2013-2025 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later
 *
 * @noinspection PhpMultipleClassDeclarationsInspection
 */
namespace Joomla\Plugin\WishboxCdek\RadicalMartOrderStatusUpdater\Extension;

use Exception;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\RadicalMart\Administrator\Model\OrderModel;
use Joomla\Component\WishboxCdek\Administrator\Table\StatusTable;
use Joomla\Component\WishboxCdek\Site\Event\Model\OrderStatusUpdater\UpdateOrderStatusEvent;
use Joomla\Component\WishboxCdek\Site\Event\Model\OrderStatusUpdater\GetCdekNumbersEvent;
use Joomla\Component\WishboxCdek\Site\Event\Model\Webhook\HandleOrderStatusEvent;
use Joomla\Component\WishboxRadicalMartCdek\Administrator\Helper\WishboxRadicalMartCdekHelper;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Event\SubscriberInterface;
use WishboxCdekSDK2\Event\AfterCalculateTariffListEvent;
use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since 1.0.0
 *
 * @noinspection PhpUnused
 */
final class RadicalMartOrderStatusUpdater extends CMSPlugin implements DatabaseAwareInterface, SubscriberInterface
{
	use DatabaseAwareTrait;

	/**
	 * Load the language file on instantiation.
	 *
	 * @var boolean
	 *
	 * @since  1.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Returns an array of events this subscriber will listen to.
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onWishboxCdekOrderStatusUpdaterGetCdekNumbers'     => 'onWishboxCdekOrderStatusUpdaterGetCdekNumbers',
			'onWishboxCdekClientV2AfterGetOrderInfo'            => 'onWishboxCdekClientV2AfterGetOrderInfo',
			'onWishboxCdekOrderStatusUpdaterUpdateOrderStatus'  => 'onWishboxCdekOrderStatusUpdaterUpdateOrderStatus'
		];
	}

	/**
	 * @param   GetCdekNumbersEvent  $event  Event
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function onWishboxCdekOrderStatusUpdaterGetCdekNumbers(GetCdekNumbersEvent $event): void
	{
		$component = $event->getComponent();

		if (!in_array($component, ['', 'radicalmart']))
		{
			return;
		}

		$orderIds = $event->getOrderIds();

		$db = $this->getDatabase();

		$subQuery = $db->createQuery()
			->select($db->qn('id'))
			->from($db->qn('#__radicalmart_shipping_methods'))
			->where($db->qn('plugin') . '=' . $db->q('wishboxcdek'));
		$db->setQuery($subQuery);

		$shippingMethodIds = $db->loadColumn();
		$oldOrderStatusIds = $this->getOLdOrderStatusIds();

		if (is_array($shippingMethodIds) && count($shippingMethodIds)
			&& count($oldOrderStatusIds))
		{
			$query = $db->createQuery()
				->select('JSON_UNQUOTE(JSON_EXTRACT(shipping, ' . $db->q('$.data.tracking_number') . ')) AS trackingNumber')
				->from($db->qn('#__radicalmart_orders'))
				->where($db->qn('state') . ' = 1')
				->whereIn($db->qn('status'), $oldOrderStatusIds)
				->whereIn('JSON_EXTRACT(shipping, ' . $db->q('$.id') . ')', $shippingMethodIds)
				->where('JSON_EXTRACT(shipping, ' . $db->q('$.data.tracking_number') . ')')
				->where('JSON_EXTRACT(shipping, ' . $db->q('$.data.tracking_number') . ') <> ""');

			if (count($orderIds))
			{
				$query->whereIn($db->qn('id'), $orderIds);
			}

			$db->setQuery($query);

			$cdekNumbers = $db->loadColumn();

			if (is_array($cdekNumbers) && count($cdekNumbers))
			{
				foreach ($cdekNumbers as $cdekNumber)
				{
					$event->addResult($cdekNumber);
				}
			}
		}
	}

	/**
	 * @param   AfterCalculateTariffListEvent  $event  Event
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function onWishboxCdekClientV2AfterGetOrderInfo(AfterCalculateTariffListEvent $event): void
	{
		$response = $event->getResponse();

		$app = $this->getApplication();

		$statusResponses = $response->getEntity()->getStatuses();

		foreach ($statusResponses as $statusResponse)
		{
			/** @var StatusTable $statusTable */
			$statusTable = $app->bootComponent('com_wishboxcdek')
				->getMVCFactory()
				->createTable('Status');

			if (!$statusTable->load(['code' => $statusResponse->getCode()]))
			{
				$statusTable->code = $statusResponse->getCode();
				$statusTable->name = $statusResponse->getName();

				if (!$statusTable->store())
				{
					throw new Exception($statusTable->getError(), 500);
				}
			}
		}
	}

	/**
	 * @param   UpdateOrderStatusEvent  $event  Event
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function onWishboxCdekOrderStatusUpdaterUpdateOrderStatus(UpdateOrderStatusEvent $event): void
	{
		$cdekNumber = $event->getCdekNumber();
		$orderCdekStatuses = $event->getOrderCdekStatuses();
		$lastOrderCdekStatus = $orderCdekStatuses[array_key_last($orderCdekStatuses)];

		$this->updateOrderStatus($cdekNumber, $lastOrderCdekStatus->getCode());
	}

	/**
	 * @param   HandleOrderStatusEvent  $event  Event
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function onWishboxCdekWebhookHandleOrderStatus(HandleOrderStatusEvent $event): void
	{
		$data = $event->getData();

		$this->updateOrderStatus($data['attributes']['cdek_number'], $data['attributes']['code']);
	}

	/**
	 * @param   string  $cdekNumber               Cdek number
	 * @param   string  $lastOrderCdekStatusCode  Code of the latest order status in Cdek
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	protected function updateOrderStatus(string $cdekNumber, string $lastOrderCdekStatusCode): void
	{
		$app = $this->getApplication();

		$orderTable = $app->bootComponent('com_radicalmart')
			->getMVCFactory()
			->createTable('Order', 'Administrator');

		$orderId = WishboxRadicalMartCdekHelper::getOrderIdByCdekNumber($cdekNumber);
		$orderTable->load($orderId);
		$newOrderStatusId = $this->getNewOrderStatusId($orderTable->status, $lastOrderCdekStatusCode);

		if ($newOrderStatusId && $newOrderStatusId != $orderTable->status)
		{
			/** @var OrderModel $orderModel */
			$orderModel = $app->bootComponent('com_radicalmart')
				->getMVCFactory()
				->createModel('order', 'Administrator', ['ignore_request' => true]);

			$orderModel->updateStatus(
				$orderTable->id,
				$newOrderStatusId,
				false,
				null,
				'Automatically changed in accordance with the status in Cdek'
			);
		}
	}

	/**
	 * @return integer[]
	 *
	 * @since 1.0.0
	 */
	private function getOldOrderStatusIds(): array
	{
		$componentParams = ComponentHelper::getParams('com_wishboxradicalmartcdek');
		$statuses = $componentParams->get('wishboxradicalmartcdekorderstatusupdater.statuses');

		$oldOrderStatusIds = [];

		foreach ($statuses as $status)
		{
			$oldOrderStatusIds[] = $status->old_status_id; // phpcs:ignore;
		}

		return array_unique($oldOrderStatusIds);
	}

	/**
	 * @param   integer  $oldStatusId          Old status id
	 * @param   string   $orderCdekStatusCode  Order Cdek status code
	 *
	 * @return integer|null
	 *
	 * @since 1.0.0
	 */
	private function getNewOrderStatusId(int $oldStatusId, string $orderCdekStatusCode): ?int
	{
		$componentParams = ComponentHelper::getParams('com_wishboxradicalmartcdek');
		$statuses = $componentParams->get('wishboxradicalmartcdekorderstatusupdater.statuses');

		foreach ($statuses as $status)
		{
			if ($oldStatusId == $status->old_status_id && $orderCdekStatusCode == $status->wishboxcdek_status_code) // phpcs:ignore
			{
				return $status->new_status_id; // phpcs:ignore
			}
		}

		return null;
	}
}
