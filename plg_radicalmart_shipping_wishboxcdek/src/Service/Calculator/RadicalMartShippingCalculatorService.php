<?php
/**
 * @copyright   (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later
 */

namespace Joomla\Plugin\RadicalMartShipping\WishboxCdek\Service\Calculator;

use Exception;
use Joomla\Event\DispatcherAwareInterface;
use Joomla\Event\DispatcherAwareTrait;
use Joomla\Plugin\RadicalMartShipping\WishboxCdek\Service\Calculator\Adapter\CalculatorAdapterService;
use Wishbox\ShippingService\ShippingTariff;
use WishboxCdekLibrary\Service\Calculator\CalculatorServiceAwareInterface;
use WishboxCdekLibrary\Service\Calculator\CalculatorServiceAwareTrait;
use function defined;
use function json_encode;
use function md5;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Calculates RadicalMart shipping tariffs through the Wishbox CDEK calculator service.
 *
 * @since 1.0.0
 */
class RadicalMartShippingCalculatorService implements CalculatorServiceAwareInterface, DispatcherAwareInterface
{
	use CalculatorServiceAwareTrait;
	use DispatcherAwareTrait;

	/**
	 * @var ShippingTariff[][]
	 *
	 * @since 1.0.0
	 */
	private static array $shippingTariffs = [];

	/**
	 * @var Exception[]
	 *
	 * @since 1.0.0
	 */
	private static array $shippingTariffExceptions = [];

	/**
	 * @param   object  $method    Method data
	 * @param   array   $formData  Order form data
	 * @param   array   $products  Order products data
	 *
	 * @return ShippingTariff|null
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function getShippingTariff(object $method, array $formData, array $products): ?ShippingTariff
	{
		$tariffCode = $formData['shipping']['tariff_code'] ?? null;

		if ($tariffCode)
		{
			return $this->getShippingTariffByCode($method, $formData, $products, (int) $tariffCode);
		}

		return $this->getMinShippingTariff($method, $formData, $products);
	}

	/**
	 * @param   object   $method      Method data
	 * @param   array    $formData    Order form data
	 * @param   array    $products    Order products data
	 * @param   integer  $tariffCode  Tariff code
	 *
	 * @return ShippingTariff|null
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	private function getShippingTariffByCode(
		object $method,
		array  $formData,
		array  $products,
		int    $tariffCode
	): ?ShippingTariff
	{
		$shippingTariffs = $this->getShippingTariffs($method, $formData, $products);

		if (!count($shippingTariffs))
		{
			throw new Exception('Array of tariffs must not be empty', 500);
		}

		return array_find(
			$shippingTariffs,
			fn($tariff) => $tariff->getCode() == $tariffCode
		);
	}

	/**
	 * @param   object  $method    Method data
	 * @param   array   $formData  Order form data
	 * @param   array   $products  Order products data
	 *
	 * @return ShippingTariff|null
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	private function getMinShippingTariff(object $method, array $formData, array $products): ?ShippingTariff
	{
		$shippingTariffs = $this->getShippingTariffs($method, $formData, $products);

		if (!count($shippingTariffs))
		{
			throw new Exception('Array of tariffs must not be empty', 500);
		}

		$minTariff = $shippingTariffs[0];

		foreach ($shippingTariffs as $tariff)
		{
			if ($tariff->getShipping() < $minTariff->getShipping())
			{
				$minTariff = $tariff;
			}
		}

		return $minTariff;
	}

	/**
	 * @param   object  $method    Method data
	 * @param   array   $formData  Order form data
	 * @param   array   $products  Order products data
	 *
	 * @return ShippingTariff[]
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	private function getShippingTariffs(object $method, array $formData, array $products): array
	{
		$hash = md5(
			json_encode($method->id)
			. json_encode($formData['shipping']['city_code'])
			. json_encode($products)
		);

		if (!isset(self::$shippingTariffs[$hash]) && !isset(self::$shippingTariffExceptions[$hash]))
		{
			try
			{
				$calculatorAdapterService = new CalculatorAdapterService(
					$method,
					$formData,
					$products,
					$this->getDispatcher()
				);

				self::$shippingTariffs[$hash] = $this->getCalculatorService()
					->getShippingTariffs($calculatorAdapterService);
			}
			catch (Exception $e)
			{
				self::$shippingTariffExceptions[$hash] = $e;

				throw $e;
			}
		}
		elseif (isset(self::$shippingTariffExceptions[$hash]))
		{
			throw self::$shippingTariffExceptions[$hash];
		}
		elseif (isset(self::$shippingTariffs[$hash]))
		{
			return self::$shippingTariffs[$hash];
		}

		return self::$shippingTariffs[$hash];
	}
}
