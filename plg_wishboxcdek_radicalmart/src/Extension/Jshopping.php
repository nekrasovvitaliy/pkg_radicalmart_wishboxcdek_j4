<?php
/**
 * @copyright   2013-2024 Nekrasov Vitaliy
 * @license     GNU General Public License version 2 or later
 */
namespace Joomla\Plugin\Wishboxcdek\JShopping\Extension;

use Exception;
use Joomla\Component\Wishboxcdek\Site\Event\Model\Offices\GetDataForMapEvent;
use Joomla\Component\Jshopping\Site\Lib\JSFactory;
use Joomla\Component\Jshopping\Site\Model\Wishbox\Shippingcalculator\CdekModel;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;
use Wishbox\JShoppingPlugin;
use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

// phpcs:disable PSR1.Files.SideEffects
if (!file_exists(JPATH_SITE . '/components/com_jshopping/bootstrap.php'))
{
	throw new Exception("Please install component \"joomshopping\"", 500);
}

require_once JPATH_SITE . '/components/com_jshopping/bootstrap.php';
// phpcs:enable PSR1.Files.SideEffects

/**
 * Task plugin with routines to update quantity from retailCRM. These routines can be used to control planned
 * maintenance periods and related operations.
 *
 * @property Registry  $addonParams
 *
 * @since 1.0.0
 */
final class JShopping extends JShoppingPlugin implements SubscriberInterface
{
	/**
	 * @var string $addonAlias Addon alias
	 *
	 * @since 1.0.0
	 */
	protected string $addonAlias = 'wishboxcdek';

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
			'onBeforeWishboxCdekOfficesGetDataForMap'   => 'onBeforeWishboxCdekOfficesGetDataForMap'
		];
	}

	/**
	 * Constructor.
	 *
	 * @param   DispatcherInterface  $dispatcher  The dispatcher
	 * @param   array                $config      An optional associative array of configuration settings
	 *
	 * @since 1.0.0
	 */
	public function __construct(DispatcherInterface $dispatcher, array $config)
	{
		parent::__construct($dispatcher, $config);
	}

	/**
	 * @param   GetDataForMapEvent  $event  Event
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function onBeforeWishboxCdekOfficesGetDataForMap(GetDataForMapEvent $event): void
	{
		/** @var string $context */
		$context = $event->getArgument('context');

		if ($context == 'jshopping')
		{
			/** @var integer $shippingMethodId */
			$shippingMethodId = $event->getArgument('shippingMethodId');

			/** @var CdekModel $wishboxshippingcalculatorcdekModel */
			$wishboxshippingcalculatorcdekModel = JSFactory::getModel('cdek', 'Site\\Wishbox\\Shippingcalculator');

			$shippingTariff = $wishboxshippingcalculatorcdekModel->getTariff($shippingMethodId);

			$event->setArgument('shippingTariff', $shippingTariff);
		}
	}
}
