<?php
/**
 * @package     Joomla.Plugins
 * @subpackage  Radicalmart.Wishboxcdek
 * @copyright   2023 Nekrasov Vitaliy
 * @license     GNU General Public License version 2 or later
 */
namespace Joomla\Plugin\System\Wishboxcdek\Extension;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use stdClass;
use Wishbox\Plugin;
use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since 1.0.0
 */
final class Wishboxcdek extends Plugin implements SubscriberInterface
{
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
			'onBeforeRender' => 'onBeforeRender',
			'onRadicalMartAfterChangeOrderStatus' => 'onRadicalMartAfterChangeOrderStatus'
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
	public function onBeforeRender(Event $event): void
	{
		$app = Factory::getApplication();

		if (!$app->isClient('administrator'))
		{
			return;
		}

		$option = $app->input->getCmd('option', '');
		$view = $app->input->getCmd('view', '');

		if ($option == 'com_radicalmart' && $view == 'orders')
		{
			ToolBarHelper::custom(
				'wishboxcdekorder.register',
				'copy',
				'copy_f2.png',
				Text::_('PLG_RADICALMART_WISHBOXCDEK_REGISTER_IN_CDEK')
			);
		}
	}

	/**
	 * @param   string    $context
	 * @param   stdClass  $order
	 * @param   integer   $oldStatus
	 * @param   integer   $newStatus
	 * @param   boolean   $isNew
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 *
	 */
	public function onRadicalMartAfterChangeOrderStatus(
		string $context,
		stdClass $order,
		int $oldStatus,
		int $newStatus,
		bool $isNew
	): void
	{

//		$context = $event->getArgument(0);
//		$order = $event->getArgument(1);
//		$oldStatus = $event->getArgument(2);
//		$newStatus = $event->getArgument(3);
//		$isNew = $event->getArgument(4);

		print_r($order);
		die;

		$apiAccount = $this->params->get('api_account', '');
		$apiSecure = $this->params->get('api_secure', '');
		$orderType = $this->params->get('order_type', '');

		$orderCreator = new \Joomla\Plugin\Radicalmart\Wishboxcdek\Service\Order($apiAccount, $apiSecure, $orderType);

	}
}
