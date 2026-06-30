<?php
/**
 * @copyright   (с) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license         GNU General Public License version 2 or later
 */

namespace Joomla\Plugin\RadicalMartShipping\WishboxCdek\Service\Calculator\Adapter;

use Exception;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\Component\WishboxRadicalMartCdek\Administrator\Helper\WishboxRadicalMartCdekHelper;
use Joomla\Event\DispatcherAwareInterface;
use Joomla\Event\DispatcherAwareTrait;
use Joomla\Event\DispatcherInterface;
use stdClass;
use WishboxCdek\Request\Calculator\CalcPackageRequestDto;
use WishboxCdekLibrary\Event\Service\CalculatorAdapter\GetPackagesEvent;
use WishboxCdekLibrary\Interface\CalculatorAdapterInterface;

/**
 * @since 1.0.0
 */
class CalculatorAdapterService implements CalculatorAdapterInterface, DispatcherAwareInterface
{
	use DispatcherAwareTrait;

	/**
	 * @var stdClass
	 *
	 * @since 1.0.0
	 */
	public stdClass $method {
		get {
			return $this->method;
		}
	}

	/**
	 * @var array $products Products
	 *
	 * @since 1.0.0
	 */
	public array $products {
		get {
			return $this->products;
		}
	}

	/**
	 * @var array $formData Form data
	 *
	 * @since 1.0.0
	 */
	public array $formData {
		get {
			return $this->formData;
		}
	}

	/**
	 * Class constructor.
	 *
	 * @param   stdClass             $method      Method
	 * @param   array                $formData    Form data
	 * @param   array                $products    Products
	 * @param   DispatcherInterface  $dispatcher  Dispatcher
	 *
	 * @since 1.0.0
	 */
	public function __construct(stdClass $method, array $formData, array $products, DispatcherInterface $dispatcher)
	{
		$this->method   = $method;
		$this->formData = $formData;
		$this->products = $products;
		$this->setDispatcher($dispatcher);
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
			$totalWeight   += $productWeight * $product->order['quantity'];
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
				'Shipping method "' . $this->method->title . '" does not have any tariffs.',
				500
			);
		}

		return $tariffCodes;
	}

	/**
	 * Method returns an array of packages
	 *
	 * @return   CalcPackageRequestDto[]
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function getPackages(): array
	{
		/** @var GetPackagesEvent $event */
		$event = AbstractEvent::create(
			'onWishboxRadicalMartCdekCalculatorAdapterGetPackages',
			[
				'subject'    => $this,
				'eventClass' => GetPackagesEvent::class
			]
		);

		$this->getDispatcher()->dispatch($event->getName(), $event);

		return $event->getPackages();
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
