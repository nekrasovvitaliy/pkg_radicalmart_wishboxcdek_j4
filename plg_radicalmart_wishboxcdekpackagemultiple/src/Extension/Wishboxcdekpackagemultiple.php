<?php
/**
 * @copyright   (c) 2013-2025 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Plugin\Radicalmart\WishboxCdekPackageMultiple\Extension;

use Exception;
use Joomla\CMS\Event\Model\PrepareFormEvent;
use Joomla\CMS\MVC\Factory\MVCFactoryAwareTrait;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\WishboxRadicalMartCdek\Administrator\Event\Model\CalculatorDelegate\GetPackagesEvent;
use Joomla\Component\WishboxRadicalMartCdek\Administrator\Helper\WishboxRadicalMartCdekHelper;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Event\SubscriberInterface;
use Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistrator\Administrator\Model\RegistratorDelegateModel;
use Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistrator\Event\Model\RegistratorDelegate\GetOrdersPatchPackagesEvent;
use Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistrator\Event\Model\RegistratorDelegate\GetOrdersPostPackagesEvent;
use stdClass;
use WishboxCdekSDK2\Factory\CdekClientV2FactoryAwareInterface;
use WishboxCdekSDK2\Factory\CdekClientV2FactoryAwareTrait;
use WishboxCdekSDK2\Model\Request\Orders\OrdersPost\MoneyRequest;
use WishboxCdekSDK2\Model\Request\Orders\OrdersPost\Package\ItemRequest;
use WishboxCdekSDK2\Model\Request\Orders\OrdersPost\PackageRequest as OrdersPostPackageRequest;
use WishboxCdekSDK2\Model\Request\Orders\OrdersPatch\PackageRequest as OrdersPatchPackageRequest;
use WishboxCdekSDK2\Model\Request\Calculator\TariffListPost\PackageRequest as TariffListPostPackageRequest;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since 1.0.0
 */
final class WishboxCdekPackageMultiple extends CMSPlugin implements SubscriberInterface, CdekClientV2FactoryAwareInterface
{
	use MVCFactoryAwareTrait;
	use DatabaseAwareTrait;
	use CdekClientV2FactoryAwareTrait;

	/**
	 * @var boolean
	 *
	 * @since 1.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * @return string[]
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onContentPrepareForm' => 'onContentPrepareForm',
			'onWishboxRadicalMartCdekRegistratorDelegateGetOrdersPostPackages'  => 'onWishboxRadicalMartCdekRegistratorDelegateGetOrdersPostPackages',
			'onWishboxRadicalMartCdekRegistratorDelegateGetOrdersPatchPackages' => 'onWishboxRadicalMartCdekRegistratorDelegateGetOrdersPatchPackages',
			'onWishboxRadicalMartCdekCalculatorDelegateGetPackages'             => 'onWishboxRadicalMartCdekCalculatorDelegateGetPackages'
		];
	}

	/**
	 * @param   GetOrdersPostPackagesEvent  $event  Event
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function onWishboxRadicalMartCdekRegistratorDelegateGetOrdersPostPackages(GetOrdersPostPackagesEvent $event): void
	{
		$delegate = $event->getRegistratorDelegate();

		/** @var OrdersPostPackageRequest[] $packageRequests */
		$packageRequests = [];

		$products = $delegate->getOrder()->products;
		$productsById = [];

		foreach ($products as $product)
		{
			if (!isset($productsById[$product->id]))
			{
				$productsById[$product->id] = clone $product;
			}
			else
			{
				$productsById[$product->id]->order['quantity'] += $product->order['quantity'];
			}
		}

		foreach ($productsById as $product)
		{
			/** @var integer $productQuantity Product quantity */
			$productQuantity = $product->order['quantity'];

			while ($productQuantity)
			{
				if (!self::packAllToOrdersPostPackages($packageRequests, $productQuantity, $product))
				{
					self::packMaxToOrdersPostPackages($packageRequests, $productQuantity, $product);
				}
			}
		}

