<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license     GNU General Public License version 2 or later
 */
namespace Joomla\Plugin\RadicalMart\Wishboxcdekorderregistrator\Service;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\RadicalMart\Administrator\Table\OrderTable;
use Joomla\Component\Wishboxcdek\Site\Entity\ProductEntity;
use Joomla\Component\Wishboxcdek\Site\Interface\RegistratorDelegateInterface;
use Joomla\Plugin\RadicalMart\Wishboxcdekorderregistrator\Exception\EmptyProductCodeException;
use Joomla\Registry\Registry;
use stdClass;

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
	private stdClass $order;

	/**
	 * @param   stdClass $order  Order
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
		$tariffCode = $this->order->formData['shipping']['tariffCode'];

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
		$plugin = PluginHelper::getPlugin('radicalmart', 'wishboxcdekorderregistrator');
		$pluginParams = new Registry($plugin->params);
		$prefix = $pluginParams->get('order_number_prefix');

		return $prefix . $this->order->number;
	}

	/**
	 * @return integer
	 *
	 * @since 1.0.0
	 */
	public function getTariffCode(): int
	{
		return $this->order->formData['shipping']['tariffCode'];
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
	 * @return integer
	 *
	 * @since 1.0.0
	 */
	public function getPackageWidth(): int
	{
		return (int) $this->order->formData['shipping']['dimensions']['width'];
	}

	/**
	 * @return integer
	 *
	 * @since 1.0.0
	 */
	public function getPackageHeight(): int
	{
		return (int) $this->order->formData['shipping']['dimensions']['height'];
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
		if ($this->orderShippingMethodParams->get(
			'registerProductsAsOneSingleProduct',
			0
		))
		{
			return [$this->getOneSingleProduct()];
		}

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

		$registry = new Registry($orderTable->shipping);
		$data = $registry->get('data');

		if ($data->trackingNumber != $trackingNumber)
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
	 * @return ProductEntity
	 *
	 * @since 1.0.0
	 */
	private function getOneSingleProduct(): ProductEntity
	{
		return new ProductEntity(
			'Product',
			time(),
			$this->getTotalProductPrice(),
			$this->getTotalPayment(),
			$this->getTotalProductCost(),
			$this->getTotalWeight(),
			1
		);
	}
}
