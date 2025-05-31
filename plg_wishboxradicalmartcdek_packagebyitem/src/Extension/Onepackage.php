<?php
/**
 * @copyright   (c) 2013-2025 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Plugin\WishboxRadicalMartCdek\OnePackage\Extension;

use Exception;
use Joomla\CMS\MVC\Factory\MVCFactoryAwareTrait;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\WishboxCdek\Site\Interface\RegistratorDelegateInterface;
use Joomla\Component\WishboxRadicalMartCdek\Administrator\Event\Model\CalculatorDelegate\GetPackagesEvent;
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
		/** @var RegistratorDelegateInterface $delegate */
		$delegate = $event->getRegistratorDelegate();

		$totalWeight   = $delegate->getTotalWeight();

		$packageRequest = (new OrdersPostPackageRequest)
			->setNumber('1')
			->setWeight($totalWeight)
			->setHeight($packageHeight)
			->setWidth($packageWidth)
			->setLength($packageLength);

		$products = $delegate->getProducts();

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
	public function onWishboxRadicalMartCdekRegistratorDelegateGetOrdersPatchPackages(GetOrdersPatchPackagesEvent $event): void
	{
		$delegate = $event->getRegistratorDelegate();

		/** @var OrdersGetResponse $existingOrdersGetResponse */
		$existingOrdersGetResponse = $event->getArgument(3);

		$totalWeight   = $delegate->getTotalWeight();

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

		$products = $delegate->getProducts();

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
	public function onWishboxRadicalMartCdekCalculatorDelegateGetPackages(GetPackagesEvent $event): void
	{
		$delegate = $event->getCalculatorDelegate();



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
