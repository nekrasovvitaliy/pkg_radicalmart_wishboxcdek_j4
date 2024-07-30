<?php
/**
 * @copyright   (c) 2013-2024 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later
 */
namespace Joomla\Plugin\Task\Wishboxradicalmartcdekorderregistrator\Extension;

use Error;
use Exception;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\Component\RadicalMart\Administrator\Model\OrderModel;
use Joomla\Component\Wishboxradicalmartcdek\Administrator\Service\OrderService;
use Joomla\Database\DatabaseInterface;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Task\Status;
use Joomla\Component\Scheduler\Administrator\Traits\TaskPluginTrait;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\SubscriberInterface;
use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Task plugin with routines to update quantity from retailCRM. These routines can be used to control planned
 * maintenance periods and related operations.
 *
 * @since  1.0.0
 *
 * @noinspection PhpUnused
 */
final class Wishboxradicalmartcdekorderregistrator extends CMSPlugin implements SubscriberInterface
{
	use TaskPluginTrait;

	/**
	 * @var string[]
	 *
	 * @since 1.0.0
	 */
	protected const TASKS_MAP = [
		'plg_task_wishboxradicalmartcdekorderregistrator_register' => [
			'langConstPrefix' => 'PLG_TASK_WISHBOXRADICALMARTCDEKORDERREGISTRATOR_REGISTER_ORDERS',
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
	 * Constructor.
	 *
	 * @param   DispatcherInterface  $dispatcher  The dispatcher
	 * @param   array                $config      An optional associative array of configuration settings
	 *
	 * @since   1.0.0
	 */
	public function __construct(DispatcherInterface $dispatcher, array $config)
	{
		parent::__construct($dispatcher, $config);
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
			$app = Factory::getApplication();
			$orderIds = $this->getOrderIds();

			/** @var OrderModel $orderModel */
			$orderModel = $app->bootComponent('com_radicalmart')
				->getMVCFactory()
				->createModel('order', 'Administrator', ['ignore_request' => true]);

			foreach ($orderIds as $orderId)
			{
				$order = $orderModel->getItem($orderId);
				$orderService = new OrderService;
				$orderService->register($order);
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
			(int) $componentParams->get('ready_status_id', 0),
			(int) $componentParams->get('error_status_id', 0),
		];

		/** @var DatabaseInterface $db */
		$db = Factory::getContainer()->get(DatabaseInterface::class);

		$query = $db->getQuery(true)
			->select('id')
			->from('#__radicalmart_orders')
			->whereIn('status', $allowedStatusIds)
			->where('state=1');
		$db->setQuery($query);

		return $db->loadColumn();
	}
}
