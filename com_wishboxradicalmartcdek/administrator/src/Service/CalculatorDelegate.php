<?php
/**
 * @copyright   (с) 2013-2025 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later
 */
namespace Joomla\Component\Wishboxradicalmartcdek\Administrator\Service;

use Exception;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Factory;
use Joomla\Component\Wishboxcdek\Site\Interface\CalculatorDelegateInterface;
use Joomla\Component\Wishboxradicalmartcdek\Administrator\Event\Service\CalculatorDelegate\GetPackagesEvent;
use Joomla\Component\Wishboxradicalmartcdek\Administrator\Helper\WishboxradicalmartcdekHelper;
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
	 * @var array $formData Form data
	 *
	 * @since 1.0.0
	 */
	private array $formData;

	/**
	 * @param   stdClass    $method    Method
	 * @param   array       $formData  Form data
	 * @param   array       $products  Data
	 *
	 * @since 1.0.0
	 */
	public function __construct(stdClass $method, array $formData, array $products)
	{
		$this->method = $method;
		$this->formData = $formData;
		$this->products = $products;
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
		return (int) $this->method->params->get('senderCityCode');
	}

	/**
	 * @return integer
	 *
	 * @since 1.0.0
	 */
	public function getReceiverCityCode(): int
	{
		$cityCode = (isset($this->formData['shipping']) && isset($this->formData['shipping']['cityCode']))
			? $this->formData['shipping']['cityCode']
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
			$productWeight = WishboxradicalmartcdekHelper::getProductWeight($product, 'g');
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
		$tariffCodes = $this->method->params->get('tariffCodes');

		if (!is_array($tariffCodes) || !count($tariffCodes))
		{
			throw new Exception(
				'Shipping method "' . $this->method->title . '" doesn`t have any tariffs.',
				500
			);
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
