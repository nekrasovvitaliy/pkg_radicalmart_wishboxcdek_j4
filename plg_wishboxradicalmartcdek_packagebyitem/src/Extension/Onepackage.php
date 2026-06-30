<?php
/**
 * @copyright   (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Plugin\WishboxRadicalMartCdek\OnePackage\Extension;

use Exception;
use Joomla\CMS\MVC\Factory\MVCFactoryAwareTrait;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistration\Event\Service\OrderRegistrationAdapter\GetOrdersPatchPackagesEvent;
use Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistration\Event\Service\OrderRegistrationAdapter\GetOrdersPostPackagesEvent;
use WishboxCdekLibrary\Event\Service\CalculatorAdapter\GetPackagesEvent;
use WishboxCdekLibrary\Interface\OrderRegistrationAdapterInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Event\SubscriberInterface;
use WishboxCdekSDK2\Model\Request\Calculator\TariffListPost\PackageRequest as TariffListPostPackageRequest;
use WishboxCdekSDK2\Model\Request\Orders\OrdersPatch\PackageRequest as OrdersPatchPackageRequest;
use WishboxCdekSDK2\Model\Request\Orders\OrdersPost\MoneyRequest;
use WishboxCdekSDK2\Model\Request\Orders\OrdersPost\Package\ItemRequest;
use WishboxCdekSDK2\Model\Request\Orders\OrdersPost\PackageRequest as OrdersPostPackageRequest;
use WishboxCdekSDK2\Model\Response\Orders\OrdersGetResponse;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since 1.0.0
 */
final class OnePackage extends CMSPlugin implements SubscriberInterface
{
	use MVCFactoryAwareTrait;
	use DatabaseAwareTrait;

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
			'onWishboxRadicalMartCdekOrderRegistrationAdapterGetOrdersPostPackages'  => 'onWishboxRadicalMartCdekOrderRegistrationAdapterGetOrdersPostPackages',
			'onWishboxRadicalMartCdekOrderRegistrationAdapterGetOrdersPatchPackages' => 'onWishboxRadicalMartCdekOrderRegistrationAdapterGetOrdersPatchPackages',
			'onWishboxRadicalMartCdekCalculatorAdapterGetPackages'              => 'onWishboxRadicalMartCdekCalculatorAdapterGetPackages'
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
	public function onWishboxRadicalMartCdekOrderRegistrationAdapterGetOrdersPostPackages(GetOrdersPostPackagesEvent $event): void
	{
		/** @var OrderRegistrationAdapterInterface $adapter */
		$adapter = $event->getOrderRegistrationAdapter();

		$totalWeight = $adapter->getTotalWeight();

		$packageRequest = (new OrdersPostPackageRequest)
			->setNumber('1')
			->setWeight($totalWeight)
			->setHeight($packageHeight)
			->setWidth($packageWidth)
			->setLength($packageLength);

		$products = $adapter->getProducts();

		$items = [];

		foreach ($products as $product)
		{
			$items[] = (new ItemRequest)
				->setName($product->name)
				->setWareKey($product->code)
				->setPayment((new MoneyRequest)->setValue($product->payment))
				->setCost($product->cost)
				->setWeight($product->weight)
				->setAmount($product->quantity);
		}

		$packageRequest->setItems($items);
		$event->addResult($packageRequest);
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
	public function onWishboxRadicalMartCdekOrderRegistrationAdapterGetOrdersPatchPackages(GetOrdersPatchPackagesEvent $event): void
	{
		$adapter = $event->getOrderRegistrationAdapter();

		/** @var OrdersGetResponse $existingOrdersGetResponse */
		$existingOrdersGetResponse = $event->getArgument(3);

		$totalWeight = $adapter->getTotalWeight();

		$packageRequest = new OrdersPatchPackageRequest;
		$existingPackages = $existingOrdersGetResponse->getEntity()->getPackages();
		$existingPackage   = $existingPackages[0];
		$existingPackageId = $existingPackage->getPackageId();
		$packageRequest->setPackageId($existingPackageId)
			->setNumber('1')
			->setWeight($totalWeight)
			->setHeight($packageHeight)
			->setWidth($packageWidth)
			->setLength($packageLength);

		$products = $adapter->getProducts();

		$items = [];

		foreach ($products as $product)
		{
			$items[] = (new \WishboxCdekSDK2\Model\Request\Orders\OrdersPatch\Package\ItemRequest)
				->setName($product->name)
				->setWareKey($product->code)
				->setPayment((new \WishboxCdekSDK2\Model\Request\Orders\OrdersPatch\MoneyRequest)->setValue($product->payment))
				->setCost($product->cost)
				->setWeight($product->weight)
				->setAmount($product->quantity);
		}

		$packageRequest->setItems($items);
		$event->addResult($packageRequest);
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
	public function onWishboxRadicalMartCdekCalculatorAdapterGetPackages(GetPackagesEvent $event): void
	{
		$packageRequest = (new TariffListPostPackageRequest)
			->setWeight($this->getTotalWeight())
			->setLength((int) $dimensions->length)
			->setWidth((int) $dimensions->width)
			->setHeight((int) $dimensions->height);

		$event->setArgument(0, $packageRequest);
	}

	/**
	 * @return integer
	 *
	 * @since 1.0.0
	 */
	public function getPackageLength(): int
	{


		return (int) $dimensions->length;
	}
}
