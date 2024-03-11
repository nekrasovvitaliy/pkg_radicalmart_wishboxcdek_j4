<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license     GNU General Public License version 2 or later
 */
namespace Joomla\Plugin\RadicalMart\Wishboxcdekorderregistrator\Extension;

use Exception;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Wishboxradicalmartcdek\Administrator\Service\OrderService;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;
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
class Wishboxcdekorderregistrator extends CMSPlugin implements SubscriberInterface
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var boolean
	 *
	 * @since  1.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Loads the application object.
	 *
	 * @var  CMSApplication
	 *
	 * @since  1.0.0
	 */
	protected $app = null;

	/**
	 * Enable on RadicalMart
	 *
	 * @var  boolean
	 *
	 * @since  2.0.0
	 */
	public bool $radicalmart = true;

	/**
	 * Enable on RadicalMartExpress
	 *
	 * @var  boolean
	 *
	 * @since  2.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public bool $radicalmart_express = true; // phpcs:ignore

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
			'onRadicalMartBeforeOrderSave'              => 'onRadicalMartBeforeOrderSave',
			'onRadicalMartAfterOrderSave'               => 'onRadicalMartAfterOrderSave',
			'onRadicalMartAfterChangeOrderStatus'       => 'onRadicalMartAfterChangeOrderStatus'
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
	public function onRadicalMartBeforeOrderSave(
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
		if ($context != 'com_radicalmart.checkout')
		{
			return;
		}

		if ($shipping->plugin != 'wishboxcdek')
		{
			return;
		}

		$registry = new Registry($data['shipping']);
		$d = $registry->get('data');
		$d->dimensions = $shipping->params->get('defaultDimensions');
		$registry->set('data', $d);
		$data['shipping'] = $registry->toString();
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
	public function onRadicalMartAfterOrderSave(
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

		$componentParams = ComponentHelper::getParams('com_wishboxradicalmartcdek');

		$allowedStatusIds = [
			(int) $componentParams->get('ready_status_id', 0),
			(int) $componentParams->get('error_status_id', 0),
			(int) $componentParams->get('completed_status_id', 0),
		];

		if (!in_array($order->status->id, $allowedStatusIds))
		{
			return;
		}

		$orderService = new OrderService;
		$orderService->register($order);
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
	public function onRadicalMartAfterChangeOrderStatus(
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
		if ($newStatus != (int) $this->params->get('ready_status_id', 0))
		{
			return;
		}

//		$orderService = new OrderService;
//		$orderService->register($order);
	}
}
