<?php
/**
 * @copyright   (c) 2013-2025 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later
 */
namespace Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistrator\Extension;

use Exception;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Factory\MVCFactoryServiceTrait;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistrator\Administrator\Model\OrdersModel;
use stdClass;
use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since 1.0.0
 *
 * @noinspection PhpUnused
 */
final class WishboxCdekOrderRegistrator extends CMSPlugin implements SubscriberInterface
{
	use MVCFactoryServiceTrait;

	/**
	 * Load the language file on instantiation.
	 *
	 * @var boolean
	 *
	 * @since  1.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Enable on RadicalMart
	 *
	 * @var  boolean
	 *
	 * @since  1.0.0
	 */
	public bool $radicalmart = true;

	/**
	 * Enable on RadicalMartExpress
	 *
	 * @var  boolean
	 *
	 * @since  1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public bool $radicalmart_express = true; // phpcs:ignore

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
			'onBeforeRender'                            => 'onBeforeRender',
			'onRadicalMartBeforeOrderSave'              => 'onBeforeOrderSave',
			'onRadicalMartAfterOrderSave'               => 'onAfterOrderSave',
			'onRadicalMartAfterChangeOrderStatus'       => 'onAfterChangeOrderStatus'
		];
	}

	/**
	 * @param   string            $context   Context
	 * @param   array             $data      Data
	 * @param   array             $formData  Form data
	 * @param   object|array      $products  Products
	 * @param   stdClass          $shipping  Shipping
	 * @param   stdClass|boolean  $payment   Payment
	 * @param   array             $currency  Currency
	 * @param   boolean           $isNew     Is new
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function onBeforeOrderSave(
		string $context,
		array &$data,
		array $formData,
		object|array $products,
		stdClass $shipping,
		stdClass|bool $payment,
		array $currency,
		bool $isNew
	): void
	{

	}

	/**
	 * @param   string    $context   Context
	 * @param   array     $formData  Form data
	 * @param   array     $data      Data
	 * @param   stdClass  $order     Order
	 * @param   boolean   $isNew     Is new
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function onAfterOrderSave(
		string $context,
		array $formData,
		array $data,
		stdClass $order,
		bool $isNew
	): void
	{
		if ($order->shipping->plugin != 'wishboxcdek')
		{
			return;
		}

		$app = $this->getApplication();
		$componentParams = ComponentHelper::getParams('com_wishboxradicalmartcdek');

		$allowedStatusIds = [
			(int) $componentParams->get('wishboxradicalmartcdekorderregistrator.ready_status_id', 0),
			(int) $componentParams->get('wishboxradicalmartcdekorderregistrator.error_status_id', 0),
			(int) $componentParams->get('wishboxradicalmartcdekorderregistrator.completed_status_id', 0),
		];

		if (!in_array($order->status->id, $allowedStatusIds))
		{
			return;
		}

		/** @var OrdersModel $ordersModel */
		$ordersModel = $app->bootPlugin('wishboxcdekorderregistrator', 'radicalmart')
			->getMVCFactory()
			->createModel('Orders', 'Administrator');

		$ordersModel->register($order);
	}

	/**
	 * @param   string    $context    Context
	 * @param   stdClass  $order      Order
	 * @param   integer   $oldStatus  Old status
	 * @param   integer   $newStatus  New status
	 * @param   boolean   $isNew      Is new
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function onAfterChangeOrderStatus(
		string $context,
		stdClass $order,
		int $oldStatus,
		int $newStatus,
		bool $isNew
	): void
	{
		if ($order->shipping->plugin != 'wishboxcdek')
		{
			return;
		}

		// If you need to register the order in Cdek
		if ($newStatus != (int) $this->params->get('wishboxradicalmartcdekorderregistrator.ready_status_id', 0))
		{
			return;
		}

		//   $orderService = new OrderService;
		//   $orderService->register($order);
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
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function onBeforeRender(Event $event): void
	{
		$app = $this->getApplication();

		if (!$app->isClient('administrator'))
		{
			return;
		}

		$option = $app->getInput()->getCmd('option', '');
		$view = $app->getInput()->getCmd('view', '');

		if ($option == 'com_radicalmart' && $view == 'orders')
		{
			/*
			ToolBarHelper::custom(
				'wishboxcdekorders.register',
				'copy',
				'copy_f2.png',
				Text::_('PLG_RADICALMART_WISHBOXCDEK_REGISTER_IN_CDEK')
			);
			*/
		}
	}
}
