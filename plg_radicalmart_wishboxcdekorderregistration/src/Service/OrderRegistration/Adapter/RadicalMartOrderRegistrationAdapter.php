<?php
/**
 * @copyright   (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license         GNU General Public License version 2 or later
 */

namespace Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistration\Service\OrderRegistration\Adapter;

use Exception;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Factory;
use Joomla\Component\RadicalMart\Administrator\Extension\RadicalMartComponent;
use Joomla\Component\RadicalMart\Administrator\Table\OrderTable;
use Joomla\Component\WishboxCdek\Administrator\Extension\WishboxCdekComponent;
use Joomla\Component\WishboxCdek\Administrator\Table\TariffTable;
use Joomla\Component\WishboxCdek\Site\Entity\ProductEntity;
use Joomla\Event\DispatcherAwareInterface;
use Joomla\Event\DispatcherAwareTrait;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistration\Event\Service\OrderRegistrationAdapter\GetOrdersPatchPackagesEvent;
use Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistration\Event\Service\OrderRegistrationAdapter\GetOrdersPostPackagesEvent;
use Joomla\Component\WishboxRadicalMartCdek\Administrator\Exception\OrderServiceException;
use Joomla\Component\WishboxRadicalMartCdek\Administrator\Helper\WishboxRadicalMartCdekHelper;
use Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistration\Exception\EmptyProductCodeException;
use Joomla\Registry\Registry;
use stdClass;
use WishboxCdek\Request\Order\MoneyDto;
use WishboxCdek\Request\Order\PackageRequestDto;
use WishboxCdek\Request\Order\PhoneDto;
use WishboxCdekLibrary\Interface\OrderRegistrationAdapterInterface;

/**
 * @property Registry|null $orderShippingMethodParams
 *
 * @since 1.0.0
 */
class RadicalMartOrderRegistrationAdapter implements OrderRegistrationAdapterInterface, DispatcherAwareInterface
{
	use DispatcherAwareTrait;

	/**
	 * @var stdClass $order Order
	 *
	 * @since 1.0.0
	 */
	public stdClass $order {
		get {
			return $this->order;
		}
	}

