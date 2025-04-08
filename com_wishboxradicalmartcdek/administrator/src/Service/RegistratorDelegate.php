<?php
/**
 * @copyright   (с) 2013-2025 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later
 *
 * @noinspection PhpMultipleClassDeclarationsInspection
 */
namespace Joomla\Component\Wishboxradicalmartcdek\Administrator\Service;

use Exception;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Factory;
use Joomla\Component\RadicalMart\Administrator\Table\OrderTable;
use Joomla\Component\Wishboxcdek\Site\Entity\ProductEntity;
use Joomla\Component\Wishboxcdek\Site\Interface\RegistratorDelegateInterface;
use Joomla\Component\Wishboxradicalmartcdek\Administrator\Event\Service\RegistratorDelegate\GetOrdersPostPackagesEvent;
use Joomla\Component\Wishboxradicalmartcdek\Administrator\Event\Service\RegistratorDelegate\GetOrdersPatchPackagesEvent;
use Joomla\Component\Wishboxradicalmartcdek\Administrator\Exception\OrderServiceException;
use Joomla\Component\Wishboxradicalmartcdek\Administrator\Helper\WishboxradicalmartcdekHelper;
use Joomla\Plugin\RadicalMart\Wishboxcdekorderregistrator\Exception\EmptyProductCodeException;
use Joomla\Registry\Registry;
use stdClass;
use WishboxCdekSDK2\Model\Request\Orders\OrdersPost\PackageRequest;

/**
 * @property Registry|null $orderShippingMethodParams
 *
 * @since 1.0.0
 */
class RegistratorDelegate implements RegistratorDelegateInterface
{
	/**
	 * @var stdClass $order Order
	 *
	 * @since 1.0.0
	 */
	public stdClass $order;

	/**
	 * @param   stdClass  $order  Order
	 *
	 * @since 1.0.0
	 */
	public function __construct(stdClass $order)
	{
		$this->order = $order;
	}

	/**
	 * @param   string  $name  Name
	 *
	 * @return Registry|null
	 *
	 * @since 1.0.0
	 */
	public function __get(string $name): ?Registry
	{
		return match ($name)
		{
			'orderShippingMethodParams' => $this->order->shippingMethods[$this->order->formData['shipping']['id']]->params,
			default                     => null,
		};
	}

	/**
	 * @return stdClass
	 *
	 * @since 1.0.0
	 */
	public function getOrder(): stdClass
	{
		return $this->order;
	}

	/**
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public function getOrderComment(): string
	{
		return $this->order->formData['shipping']['note'] ?? '';
	}

	/**
	 * @return string
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function getSellerName(): string
	{
		$sellerName = (string) $this->orderShippingMethodParams->get('sellerName');

		if (empty($sellerName))
		{
			throw new Exception('Seller name in shipping method params must not be empty', 500);
		}

		return $sellerName;
	}

	/**
	 * @return integer
	 *
	 * @since 1.0.0
	 */
	public function getPackageLength(): int
	{
		return (int) $this->order->formData['shipping']['dimensions']['length'];
	}

	/**
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public function getRecipientPhone(): string
	{
		return $this->order->formData['contacts']['phone'];
	}

	/**
	 * @return boolean
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function isTariffFromDoor(): bool
	{
		$app = Factory::getApplication();
		$tariffCode = $this->order->shipping->order->price['tariffCode'];

		$tariffTable = $app->bootComponent('com_wishboxcdek')
			->getMVCFactory()
			->createTable('Tariff', 'Administrator');
		$tariffTable->load(['code' => $tariffCode]);

		return str_starts_with($tariffTable->mode, 'Д');
	}

	/**
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public function getOrderNumber(): string
	{
		$componentParams = ComponentHelper::getParams('com_wishboxradicalmartcdek');
		$prefix = $componentParams->get('order_number_prefix');

		return $prefix . $this->order->number;
	}

	/**
	 * @return integer
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function getTariffCode(): int
	{
		$tariffCode = 0;

		if (isset($this->order->shipping->order->price['tariffCode']))
		{
			$tariffCode = (int) $this->order->shipping->order->price['tariffCode'];
		}

		if (!$tariffCode)
		{
			throw new Exception('Order ' . $this->order->number . ' has not got tariffCode');
		}

		return $tariffCode;
	}

	/**
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public function getDeliveryPoint(): string
	{
		return $this->order->formData['shipping']['officeCode'];
	}

	/**
	 * @return integer
	 *
	 * @since 1.0.0
	 */
	public function getCityCode(): int
	{
		return $this->order->formData['shipping']['cityCode'];
	}

