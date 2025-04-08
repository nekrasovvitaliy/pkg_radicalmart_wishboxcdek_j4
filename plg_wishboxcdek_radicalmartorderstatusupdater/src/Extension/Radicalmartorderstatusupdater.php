<?php
/**
 * @copyright   (c) 2013-2024 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later
 *
 * @noinspection PhpMultipleClassDeclarationsInspection
 */
namespace Joomla\Plugin\Wishboxcdek\RadicalMartOrderStatusUpdater\Extension;

use Exception;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\RadicalMart\Administrator\Model\OrderModel;
use Joomla\Component\Wishboxcdek\Administrator\Table\StatusTable;
use Joomla\Component\Wishboxcdek\Administrator\Event\Model\OrderStatusUpdater\UpdateOrderStatusEvent;
use Joomla\Component\Wishboxcdek\Site\Event\Model\OrderStatusUpdater\GetCdekNumbersEvent;
use Joomla\Component\Wishboxradicalmartcdek\Administrator\Helper\WishboxradicalmartcdekHelper;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use WishboxCdekSDK2\Model\Response\Orders\OrdersGetResponse;
use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since 1.0.0
 *
 * @noinspection PhpUnused
 */
class RadicalMartOrderStatusUpdater extends CMSPlugin implements SubscriberInterface
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
	 * @since   1.2.0
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
		$db = $this->getDatabase();

		$subQuery = $db->createQuery()
			->select($db->qn('id'))
			->from($db->qn('#__radicalmart_shipping_methods'))
			->where($db->qn('plugin') . '=' . $db->q('wishboxcdek'));
		$db->setQuery($subQuery);

		$shippingMethodIds = $db->loadColumn();
		$oldOrderStatusIds = $this->getOLdOrderStatusIds();

		if (is_array($shippingMethodIds) && count($shippingMethodIds)
			&& is_array($oldOrderStatusIds) && count($oldOrderStatusIds))
		{
			$query = $db->createQuery()
				->select('JSON_UNQUOTE(JSON_EXTRACT(shipping, ' . $db->q('$.data.trackingNumber') . ')) AS trackingNumber')
				->from($db->qn('#__radicalmart_orders'))
				->where($db->qn('state') . ' = 1')
				->whereIn($db->qn('status'), $oldOrderStatusIds)
				->whereIn('JSON_EXTRACT(shipping, ' . $db->q('$.id') . ')', $shippingMethodIds)
				->where('JSON_EXTRACT(shipping, ' . $db->q('$.data.trackingNumber') . ')')
				->where('JSON_EXTRACT(shipping, ' . $db->q('$.data.trackingNumber') . ') <> ""');

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
	 * @param   Event  $event  Event
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function onWishboxCdekClientV2AfterGetOrderInfo(Event $event): void
	{
		/** @var OrdersGetResponse $response */
		$response = $event->getArgument('response');

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

		$app = $this->getApplication();

		$orderTable = $app->bootComponent('com_radicalmart')
			->getMVCFactory()
			->createTable('Order', 'Administrator');

		$orderId = WishboxradicalmartcdekHelper::getOrderIdByCdekNumber($cdekNumber);

		$orderTable->load($orderId);

		$newOrderStatusId = $this->getNewOrderStatusId($orderTable->status, $lastOrderCdekStatus->getCode());

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
