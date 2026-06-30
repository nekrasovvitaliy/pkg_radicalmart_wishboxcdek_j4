<?php
/**
 * @copyright   (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later
 */

namespace Joomla\Plugin\RadicalMartShipping\WishboxCdek\Service\Calculator;

use UnexpectedValueException;
use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Defines the trait for a RadicalMartShippingCalculatorService aware class.
 *
 * @since 1.0.0
 */
trait RadicalMartShippingCalculatorServiceAwareTrait
{
	/**
	 * @var RadicalMartShippingCalculatorService|null
	 *
	 * @since 1.0.0
	 */
	private ?RadicalMartShippingCalculatorService $radicalMartShippingCalculatorService = null;

	/**
	 * Get the RadicalMart shipping calculator service.
	 *
	 * @return RadicalMartShippingCalculatorService
	 *
	 * @throws UnexpectedValueException
	 *
	 * @since 1.0.0
	 */
	protected function getRadicalMartShippingCalculatorService(): RadicalMartShippingCalculatorService
	{
		if ($this->radicalMartShippingCalculatorService)
		{
			return $this->radicalMartShippingCalculatorService;
		}

		throw new UnexpectedValueException('RadicalMartShippingCalculatorService not set in ' . __CLASS__);
	}

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
	): void
	{
		$this->radicalMartShippingCalculatorService = $radicalMartShippingCalculatorService;
	}
}