	/**
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function getDeliveryRecipientCost(): array
	{
		$deliveryRecipientCostParams = $this->orderShippingMethodParams->get('deliveryRecipientCost');

		if ($deliveryRecipientCostParams->useDefaultValue)
		{
			return ['value' => (float) $deliveryRecipientCostParams->defaultValue];
		}

		if ($deliveryRecipientCostParams->useTariffValue)
		{
			$tariffValue = (float) $this->order->shipping->order->price['tariff'];
			$tariffValueRatio = (float) $deliveryRecipientCostParams->tariffValueRatio;
			$value = $tariffValue * $tariffValueRatio;

			return ['value' => $value];
		}

		return ['value' => 0];
	}

	/**
	 * @return integer
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function getSellerInn(): int
	{
		$sellerInn = (int) $this->orderShippingMethodParams->get('sellerInn');

		if (!in_array(strlen(((string) $sellerInn)), [10, 12]))
		{
			throw new Exception('Seller INN in shipping method params must contain 10 or 12 digits', 500);
		}

		return $sellerInn;
	}

	/**
	 * @return string
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function getSellerPhone(): string
	{
		$sellerPhone = $this->orderShippingMethodParams->get('sellerPhone');

		if (empty($sellerPhone))
		{
			throw new Exception('Seller phone in shipping method params must not be empty.', 500);
		}

		return $sellerPhone;
	}

	/**
	 * @return string
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function getSenderName(): string
	{
		$senderName = (string) $this->orderShippingMethodParams->get('senderName');

		if (empty($senderName))
		{
			throw new Exception('Sender name in shipping method params must not be empty', 500);
		}

		return $senderName;
	}

	/**
	 * @return string
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function getSenderPhoneNumber(): string
	{
		$senderPhoneNumber = $this->orderShippingMethodParams->get('sender_phone_number', '');

		if (empty($senderPhoneNumber))
		{
			throw new Exception('Sender phone number in shipping method params must not be empty.', 500);
		}

		return $senderPhoneNumber;
	}

	/**
	 * @return string
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function getSenderPhoneAdditional(): string
	{
		$senderPhoneAdditional = $this->orderShippingMethodParams->get('sender_phone_additional', '');

		return $senderPhoneAdditional;
	}

	/**
	 * @return integer
	 *
	 * @since 1.0.0
	 */
	public function getSellerOwnershipForm(): int
	{
		return (int) $this->orderShippingMethodParams->get('sellerOwnerShipFormCode');
	}

	/**
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public function getRecipientName(): string
	{
		return $this->order->formData['contacts']['last_name'] . ' ' . $this->order->formData['contacts']['first_name'];
	}

	/**
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public function getRecipientEmail(): string
	{
		return $this->order->formData['contacts']['email'];
	}

	/**
	 * Returns total weight in grams
	 *
	 * @return integer
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function getTotalWeight(): int
	{
		if ($this->orderShippingMethodParams->get('useDefaultPackageWeight', 0))
		{
			return (int) $this->orderShippingMethodParams->get('defaultPackageWeight', 0);
		}

		$totalWeight = 0;

		foreach ($this->order->products as $product)
		{
			$productWeight = (int) WishboxradicalmartcdekHelper::getProductWeight($product, 'g');

			$totalWeight += $productWeight * $product->order['quantity'];
		}

		return $totalWeight;
	}

	/**
	 * @return ProductEntity[]
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function getProducts(): array
	{
		$products = [];

		$cdekProductCodeSource = $this->getCdekProductCodeSource();

		foreach ($this->order->products as $product)
		{
			if (empty($product->$cdekProductCodeSource))
			{
				throw new EmptyProductCodeException($product);
			}

			$productCost = (float) $product->price['base'];

			if ($this->orderShippingMethodParams->get('useDefaultProductCost', 0))
			{
				$productCost = (float) $this->orderShippingMethodParams->get('defaultProductCost', 0);
			}

			$productPayment = 0;

			$productPaymentParams = $this->orderShippingMethodParams->get('product_payment');

			if ($productPaymentParams->use_product_payment) // phpcs:ignore
			{
				$productPayment = (float) $product->price['base'];
			}

			$productEntity = new ProductEntity(
				$product->title,
				$product->$cdekProductCodeSource,
				(float) $product->price['base'],
				$productPayment,
				$productCost,
				WishboxradicalmartcdekHelper::getProductWeight($product, 'g'),
				(int) $product->order['quantity']
			);

			$products[] = $productEntity;
		}

		return $products;
	}

	/**
	 * @return string
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function getShipmentPoint(): string
	{
		$senderOfficeCode = $this->orderShippingMethodParams->get('senderOfficeCode', '');

		if ($senderOfficeCode == '-1')
		{
			throw new Exception('SenderOfficeCode is not set in shipping method params.', 500);
		}

		return $senderOfficeCode;
	}

	/**
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public function getRecipientCompany(): string
	{
		return $this->order->formData['contacts']['last_name'] . ' ' . $this->order->formData['contacts']['first_name'];
	}

	/**
	 * @param   string  $trackingNumber  Tracking number
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function setTrackingNumber(string $trackingNumber): void
	{
		/** @var OrderTable $orderTable */
		$orderTable = Factory::getApplication()
			->bootComponent('com_radicalmart')
			->getMVCFactory()
			->createTable('Order', 'Administrator');

		$orderTable->load($this->order->id);