		foreach ($packageRequests as $packageRequest)
		{
			$event->addResult($packageRequest);
		}
	}

	/**
	 * @param   GetOrdersPatchPackagesEvent  $event  Event
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function onWishboxRadicalMartCdekRegistratorDelegateGetOrdersPatchPackages(GetOrdersPatchPackagesEvent $event): void
	{
		/** @var OrdersPatchPackageRequest[] $packageRequests */
		$packageRequests = [];

		/** @var RegistratorDelegateModel $delegate */
		$delegate = $event->getRegistratorDelegate();

		$apiClient = $this->getCdekClientV2Factory()->getDefaultClient();
		sleep(1);
		$existingOrdersGetResponse = $apiClient->getOrderInfoByImNumber($delegate->getOrderNumber());
		$existingPackages = $existingOrdersGetResponse->getEntity()->getPackages();
		$existingPackageNumbers = [];

		foreach ($existingPackages as $existingPackage)
		{
			$existingPackageNumbers[] = $existingPackage->getNumber();
		}

		$products = $delegate->getOrder()->products;

		$productsById = [];

		foreach ($products as $product)
		{
			if (!isset($productsById[$product->id]))
			{
				$productsById[$product->id] = clone $product;
			}
			else
			{
				$productsById[$product->id]->order['quantity'] += $product->order['quantity'];
			}
		}

		foreach ($productsById as $product)
		{
			/** @var integer $productQuantity Product quantity */
			$productQuantity = $product->order['quantity'];

			while ($productQuantity)
			{
				if (!self::packAllToOrdersPatchPackages($packageRequests, $productQuantity, $product, $existingPackageNumbers))
				{
					self::packMaxToOrdersPatchPackages($packageRequests, $productQuantity, $product, $existingPackageNumbers);
				}
			}
		}

		foreach ($packageRequests as $packageRequest)
		{
			$event->addResult($packageRequest);
		}
	}

	/**
	 * @param   GetPackagesEvent  $event  Event
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function onWishboxRadicalMartCdekCalculatorDelegateGetPackages(GetPackagesEvent $event): void
	{
		$delegate = $event->getCalculatorDelegate();

		/** @var TariffListPostPackageRequest[] $packageRequests */
		$packageRequests = [];

		$products = $delegate->getProducts();
		$productsById = [];

		foreach ($products as $product)
		{
			if (!isset($productsById[$product->id]))
			{
				$productsById[$product->id] = clone $product;
			}
			else
			{
				$productsById[$product->id]->order['quantity'] += $product->order['quantity'];
			}
		}

		foreach ($productsById as $product)
		{
			/** @var int $productQuantity Product quantity */
			$productQuantity = $product->order['quantity'];

			while ($productQuantity)
			{
				if (!self::packAll($packageRequests, $productQuantity, $product))
				{
					self::packMax($packageRequests, $productQuantity, $product);
				}
			}
		}

		foreach ($packageRequests as $packageRequest)
		{
			$event->addResult($packageRequest);
		}
	}

	/**
	 * @param   PrepareFormEvent  $event  Event
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function onContentPrepareForm(PrepareFormEvent $event): void
	{
		$form = $event->getForm();

		$formName = $form->getName();

		if ($formName == 'com_radicalmart.product')
		{
			if (!$form->loadFile(JPATH_SITE . '/plugins/' . $this->_type . '/' . $this->_name . '/forms/product.xml'))
			{
				throw new Exception('Failed load file', 500);
			}
		}
	}

	/**
	 * @param   TariffListPostPackageRequest[]  $packageRequests  Array of packages
	 * @param   integer                         $productQuantity  Product quantity
	 * @param   stdClass                        $product          Product
	 *
	 * @return boolean
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	private function packAll(array &$packageRequests, int &$productQuantity, stdClass $product): bool
	{
		$productPackageDimensions = self::getPackageDimensions($product);
		$productWeight = WishboxRadicalMartCdekHelper::getProductWeight($product, 'g');

		foreach ($productPackageDimensions as $productPackageDimension)
		{
			$productPackageDimension = (array) $productPackageDimension;

			if ($productQuantity >= (int) $productPackageDimension['min_quantity']
				&& $productQuantity <= (int) $productPackageDimension['max_quantity'])
			{
				$weight = $productWeight * $productQuantity;

				$packageRequest = (new TariffListPostPackageRequest)
					->setWeight($weight)
					->setHeight($productPackageDimension['package_width'])
					->setWidth($productPackageDimension['package_height'])
					->setLength($productPackageDimension['package_length']);
				$packageRequests[] = $packageRequest;

				$productQuantity = 0;

				return true;
			}
		}

		return false;
	}

	/**
	 * @param   TariffListPostPackageRequest[]  $packageRequests  Array of package requests
	 * @param   integer                         $productQuantity  Product quantity
	 * @param   stdClass                        $product          Product
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function packMax(array &$packageRequests, int &$productQuantity, stdClass $product): void
	{
		/** @var array $productPackageTypes Product package types */
		$maxProductPackageTypes = self::getMaxPackageDimension($product);

		$productWeight = WishboxRadicalMartCdekHelper::getProductWeight($product, 'g');

		$weight = $productWeight * $maxProductPackageTypes['max_quantity'];

		$packageRequest = (new TariffListPostPackageRequest)
			->setWeight($weight)
			->setHeight($maxProductPackageTypes['package_width'])
			->setWidth($maxProductPackageTypes['package_height'])
			->setLength($maxProductPackageTypes['package_length']);

		$packageRequests[] = $packageRequest;

		$productQuantity -= $maxProductPackageTypes['max_quantity'];
	}

	/**
	 * @param   OrdersPostPackageRequest[]  $packageRequests  Array of packages
	 * @param   integer                     $productQuantity  Product quantity
	 * @param   stdClass                    $product          Product
	 *
	 * @return boolean
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	private function packAllToOrdersPostPackages(array &$packageRequests, int &$productQuantity, stdClass $product): bool
	{
		$productPackageDimensions = self::getPackageDimensions($product);
		$productWeight = WishboxRadicalMartCdekHelper::getProductWeight($product, 'g');

		foreach ($productPackageDimensions as $productPackageDimension)
		{
			$productPackageDimension = (array) $productPackageDimension;

			if ($productQuantity >= (int) $productPackageDimension['min_quantity']
				&& $productQuantity <= (int) $productPackageDimension['max_quantity'])
			{
				$weight = $productWeight * $productQuantity;
				$packageNumber = (string) (count($packageRequests) + 1);

				$packageRequest = (new OrdersPostPackageRequest)
					->setNumber($packageNumber)
					->setWeight($weight)
					->setHeight($productPackageDimension['package_width'])
					->setWidth($productPackageDimension['package_height'])
					->setLength($productPackageDimension['package_length']);

				$itemRequest = (new ItemRequest)
					->setName($product->title)
					->setWareKey($product->id)
					->setPayment((new MoneyRequest)->setValue(0))
					->setCost(1)
					->setWeight(1)
					->setAmount($productQuantity);

				$packageRequest->setItems([$itemRequest]);

				$packageRequests[] = $packageRequest;

				$productQuantity = 0;

				return true;
			}
		}

		return false;
	}

	/**
	 * @param   OrdersPostPackageRequest[]  $packageRequests  Array of package requests
	 * @param   integer                     $productQuantity  Product quantity
	 * @param   stdClass                    $product          Product
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function packMaxToOrdersPostPackages(array &$packageRequests, int &$productQuantity, stdClass $product): void
	{
		/** @var array $productPackageTypes Product package types */
		$maxProductPackageTypes = self::getMaxPackageDimension($product);

		$productWeight = WishboxRadicalMartCdekHelper::getProductWeight($product, 'g');

		$weight = $productWeight * $maxProductPackageTypes['max_quantity'];

		$packageNumber = (string) (count($packageRequests) + 1);

		$packageRequest = (new OrdersPostPackageRequest)
			->setNumber($packageNumber)
			->setWeight($weight)
			->setHeight($maxProductPackageTypes['package_width'])
			->setWidth($maxProductPackageTypes['package_height'])
			->setLength($maxProductPackageTypes['package_length']);

		$itemRequest = (new ItemRequest)
			->setName($product->title)
			->setWareKey($product->id)
			->setPayment((new MoneyRequest)->setValue(0))
			->setCost(1)
			->setWeight(1)
			->setAmount($maxProductPackageTypes['max_quantity']);

		$packageRequest->setItems([$itemRequest]);

		$packageRequests[] = $packageRequest;

		$productQuantity -= $maxProductPackageTypes['max_quantity'];
	}

	/**
	 * @param   OrdersPatchPackageRequest[]  $packageRequests         Array of packages
	 * @param   integer                      $productQuantity         Product quantity
	 * @param   stdClass                     $product                 Product
	 * @param   string[]                     $existingPackageNumbers  Existing package numbers
	 *
	 * @return boolean
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	private function packAllToOrdersPatchPackages(
		array &$packageRequests,
		int &$productQuantity,
		stdClass $product,
		array &$existingPackageNumbers
	): bool
	{
		$productPackageDimensions = self::getPackageDimensions($product);
		$productWeight = WishboxRadicalMartCdekHelper::getProductWeight($product, 'g');

		foreach ($productPackageDimensions as $productPackageDimension)
		{
			$productPackageDimension = (array) $productPackageDimension;

			if ($productQuantity >= (int) $productPackageDimension['min_quantity']
				&& $productQuantity <= (int) $productPackageDimension['max_quantity'])
			{
				$weight = $productWeight * $productQuantity;

				$number = count($packageRequests) + 1;

				while (in_array((string) $number, $existingPackageNumbers))
				{
					$number++;
				}

				$existingPackageNumbers[] = $number;

				$packageRequest = (new OrdersPostPackageRequest)
					->setNumber($number)
					->setWeight($weight)
					->setHeight($productPackageDimension['package_width'])
					->setWidth($productPackageDimension['package_height'])
					->setLength($productPackageDimension['package_length']);

				$itemRequest = (new ItemRequest)
					->setName($product->title)
					->setWareKey($product->id)
					->setPayment((new MoneyRequest)->setValue(0))
					->setCost(1)
					->setWeight(1)
					->setAmount($productQuantity);

				$packageRequest->setItems([$itemRequest]);

				$packageRequests[] = $packageRequest;

				$productQuantity = 0;

				return true;
			}
		}

		return false;
	}

	/**
	 * @param   OrdersPatchPackageRequest[]  $packageRequests         Array of package requests
	 * @param   integer                      $productQuantity         Product quantity
	 * @param   stdClass                     $product                 Product
	 * @param   string[]                     $existingPackageNumbers  Existing package numbers
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function packMaxToOrdersPatchPackages(
		array &$packageRequests,
		int &$productQuantity,
		stdClass $product,
		array &$existingPackageNumbers
	): void
	{
		/** @var array $productPackageTypes Product package types */
		$maxProductPackageTypes = self::getMaxPackageDimension($product);

		$productWeight = WishboxRadicalMartCdekHelper::getProductWeight($product, 'g');
		$weight = $productWeight * $maxProductPackageTypes['max_quantity'];
		$number = count($packageRequests) + 1;

		while (in_array((string) $number, $existingPackageNumbers))
		{
			$number++;
		}

		$existingPackageNumbers[] = $number;

		$packageRequest = (new OrdersPatchPackageRequest)
			->setNumber($number)
			->setWeight($weight)
			->setHeight($maxProductPackageTypes['package_width'])
			->setWidth($maxProductPackageTypes['package_height'])
			->setLength($maxProductPackageTypes['package_length']);

		$itemRequest = (new ItemRequest)
			->setName($product->title)
			->setWareKey($product->id)
			->setPayment((new MoneyRequest)->setValue(0))
			->setCost(1)
			->setWeight($productWeight)
			->setAmount($maxProductPackageTypes['max_quantity']);

		$packageRequest->setItems([$itemRequest]);

		$packageRequests[] = $packageRequest;

		$productQuantity -= $maxProductPackageTypes['max_quantity'];
	}

	/**
	 * @param   stdClass  $product  Product
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function getPackageDimensions(stdClass $product): array
	{
		return (array) $product->shipping->get('wishboxcdekpackagemultiple.dimensions');
	}

	/**
	 * @param   stdClass  $product  Product
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function getMaxPackageDimension(stdClass $product): array
	{
		$dimensions = self::getPackageDimensions($product);

		usort(
			$dimensions,
			function ($a, $b)
			{
				$a = (array) $a;
				$b = (array) $b;

				if ($b['max_quantity'] == $a['max_quantity'])
				{
					return 0;
				}

				return ($b['max_quantity'] > $a['max_quantity']) ? 1 : -1;
			}
		);

		return (array) $dimensions[0];
	}
}
