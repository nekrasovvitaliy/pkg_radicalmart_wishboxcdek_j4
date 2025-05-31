<?php
/**
 * @copyright   (c) 2013-2025 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Plugin\Radicalmart\WishboxCdekOnePackage\Extension;

use Exception;
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Factory\MVCFactoryAwareTrait;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\WishboxRadicalMartCdek\Administrator\Event\Model\CalculatorDelegate\GetPackagesEvent;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Event\SubscriberInterface;
use Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistrator\Event\Model\RegistratorDelegate\GetOrdersPatchPackagesEvent;
use Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistrator\Event\Model\RegistratorDelegate\GetOrdersPostPackagesEvent;
use Joomla\Registry\Registry;
use stdClass;
use WishboxCdekSDK2\Factory\CdekClientV2FactoryAwareInterface;
use WishboxCdekSDK2\Factory\CdekClientV2FactoryAwareTrait;
use WishboxCdekSDK2\Model\Request\Orders\OrdersPatch\MoneyRequest as OrdersPatchMoneyRequest;
use WishboxCdekSDK2\Model\Request\Orders\OrdersPatch\PackageRequest as OrdersPatchPackageRequest;
use WishboxCdekSDK2\Model\Request\Orders\OrdersPatch\Package\ItemRequest as OrdersPatchPackageItemRequest;
use WishboxCdekSDK2\Model\Request\Orders\OrdersPost\MoneyRequest as OrdersPostMoneyRequest;
use WishboxCdekSDK2\Model\Request\Orders\OrdersPost\Package\ItemRequest as OrdersPostPackageItemRequest;
use WishboxCdekSDK2\Model\Request\Orders\OrdersPost\PackageRequest as OrdersPostPackageRequest;
use WishboxCdekSDK2\Model\Request\Calculator\TariffListPost\PackageRequest as TariffListPostPackageRequest;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since 1.0.0
 */
