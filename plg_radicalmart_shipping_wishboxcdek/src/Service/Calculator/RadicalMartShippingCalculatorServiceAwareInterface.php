<?php
/**
 * @copyright   (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later
 */

namespace Joomla\Plugin\RadicalMartShipping\WishboxCdek\Service\Calculator;

use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Interface to be implemented by classes depending on a RadicalMart shipping calculator service.
 *
 * @since 1.0.0
 */
interface RadicalMartShippingCalculatorServiceAwareInterface
{
	/**
	 * Set the RadicalMart shipping calculator service.
	 *
	 * @param   RadicalMartShippingCalculatorService  $radicalMartShippingCalculatorService  RadicalMart shipping calculator service
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function setRadicalMartShippingCalculatorService(
		RadicalMartShippingCalculatorService $radicalMartShippingCalculatorService
	): void;
}
