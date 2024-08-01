<?php
/**
 * @copyright   (c) 2013-2024 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Plugin\Radicalmart\Wishboxcdekpackagemultiple\Extension;

use Exception;
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Factory\MVCFactoryAwareTrait;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Wishboxcdek\Site\Trait\ApiClientTrait;
use Joomla\Component\Wishboxradicalmartcdek\Administrator\Service\CalculatorDelegate;
use Joomla\Component\Wishboxradicalmartcdek\Administrator\Service\RegistratorDelegate;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use stdClass;
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
class Wishboxcdekpackagemultiple extends CMSPlugin implements SubscriberInterface
{
	use MVCFactoryAwareTrait;
	use DatabaseAwareTrait;
	use ApiClientTrait;

	/**
	 * @var boolean
	 *
	 * @since 1.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * @param   DispatcherInterface  $subject  The object to observe
	 * @param   array                $config   An optional associative array of configuration settings.
	 *                                           Recognized key values include 'name', 'group', 'params', 'language'
	 *                                           (this list is not meant to be comprehensive).
	 *
	 * @since 1.0.0
	 */
	public function __construct(&$subject, $config = [])
	{
		parent::__construct($subject, $config);
	}

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
			'onWishboxRadicalMartCdekRegistratorDelegateGetOrdersPostPackages' => 'onWishboxRadicalMartCdekRegistratorDelegateGetOrdersPostPackages',
			'onWishboxRadicalMartCdekRegistratorDelegateGetOrdersPatchPackages'
			=> 'onWishboxRadicalMartCdekRegistratorDelegateGetOrdersPatchPackages',
			'onWishboxRadicalMartCdekCalculatorDelegateGetPackages' => 'onWishboxRadicalMartCdekCalculatorDelegateGetPackages'
		];
	}

	/**
	 * @param   Event  $event  Event
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function onWishboxRadicalMartCdekRegistratorDelegateGetOrdersPostPackages(Event $event): void
	{
		/** @var OrdersPostPackageRequest[] $packageRequests */
		$packageRequests = $event->getArgument(0);

		/** @var RegistratorDelegate $delegate */
		$delegate = $event->getArgument(1);

		$products = $delegate->getOrder()->products;

		foreach ($products as $product)
		{
			/** @var int $productQuantity Product quantity */
			$productQuantity = $product->order['quantity'];

			while ($productQuantity)
			{
				if (!self::packAllToOrdersPostPackages($packageRequests, $productQuantity, $product))
				{
					self::packMaxToOrdersPostPackages($packageRequests, $productQuantity, $product);
				}
			}
		}

		$event->setArgument(0, $packageRequests);
		$event->setArgument(1, $delegate);
	}

	/**
	 * @param   Event  $event  Event
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function onWishboxRadicalMartCdekRegistratorDelegateGetOrdersPatchPackages(Event $event): void
	{
		/** @var OrdersPatchPackageRequest[] $packageRequests */
		$packageRequests = $event->getArgument(0);

		/** @var RegistratorDelegate $delegate */
		$delegate = $event->getArgument(1);

		$apiClient = $this->getApiClient();
		sleep(1);
		$existingOrdersGetResponse = $apiClient->getOrderInfoByImNumber($delegate->getOrderNumber());
		$existingPackages = $existingOrdersGetResponse->getEntity()->getPackages();
		$existingPackageNumbers = [];

		foreach ($existingPackages as $existingPackage)
		{
			$existingPackageNumbers[] = $existingPackage->getNumber();
		}

		$products = $delegate->getOrder()->products;

		foreach ($products as $product)
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

		$event->setArgument(0, $packageRequests);
		$event->setArgument(1, $delegate);
	}

	/**
	 * @param   Event  $event  Event
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function onWishboxRadicalMartCdekCalculatorDelegateGetPackages(Event $event): void
	{
		/** @var TariffListPostPackageRequest[] $packageRequests */
		$packageRequests = $event->getArgument(0);

		/** @var CalculatorDelegate $delegate */
		$delegate = $event->getArgument(1);

		$products = $delegate->getProducts();

		foreach ($products as $product)
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

		$event->setArgument(0, $packageRequests);
		$event->setArgument(1, $delegate);
	}

	/**
	 * @param   Event  $event  Event
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function onContentPrepareForm(Event $event): void
	{
		/** @var Form $form */
		$form = $event->getArgument(0);

		/** @var array $data */
		$data = $event->getArgument(1);

		$formName = $form->getName();

		if ($formName == 'com_radicalmart.product')
		{
			if (!$form->loadFile(JPATH_SITE . '/plugins/' . $this->_type . '/' . $this->_name . '/forms/product.xml'))
			{
				echo '111';
				die;
			}
		}

		$event->setArgument(0, $form);
		$event->setArgument(1, $data);
	}

	/**
	 * @param   TariffListPostPackageRequest[]  $packageRequests  Array of packages
	 * @param   integer                         $productQuantity  Product quantity
	 * @param   stdClass                        $product          Product
	 *
	 * @return boolean
	 *
	 * @since 1.0.0
	 */
	private function packAll(array &$packageRequests, int &$productQuantity, stdClass $product): bool
	{
		$productPackageDimensions = self::getPackageDimensions($product);
		$productWeight = $product->shipping->get('weight');

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
	 * @since 1.0.0
	 */
	public function packMax(array &$packageRequests, int &$productQuantity, stdClass $product): void
	{
		/** @var array $productPackageTypes Product package types */
		$maxProductPackageTypes = self::getMaxPackageDimension($product);

		/** @var int $productQuantity Product weight */
		$productWeight = $product->shipping->get('weight');

		$weight = $productWeight * $productQuantity;

		$packageRequest = (new OrdersPostPackageRequest)
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
	 * @since 1.0.0
	 */
	private function packAllToOrdersPostPackages(array &$packageRequests, int &$productQuantity, stdClass $product): bool
	{
		$productPackageDimensions = self::getPackageDimensions($product);
		$productWeight = $product->shipping->get('weight');

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
	 * @since 1.0.0
	 */
	public function packMaxToOrdersPostPackages(array &$packageRequests, int &$productQuantity, stdClass $product): void
	{
		/** @var array $productPackageTypes Product package types */
		$maxProductPackageTypes = self::getMaxPackageDimension($product);

		/** @var integer $productQuantity Product weight */
		$productWeight = $product->shipping->get('weight');

		$weight = $productWeight * $productQuantity;

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
		$productWeight = $product->shipping->get('weight');

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

		/** @var integer $productQuantity Product weight */
		$productWeight = $product->shipping->get('weight');

		$weight = $productWeight * $productQuantity;

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
			->setWeight(1)
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
		return (array) $product->shipping->get('wishboxcdekpackagemultiple_dimensions');
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
