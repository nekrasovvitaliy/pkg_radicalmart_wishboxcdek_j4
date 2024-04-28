<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license     GNU General Public License version 2 or later
 */
namespace Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Service;

use Exception;
use Joomla\Component\Wishboxcdek\Site\Entity\ProductEntity;
use stdClass;
use Wishbox\ShippingService\Cdek\Interface\CalculatorDelegateInterface;

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
	private stdClass $method;

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
		$useDefaultPackageWeight = $this->method->params->get('useDefaultPackageWeight', 0);

		if ($useDefaultPackageWeight)
		{
			return (int) $this->method->params->get('defaultPackageWeight', 0);
		}

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
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public function getCalculationMethod(): string
	{
		return 'without_parcels';
	}

	/**
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function getProducts(): array
	{
		$products = [];

		foreach ($this->products as $product)
		{
			print_r($product);
			die;
			$p = new ProductEntity(

			);
		}

		return [];
	}

	/**
	 * @return boolean
	 *
	 * @since 1.0.0
	 */
	public function useDimencions(): bool
	{
		return false;
	}

	/**
	 * @return integer
	 *
	 * @since 1.0.0
	 */
	public function getPackageWidth(): int
	{
		$dimensions = $this->method->params->get('defaultDimensions');

		return (int) $dimensions->width;
	}

	/**
	 * @return integer
	 *
	 * @since 1.0.0
	 */
	public function getPackageHeight(): int
	{
		$dimensions = $this->method->params->get('defaultDimensions');

		return (int) $dimensions->height;
	}

	/**
	 * @return integer
	 *
	 * @since 1.0.0
	 */
	public function getPackageLength(): int
	{
		$dimensions = $this->method->params->get('defaultDimensions');

		return (int) $dimensions->length;
	}
}
