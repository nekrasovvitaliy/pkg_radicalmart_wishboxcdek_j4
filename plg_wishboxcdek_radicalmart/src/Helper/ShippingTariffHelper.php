<?php
/**
 * @copyright  2013-2025 Nekrasov Vitaliy
 * @license    GNU General Public License version 2 or later
 */
namespace Joomla\Plugin\WishboxCdek\JShopping\Helper;

use Exception;
use Joomla\Component\Jshopping\Site\Lib\JSFactory;
use Wishbox\ShippingService\ShippingTariff;
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
 * @since 1.0.0
 */
class ShippingTariffHelper
{
	/**
	 * @param   integer  $shippingMethodId  Shipping method id
	 *
	 * @return ShippingTariff|null
	 *
	 * @since 1.0.0
	 */
	public static function getShippingTariff(int $shippingMethodId): ?ShippingTariff
	{
		/** @var CdekModel $wishboxshippingcalculatorcdekModel */
		$wishboxshippingcalculatorcdekModel = JSFactory::getModel('cdek', 'Site\\Wishbox\\Shippingcalculator');

		return $wishboxshippingcalculatorcdekModel->getTariff($shippingMethodId);
	}
}