		/** @noinspection PhpUndefinedFieldInspection */
		$registry = new Registry($orderTable->shipping);

		$data = $registry->get('data');

		if (!isset($data->trackingNumber) || $data->trackingNumber != $trackingNumber)
		{
			$data->trackingNumber = $trackingNumber;
			$registry->set('data', $data);
			$orderTable->shipping = $registry->toString();

			if (!$orderTable->store())
			{
				throw new Exception('$orderTable->store() return false', 500);
			}
		}
	}

	/**
	 * @return string
	 *
	 * @throws OrderServiceException
	 *
	 * @since 1.0.0
	 */
	public function getDeliveryAddress(): string
	{
		if (!isset($this->order->formData['shipping']['address']))
		{
			throw new OrderServiceException(
				'Order with has empty address',
				500,
				$this->order->id
			);
		}

		return $this->order->formData['shipping']['address'];
	}

	/**
	 * @return string
	 *
	 * @since 1.0.0
	 */
	private function getCdekProductCodeSource(): string
	{
		return $this->orderShippingMethodParams->get('cdekProductCodeSource', 'id');
	}

	/**
	 * @return float
	 *
	 * @since 1.0.0
	 */
	private function getTotalProductCost(): float
	{
		$totalProductCost = 0;

		if ($this->orderShippingMethodParams->get(
			'registerProductsAsOneSingleProduct',
			0
		))
		{
			if ($this->orderShippingMethodParams->get('useDefaultProductCost', 0))
			{
				return (float) $this->orderShippingMethodParams->get('defaultProductCost', 0);
			}
		}

		foreach ($this->order->products as $product)
		{
			$productCost = (float) $product->price['base'];

			if ($this->orderShippingMethodParams->get('useDefaultProductCost', 0))
			{
				$productCost = (float) $this->orderShippingMethodParams->get('defaultProductCost', 0);
			}

			$totalProductCost += $productCost;
		}

		return $totalProductCost;
	}

	/**
	 * @return float
	 *
	 * @since 1.0.0
	 */
	private function getTotalProductPrice(): float
	{
		$totalProductCost = 0;

		foreach ($this->order->products as $product)
		{
			$productCost = (float) $product->price['base'];

			if ($this->orderShippingMethodParams->get('useZeroProductCost', 0))
			{
				$productCost = 0;
			}

			$totalProductCost += $productCost;
		}

		return $totalProductCost;
	}

	/**
	 * @return float
	 *
	 * @since 1.0.0
	 */
	private function getTotalPayment(): float
	{
		return 0;
	}

	/**
	 * @param   float  $shippingPriceTariff  Shipping price tariff
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function setShippingPriceTariff(float $shippingPriceTariff): void
	{
		/** @var OrderTable $orderTable */
		$orderTable = Factory::getApplication()
			->bootComponent('com_radicalmart')
			->getMVCFactory()
			->createTable('Order', 'Administrator');

		$orderTable->load($this->order->id);

		/** @noinspection PhpUndefinedFieldInspection */
		$registry = new Registry($orderTable->shipping);

		$price = $registry->get('price');

		if ($price->tariff != $shippingPriceTariff)
		{
			$price->tariff = $shippingPriceTariff;
			$registry->set('price', $price);
			$orderTable->shipping = $registry->toString();

			$this->order->formData['shipping']['price']['tariff'] = $shippingPriceTariff;
			$this->order->shipping->order->price['tariff'] = $shippingPriceTariff;

			if (!$orderTable->store())
			{
				throw new Exception('$orderTable->store() return false', 500);
			}
		}
	}

	/**
	 * @return PackageRequest[]
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function getOrdersPostPackages(): array
	{
		$app = Factory::getApplication();

		/** @var GetOrdersPostPackagesEvent $event */
		$event = AbstractEvent::create(
			'onWishboxRadicalMartCdekRegistratorDelegateGetOrdersPostPackages',
			[
				'subject'       => $this,
				'eventClass'    => GetOrdersPostPackagesEvent::class
			]
		);

		/** @var GetOrdersPostPackagesEvent $event */
		$eventResult = $app->getDispatcher()
			->dispatch('onWishboxRadicalMartCdekRegistratorDelegateGetOrdersPostPackages', $event);

		return $eventResult->getPackageRequests();
	}

	/**
	 * @return PackageRequest[]
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function getOrdersPatchPackages(): array
	{
		$app = Factory::getApplication();

		/** @var GetOrdersPatchPackagesEvent $event */
		$event = AbstractEvent::create(
			'onWishboxRadicalMartCdekRegistratorDelegateGetOrdersPatchPackages',
			[
				'subject'       => $this,
				'eventClass'    => GetOrdersPatchPackagesEvent::class
			]
		);

		/** @var GetOrdersPatchPackagesEvent $event */
		$eventResult = $app->getDispatcher()
			->dispatch('onWishboxRadicalMartCdekRegistratorDelegateGetOrdersPatchPackages', $event);

		return $eventResult->getPackageRequests();
	}
}