final class WishboxCdekOnePackage extends CMSPlugin implements SubscriberInterface, CdekClientV2FactoryAwareInterface
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
			'onWishboxRadicalMartCdekRegistratorDelegateGetOrdersPostPackages'  => 'onWishboxRadicalMartCdekRegistratorDelegateGetOrdersPostPackages',
			'onWishboxRadicalMartCdekRegistratorDelegateGetOrdersPatchPackages' => 'onWishboxRadicalMartCdekRegistratorDelegateGetOrdersPatchPackages',
			'onWishboxRadicalMartCdekCalculatorDelegateGetPackages'             => 'onWishboxRadicalMartCdekCalculatorDelegateGetPackages',
			'onRadicalMartPrepareMethodForm'                                    => 'onRadicalMartPrepareMethodForm',
			'onRadicalMartGetOrderForm'                                         => 'onRadicalMartGetOrderForm',
			'onRadicalMartBeforeOrderSave'                                      => 'onBeforeOrderSave'
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

		$useDefaultPackageWeight = $delegate->order->shipping->params->get('wishboxcdekonepackage.use_default_weight');

		if ($useDefaultPackageWeight)
		{
			$weight = $delegate->order->shipping->params->get('wishboxcdekonepackage.default_weight');
		}
		else
		{
			$weight = $delegate->getTotalWeight();
		}

		if (isset($delegate->order->formData['shipping']['wishboxcdekonepackage']['dimensions'])
			&& is_array($delegate->order->formData['shipping']['wishboxcdekonepackage']['dimensions'])
			&& (!empty($delegate->order->formData['shipping']['wishboxcdekonepackage']['dimensions']['width'])
			&& !empty($delegate->order->formData['shipping']['wishboxcdekonepackage']['dimensions']['height'])
			&& !empty($delegate->order->formData['shipping']['wishboxcdekonepackage']['dimensions']['length'])))
		{
			$dimensions = $delegate->order->formData['shipping']['wishboxcdekonepackage']['dimensions'];
		}
		else
		{
			$dimensions = (array) $delegate->order->shipping->params->get('wishboxcdekonepackage.default_dimensions');
		}

		$package = (new OrdersPostPackageRequest)
			->setNumber('1')
			->setWeight($weight)
			->setHeight($dimensions['height'])
			->setWidth($dimensions['width'])
			->setLength($dimensions['length']);

		$products = $delegate->getProducts();

		$items = [];

		foreach ($products as $product)
		{
			$productCost = $product->cost;

			if ($delegate->order->shipping->params->get('wishboxcdekonepackage.use_default_product_cost', 0))
			{
				$productCost = (float) $delegate->order->shipping->params->get('wishboxcdekonepackage.default_product_cost', 0);
			}

			$payment = (new OrdersPostMoneyRequest)
				->setValue(0)
				->setVatSum(0);

			if ($delegate->order->shipping->params->get('wishboxcdekonepackage.product_payment.use_product_payment', 0))
			{
				$vatRate = (int) $delegate->order->shipping->params->get('product_payment.product_payment_vat_rate');
				$vatSum = (float) $product->price * (1 - (100 / (100 + $vatRate)));
				$payment->setVatSum($vatSum)
					->setVatRate($vatRate);
			}

			$items[] = (new OrdersPostPackageItemRequest)
				->setName($product->name)
				->setWareKey($product->code)
				->setPayment($payment)
				->setCost($productCost)
				->setWeight($product->weight)
				->setAmount($product->quantity);
		}

		$package->setItems($items);

		$event->addResult($package);
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

		$apiClient = $this->getCdekClientV2Factory()->getDefaultClient();
		$existingOrdersGetResponse = $apiClient->getOrderInfoByImNumber($delegate->getOrderNumber());

		if ($delegate->order->shipping->params->get('wishboxcdekonepackage.use_default_weight'))
		{
			$weight = $delegate->order->shipping->params->get('wishboxcdekonepackage.default_weight');
		}
		else
		{
			$weight = $delegate->getTotalWeight();
		}

		if (isset($delegate->order->formData['shipping']['wishboxcdekonepackage']['dimensions'])
			&& is_array($delegate->order->formData['shipping']['wishboxcdekonepackage']['dimensions'])
			&& (!empty($delegate->order->formData['shipping']['wishboxcdekonepackage']['dimensions']['width'])
			&& !empty($delegate->order->formData['shipping']['wishboxcdekonepackage']['dimensions']['height'])
			&& !empty($delegate->order->formData['shipping']['wishboxcdekonepackage']['dimensions']['length'])))
		{
			$dimensions = $delegate->order->formData['shipping']['wishboxcdekonepackage']['dimensions'];
		}
		else
		{
			$dimensions = (array) $delegate->order->shipping->params->get('wishboxcdekonepackage.default_dimensions');
		}

		$package = new OrdersPatchPackageRequest;
		$existingPackages = $existingOrdersGetResponse->getEntity()->getPackages();
		$existingPackage = $existingPackages[0];
		$existingPackageId = $existingPackage->getPackageId();
		$package->setPackageId($existingPackageId)
			->setNumber('1')
			->setWeight($weight)
			->setHeight($dimensions['height'])
			->setWidth($dimensions['width'])
			->setLength($dimensions['length']);

		$products = $delegate->getProducts();

		$items = [];

		foreach ($products as $product)
		{
			$paymentRequest = (new OrdersPatchMoneyRequest)
				->setValue(0)
				->setVatSum(0);

			if ($delegate->order->shipping->params->get('product_payment.use_product_payment', 0))
			{
				$vatRate = (int) $delegate->order->shipping->params->get('product_payment.product_payment_vat_rate');
				$vatSum = (float) $product->price * (1 - (100 / (100 + $vatRate)));
				$paymentRequest->setVatSum($vatSum)
					->setVatRate($vatRate);
			}

			$items[] = (new OrdersPatchPackageItemRequest)
				->setName($product->name)
				->setWareKey($product->code)
				->setPayment($paymentRequest)
				->setCost($product->cost)
				->setWeight($product->weight)
				->setAmount($product->quantity);
		}

		$package->setItems($items);

		$event->addResult($package);
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

		if ($delegate->method->params->get('wishboxcdekonepackage.use_default_weight'))
		{
			$weight = (int) $delegate->getMethod()->params->get('wishboxcdekonepackage.default_weight');
		}
		else
		{
			$weight = $delegate->getTotalWeight();
		}

		$formData = $delegate->getFormData();

		if (isset($formData['shipping']['wishboxcdekonepackage']['dimensions']))
		{
			$dimensions = $formData['shipping']['wishboxcdekonepackage']['dimensions'];
		}
		else
		{
			$dimensions = (array) $delegate->method->params->get('wishboxcdekonepackage.default_dimensions');
		}

		if (empty($dimensions['height']) || empty($dimensions['height']) || empty($dimensions['height']))
		{
			$dimensions = (array) $delegate->method->params->get('wishboxcdekonepackage.default_dimensions');
		}

		$packageRequest = (new TarifflistPostPackageRequest)
			->setWeight($weight)
			->setHeight($dimensions['height'])
			->setWidth($dimensions['width'])
			->setLength($dimensions['length']);

		$event->addResult($packageRequest);
	}

	/**
	 * @param   Form                      $form     Form
	 * @param   array|CMSObject|Registry  $data     Data
	 * @param   array|CMSObject           $tmpData  Tmp data
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function onRadicalMartPrepareMethodForm(Form $form, array|CMSObject|Registry $data, array|CMSObject $tmpData): void
	{
		$formName = $form->getName();

		if ($formName == 'com_radicalmart.shippingmethod')
		{
			if (!$form->loadFile(JPATH_SITE . '/plugins/' . $this->_type . '/' . $this->_name . '/forms/method.xml'))
			{
				echo '111';
				die;
			}
		}
	}

	/**
	 * Prepare RadicalMart & RadicalMart Express order form.
	 *
	 * @param   string             $context   Context selector string.
	 * @param   Form               $form      Order form object.
	 * @param   array              $formData  Form data array.
	 * @param   array|null|false   $products  Shipping method data.
	 * @param   object|null|false  $shipping  Shipping method data.
	 * @param   object|null|false  $payment   Payment method data.
	 * @param   array              $currency  Order currency data.
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function onRadicalMartGetOrderForm(
		string $context,
		Form $form,
		array $formData,
		array|null|false $products,
		object|null|false $shipping,
		object|null|false $payment,
		array $currency
	): void
	{
		$formName = $form->getName();

		if ($formName == 'com_radicalmart.order')
		{
			if (!$form->loadFile(JPATH_SITE . '/plugins/' . $this->_type . '/' . $this->_name . '/forms/order.xml'))
			{
				echo '111';
				die;
			}
		}
	}

	/**
	 * @param   string            $context   Context
	 * @param   array             $data      Data
	 * @param   array             $formData  Form data
	 * @param   object|array      $products  Products
	 * @param   stdClass          $shipping  Shipping
	 * @param   stdClass|boolean  $payment   Payment
	 * @param   array             $currency  Currency
	 * @param   boolean           $isNew     Is new
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function onBeforeOrderSave(
		string $context,
		array &$data,
		array $formData,
		object|array $products,
		stdClass $shipping,
		stdClass|bool $payment,
		array $currency,
		bool $isNew
	): void
	{
		if ($context != 'com_radicalmart.checkout')
		{
			return;
		}

		if ($shipping->plugin != 'wishboxcdek')
		{
			return;
		}

		$defaultDimensions = $shipping->params->get('wishboxcdekonepackage.default_dimensions');

		$shipping = new Registry($data['shipping']);
		$shipping->set('data.wishboxcdekonepackage.dimensions', $defaultDimensions);
		$data['shipping'] = $shipping->toString();
	}
}
