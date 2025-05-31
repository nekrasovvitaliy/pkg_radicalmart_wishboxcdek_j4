<?php
/**
 * @copyright   (с) 2013-2025 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later
 */
namespace Joomla\Component\WishboxRadicalMartCdek\Administrator\Model;

use Exception;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseModel;
use Joomla\Component\WishboxCdek\Site\Interface\CalculatorDelegateInterface;
use Joomla\Component\WishboxRadicalMartCdek\Administrator\Event\Model\CalculatorDelegate\GetPackagesEvent;
use Joomla\Component\WishboxRadicalMartCdek\Administrator\Helper\WishboxRadicalMartCdekHelper;
use stdClass;
use WishboxCdekSDK2\Model\Request\Calculator\TariffListPost\PackageRequest;

/**
 * @since 1.0.0
 */
class CalculatorDelegateModel extends BaseModel implements CalculatorDelegateInterface
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
	 * @var array $formData Form data
	 *
	 * @since 1.0.0
	 */
	private array $formData;

	/**
	 * @param   stdClass $method Method
	 *
	 * @return self
	 *
	 * @since 1.0.0
	 */
	public function setMethod(stdClass $method): self
	{
		$this->method = $method;

		return $this;
	}

	/**
	 * @param   array  $formData  Form data
	 *
	 * @return self
	 *
	 * @since 1.0.0
	 */
	public function setFormData(array $formData): self
	{
		$this->formData = $formData;

		return $this;
	}

	/**
	 * @param   array  $products  Products
	 *
	 * @return self
	 *
	 * @since 1.0.0
	 */
	public function setProducts(array $products): self
	{
		$this->products = $products;

		return $this;
	}

	/**
	 * @return stdClass
	 *
	 * @since 1.0.0
	 */
	public function getMethod(): stdClass
	{
		return $this->method;
	}

	/**
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function getProducts(): array
	{
		return $this->products;
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
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function getFormData(): array
	{
		return $this->formData;
	}

	/**
	 * @return integer
	 *
	 * @since 1.0.0
	 */
	public function getSenderCityCode(): int
	{
		return (int) $this->method->params->get('sender_city_code');
	}

	/**
	 * @return integer
	 *
	 * @since 1.0.0
	 */
	public function getReceiverCityCode(): int
	{
		$cityCode = (isset($this->formData['shipping']) && isset($this->formData['shipping']['city_code']))
			? $this->formData['shipping']['city_code']
			: 0;

		return (int) $cityCode;
	}

	/**
	 * @return integer
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function getTotalWeight(): int
	{
		$totalWeight = 0;

		foreach ($this->products as $product)
		{
			$productWeight = WishboxRadicalMartCdekHelper::getProductWeight($product, 'g');
			$totalWeight += $productWeight * $product->order['quantity'];
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
		$tariffCodes = $this->method->params->get('tariff_codes');

		if (!is_array($tariffCodes) || !count($tariffCodes))
		{
			throw new Exception(
				'Shipping method "' . $this->method->title . '" does`t have any tariffs.',
				500
			);
		}

		return $tariffCodes;
	}

	/**
	 * Metod returns an array of packages
	 *
	 * @return   PackageRequest[]
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function getPackages(): array
	{
		$app = Factory::getApplication();

		/** @var GetPackagesEvent $event */
		$event = AbstractEvent::create(
			'onWishboxRadicalMartCdekCalculatorDelegateGetPackages',
			[
				'subject'       => $this,
				'eventClass'    => GetPackagesEvent::class
			]
		);

		/** @var GetPackagesEvent $event */
		$eventResult = $app->getDispatcher()->dispatch($event->getName(), $event);

		return $eventResult->getPackageRequests();
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
