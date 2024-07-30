<?php
/**
 * @copyright   (с) 2013-2024 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later
 */
namespace Joomla\Component\Wishboxradicalmartcdek\Administrator\Service;

use Exception;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\Component\RadicalMart\Administrator\Table\OrderTable;
use Joomla\Component\Wishboxcdek\Site\Entity\ProductEntity;
use Joomla\Component\Wishboxcdek\Site\Interface\RegistratorDelegateInterface;
use Joomla\Component\Wishboxradicalmartcdek\Administrator\Exception\OrderServiceException;
use Joomla\Plugin\RadicalMart\Wishboxcdekorderregistrator\Exception\EmptyProductCodeException;
use Joomla\Registry\Registry;
use stdClass;
use WishboxCdekSDK2\Model\Request\Calculator\TariffListPost\PackageRequest;

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
			default => null,
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
	 * @since 1.0.0
	 */
	public function getSellerName(): string
	{
		return $this->orderShippingMethodParams->get('sellerName');
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
	 * @since 1.0.0
	 */
	public function getSellerInn(): int
	{
		return (int) $this->orderShippingMethodParams->get('sellerInn');
	}

	/**
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public function getSellerPhone(): string
	{
		return $this->orderShippingMethodParams->get('sellerPhone');
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
	 * @return integer
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
			$totalWeight += (float) $product->shipping->get('weight', 0) * $product->order['quantity'];
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

			$productEntity = new ProductEntity(
				$product->title,
				$product->$cdekProductCodeSource,
				(float) $product->price['base'],
				0,
				$productCost,
				(int) $product->shipping->get('weight', 0),
				(int) $product->order['quantity']
			);

			$products[] = $productEntity;
		}

		return $products;
	}

	/**
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public function getShipmentPoint(): string
	{
		return $this->orderShippingMethodParams->get('senderOfficeCode', '');
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

		$data     = $registry->get('data');

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
		/** @var PackageRequest[] $packages */
		$packages = [];

		$app = Factory::getApplication();
		$app->triggerEvent('onWishboxRadicalMartCdekRegistratorDelegateGetOrdersPostPackages', [&$packages, $this]);

		return $packages;
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
		/** @var PackageRequest[] $packages */
		$packages = [];

		$app = Factory::getApplication();
		$app->triggerEvent('onWishboxRadicalMartCdekRegistratorDelegateGetOrdersPatchPackages', [&$packages, $this]);

		return $packages;
	}
}
