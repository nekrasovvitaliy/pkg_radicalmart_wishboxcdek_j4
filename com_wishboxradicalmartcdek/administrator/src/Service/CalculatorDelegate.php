<?php
/**
 * @copyright   (с) 2013-2024 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later
 */
namespace Joomla\Component\Wishboxradicalmartcdek\Administrator\Service;

use Exception;
use Joomla\CMS\Factory;
use Joomla\Component\Wishboxcdek\Site\Interface\CalculatorDelegateInterface;
use stdClass;
use WishboxCdekSDK2\Model\Request\Calculator\TariffListPost\PackageRequest;

/**
 * @since 1.0.0
 */
class CalculatorDelegate implements CalculatorDelegateInterface
{
	/**
	 * @var stdClass
	 *
	 * @since 1.0.0
	 */
	public stdClass $method;

	/**
	 * @var array $products Products
	 *
	 * @since 1.0.0
	 */
	private array $products;

	/**
	 * @var integer $receiverCityCode Receiver city code
	 *
	 * @since 1.0.0
	 */
	private int $receiverCityCode;

	/**
	 * @param   stdClass    $method            Method
	 * @param   array       $products          Data
	 * @param   integer     $receiverCityCode  Receiver city code
	 *
	 * @since 1.0.0
	 */
	public function __construct(stdClass $method, array $products, int $receiverCityCode)
	{
		$this->method = $method;
		$this->products = $products;
		$this->receiverCityCode = $receiverCityCode;
	}

	/**
	 * @return integer
	 *
	 * @since 1.0.0
	 */
	public function getShippingMethodId(): int
	{
		return $this->method->id;
	}

	/**
	 * @return integer
	 *
	 * @since 1.0.0
	 */
	public function getSenderCityCode(): int
	{
		return (int) $this->method->params->get('senderCityCode');
	}

	/**
	 * @return integer
	 *
	 * @since 1.0.0
	 */
	public function getReceiverCityCode(): int
	{
		return $this->receiverCityCode;
	}

	/**
	 * @return integer
	 *
	 * @since 1.0.0
	 */
	public function getTotalWeight(): int
	{
		$totalWeight = 0;

		foreach ($this->products as $product)
		{
			$totalWeight += (float) $product->shipping->get('weight', 0) * $product->order['quantity'];
		}

		return $totalWeight;
	}

	/**
	 * @return array
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function getTariffCodes(): array
	{
		$tariffCodes = $this->method->params->get('tariffCodes');

		if (!is_array($tariffCodes) || !count($tariffCodes))
		{
			throw new Exception('Shipping method "' . $this->method->title . '" doesn`t have any tariffs.', 500);
		}

		return $tariffCodes;
	}

	/**
	 * Metod returns array of packages
	 *
	 * @return   PackageRequest[]
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function getPackages(): array
	{
		/** @var PackageRequest[] $packages */
		$packages = [];

		$app = Factory::getApplication();
		$app->triggerEvent('onWishboxRadicalMartCdekCalculatorDelegateGetPackages', [&$packages, $this]);

		return $packages;
	}

	/**
	 * @return boolean
	 *
	 * @since 1.0.0
	 */
	public function useDimensions(): bool
	{
		return false;
	}
}
