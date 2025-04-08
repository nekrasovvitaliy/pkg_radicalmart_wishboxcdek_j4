<?php
/**
 * @copyright   (c) 2013-2025 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later
 *
 * @noinspection PhpMultipleClassDeclarationsInspection
 */
namespace Joomla\Component\Wishboxradicalmartcdek\Administrator\Service;

use Exception;
use Joomla\Component\Wishboxcdek\Site\Service\Calculator;
use Wishbox\ShippingService\ShippingTariff;

/**
 * @since 1.0.0
 */
class CalculatorService
{
	/**
	 * @var array
	 *
	 * @since 1.0.0
	 */
	private static array $shippingTariffs = [];

	/**
	 * @var array
	 *
	 * @since 1.0.0
	 */
	private static array $shippingTariffExceptions = [];

	/**
	 * @param   object  $method    Method data
	 * @param   array   $formData  Order form data.
	 * @param   array   $products  Order products data.
	 *
	 * @return ShippingTariff|null
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnnecessaryLocalVariableInspection
	 */
	public static function getShippingTariff(object $method, array $formData, array $products): ?ShippingTariff
	{
		$tariffCode = $formData['shipping']['tariffCode'] ?? null;

		if ($tariffCode)
		{
			$shippingTariffByCode = self::getShippingTariffByCode($method, $formData, $products, $tariffCode);

			return $shippingTariffByCode;
		}
		else
		{
			$minShippingTariff = self::getMinShippingTariff($method, $formData, $products);

			return $minShippingTariff;
		}
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
	public static function getShippingTariffByCode(object $method, array $formData, array $products, int $tariffCode): ?ShippingTariff
	{
		$shippingTariffs = self::getShippingTariffs($method, $formData, $products);

		if (!count($shippingTariffs))
		{
			throw new Exception('Array of tariffs must not be empty', 500);
		}

		$tariffByCode = null;

		foreach ($shippingTariffs as $tariff)
		{
			if ($tariff->getCode() == $tariffCode)
			{
				$tariffByCode = $tariff;
			}
		}

		return $tariffByCode;
	}

	/**
	 * @param   object  $method    Method data
	 * @param   array   $formData  Order form data.
	 * @param   array   $products  Order products data.
	 *
	 * @return ShippingTariff|null
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public static function getMinShippingTariff(object $method, array $formData, array $products): ?ShippingTariff
	{
		$shippingTariffs = self::getShippingTariffs($method, $formData, $products);

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
	 * @param   array   $products  Order products data.
	 *
	 * @return ShippingTariff[]
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnnecessaryLocalVariableInspection
	 */
	public static function getShippingTariffs(object $method, array $formData, array $products): array
	{
		$hash = md5(
			json_encode($method->id)
			. json_encode($formData['shipping']['cityCode'])
			. json_encode($products)
		);

		if (!isset(self::$shippingTariffs[$hash]) && !isset(self::$shippingTariffExceptions[$hash]))
		{
			try
			{
				$calculatorDelegate = new CalculatorDelegate($method, $formData, $products);
				$calculator = new Calculator($calculatorDelegate);
				self::$shippingTariffs[$hash] = $calculator->getShippingTariffs();
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
