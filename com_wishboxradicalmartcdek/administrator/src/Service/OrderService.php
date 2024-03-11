<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license     GNU General Public License version 2 or later
 */
namespace Joomla\Component\Wishboxradicalmartcdek\Administrator\Service;

use AntistressStore\CdekSDK2\Exceptions\CdekV2RequestException;
use Error;
use Exception;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Component\RadicalMart\Administrator\Model\OrderModel;
use Joomla\Component\Wishboxcdek\Site\Service\Registrator;
use Joomla\Registry\Registry;
use stdClass;

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
			$errorStatusId = (int) $componentParams->get('error_status_id', 0);
			$completedStatusId = (int) $componentParams->get('completed_status_id', 0);

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

				$orderModel->updateStatus(
					$order->id,
					$completedStatusId
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
					$errorStatusId
				);
			}
		}
		catch (Exception | Error $e)
		{
			echo $e;
			die;
		}
	}
}
