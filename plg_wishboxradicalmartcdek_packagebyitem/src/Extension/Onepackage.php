<?php
/**
 * @copyright   (c) 2013-2024 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Plugin\Wishboxradicalmartcdek\Onepackage\Extension;

use Exception;
use Joomla\CMS\MVC\Factory\MVCFactoryAwareTrait;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Wishboxcdek\Site\Interface\CalculatorDelegateInterface;
use Joomla\Component\Wishboxcdek\Site\Interface\RegistratorDelegateInterface;
use Joomla\Component\Wishboxradicalmartcdek\Administrator\Helper\WishboxradicalmartcdekHelper;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use WishboxCdekSDK2\Model\Request\Orders\OrdersPost\MoneyRequest;
use WishboxCdekSDK2\Model\Request\Orders\OrdersPost\Package\ItemRequest;
use WishboxCdekSDK2\Model\Request\Orders\OrdersPost\PackageRequest;
use WishboxCdekSDK2\Model\Response\Orders\OrdersGetResponse;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since 1.0.0
 */
class Onepackage extends CMSPlugin implements SubscriberInterface
{
	use MVCFactoryAwareTrait;
	use DatabaseAwareTrait;

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
			'onWishboxCdekOrdersPostRequestCreatorBeforeGetPackages'    => 'onWishboxCdekOrdersPostRequestCreatorBeforeGetPackages',
			'onWishboxCdekOrdersPatchRequestCreatorBeforeGetPackages'   => 'onWishboxCdekOrdersPatchRequestCreatorBeforeGetPackages',
			'onWishboxRadicalMartCdekCalculatorDelegateGetPackages'     => 'onWishboxRadicalMartCdekCalculatorDelegateGetPackages'
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
	public function onWishboxCdekOrdersPostRequestCreatorBeforeGetPackages(Event $event): void
	{
		/** @var PackageRequest[] $packageRequests */
		$packageRequests = $event->getArgument(0);

		/** @var RegistratorDelegateInterface $delegate */
		$delegate = $event->getArgument(1);

		$totalWeight   = $delegate->getTotalWeight();

		// Создаем данные посылки. Место
		$package = (new PackageRequest)
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

		$package->setItems($items);
		$packageRequests = [$package];

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
	public function onWishboxCdekOrdersPatchRequestCreatorBeforeGetPackages(Event $event): void
	{
		/** @var PackageRequest[] $cityResponses */
		$packageRequest = $event->getArgument(0);

		/** @var RegistratorDelegateInterface $delegate */
		$delegate = $event->getArgument(1);

		/** @var OrdersGetResponse $existingOrdersGetResponse */
		$existingOrdersGetResponse = $event->getArgument(3);

		$totalWeight   = $delegate->getTotalWeight();

		$package = new \WishboxCdekSDK2\Model\Request\Orders\OrdersPatch\PackageRequest;
		$existingPackages = $existingOrdersGetResponse->getEntity()->getPackages();
		$existingPackage   = $existingPackages[0];
		$existingPackageId = $existingPackage->getPackageId();
		$package->setPackageId($existingPackageId)
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

		$package->setItems($items);
		$packageRequests = [$package];

		$event->setArgument(0, $packageRequests);
		$event->setArgument(1, $delegate);
		$event->setArgument(3, $existingOrdersGetResponse);
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
		/** @var PackageRequest[] $cityResponses */
		$packageRequest = $event->getArgument(0);

		/** @var CalculatorDelegateInterface $delegate */
		$delegate = $event->getArgument(1);



		if ($delegate->method->params->get('useTheOnlyOnePackage'))
		{
			$dimensions = $delegate->method->params->get('defaultDimensions');

			$package = (new \WishboxCdekSDK2\Model\Request\Calculator\TariffListPost\PackageRequest)
				->setWeight($this->getTotalWeight())
				->setLength((int) $dimensions->length)
				->setWidth((int) $dimensions->width)
				->setHeight((int) $dimensions->height);
			$packages[] = $package;
		}
		else
		{
			foreach ($this->products as $product)
			{
				for ($k = 0; $k < $product->order['quantity']; $k++)
				{
					$package = new PackageRequest;
					$productWeight = WishboxradicalmartcdekHelper::getProductWeight($product, 'g');

					$package->setWeight($productWeight);

					if ($this->useDimensions())
					{
						$package->setWidth($product->shipping->get('width'))
							->setHeight($product->shipping->get('height'))
							->setLength($product->shipping->get('length'));
					}

					$packages[] = $package;
				}
			}
		}

		$event->setArgument(0, $packageRequests);
		$event->setArgument(1, $delegate);
	}

	/**
	 * @return integer
	 *
	 * @since 1.0.0
	 */
	public function getPackageLength(): int
	{
		$dimensions = $this->method->params->get('defaultPackageDimensions');

		return (int) $dimensions->length;
	}
}
