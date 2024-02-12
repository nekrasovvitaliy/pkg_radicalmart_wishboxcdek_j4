<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license     GNU General Public License version 2 or later
 */
namespace Joomla\Plugin\RadicalMart\Wishboxcdekorderregistrator\Extension;

use AntistressStore\CdekSDK2\Exceptions\CdekV2RequestException;
use Error;
use Exception;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\RadicalMart\Administrator\Model\OrderModel;
use Joomla\Component\Wishboxcdek\Site\Service\Registrator;
use Joomla\Event\SubscriberInterface;
use Joomla\Plugin\RadicalMart\Wishboxcdekorderregistrator\Service\RegistratorDelegate;
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
		try
		{
			if ($order->shipping->plugin != 'wishboxcdek')
			{
				return;
			}

			$allowedStatusids = [
				(int) $this->params->get('ready_status_id', 0),
				(int) $this->params->get('error_status_id', 0),
				(int) $this->params->get('completed_status_id', 0),
			];

			if (!in_array($order->status->id, $allowedStatusids))
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
			catch (CdekV2RequestException $e)
			{
				$app->enqueueMessage(
					Text::_(
						'PLG_RADICALMART_WISHBOXCDEKORDERREGISTRATOR_DELIVERY_SERVICE_REGISTRATION_ERROR_MESSAGE'
					) . ': ' . $e->errorMessage,
					CMSApplicationInterface::MSG_WARNING
				);

				$orderModel->addLog(
					$order->id,
					'delivery_service_registration_error',
					[
						'action_text' => Text::_(
							'PLG_RADICALMART_WISHBOXCDEKORDERREGISTRATOR_DELIVERY_SERVICE_REGISTRATION_ERROR_MESSAGE'
						),
						'message' => $e->errorMessage
					]
				);

				$orderModel->updateStatus(
					$order->id,
					(int) $this->params->get('error_status_id', 0)
				);
			}
		}
		catch (Exception | Error $e)
		{
			echo $e;
			die;
		}
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

			$app->enqueueMessage(
				Text::_(
					'PLG_RADICALMART_WISHBOXCDEKORDERREGISTRATOR_DELIVERY_SERVICE_REGISTRATION_COMPLETED_MESSAGE'
				),
				CMSApplicationInterface::MSG_NOTICE
			);

			$orderModel->updateStatus(
				$order->id,
				(int) $this->params->get('completed_status_id', 0)
			);
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(
				Text::_(
					'PLG_RADICALMART_WISHBOXCDEKORDERREGISTRATOR_DELIVERY_SERVICE_REGISTRATION_ERROR_MESSAGE'
				) . ': ' . $e->errorMessage,
				CMSApplicationInterface::MSG_WARNING
			);

			$orderModel->addLog(
				$order->id,
				'delivery_service_registration_error',
				[
					'action_text' => 'Delivery service registration error',
					'message' => $e->getMessage()
				]
			);

			$orderModel->updateStatus(
				$order->id,
				(int) $this->params->get('error_status_id', 0)
			);
		}
	}
}
