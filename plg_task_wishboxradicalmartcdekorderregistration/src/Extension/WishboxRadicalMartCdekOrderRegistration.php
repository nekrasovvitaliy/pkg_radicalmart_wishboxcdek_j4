<?php
/**
 * @copyright   (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later
 */
namespace Joomla\Plugin\Task\WishboxRadicalMartCdekOrderRegistration\Extension;

use Error;
use Exception;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\RadicalMart\Administrator\Model\OrderModel;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Task\Status;
use Joomla\Component\Scheduler\Administrator\Traits\TaskPluginTrait;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Event\SubscriberInterface;
use Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistration\Extension\WishboxCdekOrderRegistration;
use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Task plugin with routines to register RadicalMart orders in CDEK.
 *
 * @since  1.0.0
 *
 * @noinspection PhpUnused
 */
final class WishboxRadicalMartCdekOrderRegistration extends CMSPlugin implements SubscriberInterface
{
	use DatabaseAwareTrait;
	use TaskPluginTrait;

	/**
	 * @var string[]
	 *
	 * @since 1.0.0
	 */
	protected const TASKS_MAP = [
		'plg_task_wishboxradicalmartcdekorderregistration_register' => [
			'langConstPrefix' => 'PLG_TASK_WISHBOXRADICALMARTCDEKORDERREGISTRATION_REGISTER_ORDERS',
			'method'          => 'register'
		],
	];

	/**
	 * Autoload the language file.
	 *
	 * @var boolean
	 *
	 * @since 1.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * @inheritDoc
	 *
	 * @return string[]
	 *
	 * @since 1.0.0
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onTaskOptionsList'    => 'advertiseRoutines',
			'onExecuteTask'        => 'standardRoutineHandler'
		];
	}

	/**
	 * @param   ExecuteTaskEvent  $event Event
	 *
	 * @return integer
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnusedParameterInspection
	 * @noinspection PhpUnusedPrivateMethodInspection
	 */
	private function register(ExecuteTaskEvent $event): int
	{
		try
		{
			$app = $this->getApplication();
			$orderIds = $this->getOrderIds();

			/** @var OrderModel $orderModel */
			$orderModel = $app->bootComponent('com_radicalmart')
				->getMVCFactory()
				->createModel('order', 'Administrator', ['ignore_request' => true]);

			/** @var WishboxCdekOrderRegistration $plugin */
			$plugin = $app->bootPlugin('wishboxcdekorderregistration', 'radicalmart');

			foreach ($orderIds as $orderId)
			{
				$order = $orderModel->getItem($orderId);
				$plugin->getOrderRegistrationService()->register($order);
			}
		}
		catch (Exception | Error $e)
		{
			$this->logTask((string) $e, 'error');

			return Status::KNOCKOUT;
		}

		return Status::OK;
	}

	/**
	 * @return integer[]
	 *
	 * @since 1.0.0
	 */
	protected function getOrderIds(): array
	{
		$componentParams = ComponentHelper::getParams('com_wishboxradicalmartcdek');
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
}