	/**
	 * Class constructor.
	 *
	 * @param   stdClass             $order       Order
	 * @param   DispatcherInterface  $dispatcher  Dispatcher
	 *
	 * @since 1.0.0
	 */
	public function __construct(DispatcherInterface $dispatcher)
	{
		$this->setDispatcher($dispatcher);
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
			default => null,
		};
	}

	/**
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public function setOrder(stdClass $order): string
	{
		return $this->order = $order;
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
		$sellerName = (string) $this->orderShippingMethodParams->get('seller_name');

		if (empty($sellerName))
		{
			throw new Exception('Seller name in shipping method params must not be empty', 500);
		}

		return $sellerName;
	}

	/**
	 * @return PhoneDto
	 *
	 * @since 1.0.0
	 */
	public function getRecipientPhone(): PhoneDto
	{
		return new PhoneDto($this->order->formData['contacts']['phone']);
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
		$app        = Factory::getApplication();
		$tariffCode = $this->order->shipping->order->price['tariff_code'];

		/** @var WishboxCdekComponent $component */
		$component = $app->bootComponent('com_wishboxcdek');

		/** @var TariffTable $tariffTable */
		$tariffTable = $component->getMVCFactory()
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
		$prefix          = $componentParams->get('wishboxradicalmartcdekorderregistration.order_number_prefix');

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

		if (isset($this->order->shipping->order->price['tariff_code']))
		{
			$tariffCode = (int) $this->order->shipping->order->price['tariff_code'];
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
		return $this->order->formData['shipping']['office_code'];
	}

	/**
	 * @return integer
	 *
	 * @since 1.0.0
	 */
	public function getCityCode(): int
	{
		return $this->order->formData['shipping']['city_code'];
	}

	/**
	 * @return MoneyDto
	 *
	 * @since 1.0.0
	 */
	public function getDeliveryRecipientCost(): MoneyDto
	{
		if ($this->orderShippingMethodParams->get('delivery_recipient_cost.use_default_value'))
		{
			return new MoneyDto((float) $this->orderShippingMethodParams->get('delivery_recipient_cost.default_value'));
		}

		if ($this->orderShippingMethodParams->get('delivery_recipient_cost.use_tariff_value'))
		{
			$tariffValue      = (float) $this->order->shipping->order->price['tariff'];
			$tariffValueRatio = (float) $this->orderShippingMethodParams->get('delivery_recipient_cost.tariff_value_ratio');
			$value            = $tariffValue * $tariffValueRatio;

			return new MoneyDto($value);
		}

		return new MoneyDto(0);
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
		$sellerInn = (int) $this->orderShippingMethodParams->get('seller_inn');

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
		$sellerPhone = $this->orderShippingMethodParams->get('seller_phone');

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
		$senderName = (string) $this->orderShippingMethodParams->get('sender_name');

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
		return $this->orderShippingMethodParams->get('sender_phone_additional', '');
	}

	/**
	 * @return integer
	 *
	 * @since 1.0.0
	 */
	public function getSellerOwnershipForm(): int
	{
		return (int) $this->orderShippingMethodParams->get('seller_owner_ship_form_code');
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
		$totalWeight = 0;

		foreach ($this->order->products as $product)
		{
			$productWeight = (int) WishboxRadicalMartCdekHelper::getProductWeight($product, 'g');

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

			$productCost    = (float) $product->price['base'];
			$productPayment = 0;

			$productEntity = new ProductEntity(
				$product->title,
				$product->$cdekProductCodeSource,
				(float) $product->price['base'],
				$productPayment,
				$productCost,
				WishboxRadicalMartCdekHelper::getProductWeight($product, 'g'),
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
		$senderOfficeCode = $this->orderShippingMethodParams->get('sender_office_code', '');

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
		$app = Factory::getApplication();

		/** @var RadicalMartComponent $component */
		$component = $app->bootComponent('com_radicalmart');

		/** @var OrderTable $orderTable */
		$orderTable = $component->getMVCFactory()
			->createTable('Order', 'Administrator');

		$orderTable->load($this->order->id);

		$registry = new Registry($orderTable->shipping);

		$oldTrackingNumber = $registry->get('data.tracking_number', '');

		if ($oldTrackingNumber != $trackingNumber)
		{
			$registry->set('data.tracking_number', $trackingNumber);
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
		return $this->orderShippingMethodParams->get('cdek_product_code_source', 'id');
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
		$app = Factory::getApplication();

		/** @var RadicalMartComponent $component */
		$component = $app->bootComponent('com_radicalmart');

		/** @var OrderTable $orderTable */
		$orderTable = $component->getMVCFactory()
			->createTable('Order', 'Administrator');

		$orderTable->load($this->order->id);

		$registry = new Registry($orderTable->shipping);

		$price = $registry->get('price');

		if ($price->tariff != $shippingPriceTariff)
		{
			$price->tariff = $shippingPriceTariff;
			$registry->set('price', $price);
			$orderTable->shipping = $registry->toString();

			$this->order->formData['shipping']['price']['tariff'] = $shippingPriceTariff;
			$this->order->shipping->order->price['tariff']        = $shippingPriceTariff;

			if (!$orderTable->store())
			{
				throw new Exception('$orderTable->store() return false', 500);
			}
		}
	}

	/**
	 * @return PackageRequestDto[]
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
			'onWishboxRadicalMartCdekOrderRegistrationAdapterGetOrdersPostPackages',
			[
				'subject'    => $this,
				'eventClass' => GetOrdersPostPackagesEvent::class
			]
		);

		/** @var GetOrdersPostPackagesEvent $eventResult */
		$eventResult = $app->getDispatcher()->dispatch($event->getName(), $event);

		return $eventResult->getPackageRequests();
	}

	/**
	 * @return PackageRequestDto[]
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
			'onWishboxRadicalMartCdekOrderRegistrationAdapterGetOrdersPatchPackages',
			[
				'subject'    => $this,
				'eventClass' => GetOrdersPatchPackagesEvent::class
			]
		);

		/** @var GetOrdersPatchPackagesEvent $eventResult */
		$eventResult = $app->getDispatcher()->dispatch($event->getName(), $event);

		return $eventResult->getPackageRequests();
	}
}
