<?php
/**
 * @copyright  2013-2026 Nekrasov Vitaliy
 * @license    GNU General Public License version 2 or later
 */
namespace Joomla\Plugin\WishboxCdek\JShopping\Helper;

use Exception;
use Wishbox\ShippingService\ShippingTariff;
use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
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
