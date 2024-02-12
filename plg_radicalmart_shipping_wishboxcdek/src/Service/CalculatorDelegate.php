<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license     GNU General Public License version 2 or later
 */
namespace Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Service;

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
	 * @var array
	 *
	 * @since 1.0.0
	 */
	private array $data;

	/**
	 * @var array $products Products
	 *
	 * @since 1.0.0
	 */
	private array $products;

	/**
	 * @param   stdClass    $method    Method
	 * @param   array|null  $data      Data
	 * @param   array       $products  Data
	 *
	 * @since 1.0.0
	 */
	public function __construct(stdClass $method, ?array $data, array $products)
	{
		$this->method = $method;
		$this->data = $data;
		$this->products = $products;
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
		$receiverCityCode = 0;

		if (isset($this->data['shipping']['cityCode']))
		{
			$receiverCityCode = (int) $this->data['shipping']['cityCode'];
		}

		if ($receiverCityCode <= 0)
		{
			$receiverCityCode = 0;
		}

		return $receiverCityCode;
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
	 * @since 1.0.0
	 */
	public function getTariffCodes(): array
	{
		return $this->method->params->get('tariffCodes');
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
