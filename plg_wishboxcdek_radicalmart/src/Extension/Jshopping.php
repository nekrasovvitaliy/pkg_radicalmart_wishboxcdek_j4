<?php
/**
 * @copyright   (c) 2013-2025 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later
 */
namespace Joomla\Plugin\WishboxCdek\JShopping\Extension;

use Exception;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\WishboxCdek\Site\Event\Model\Offices\GetDataForMapEvent;
use Joomla\Component\Jshopping\Site\Lib\JSFactory;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;
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
final class JShopping extends CMSPlugin implements SubscriberInterface
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
