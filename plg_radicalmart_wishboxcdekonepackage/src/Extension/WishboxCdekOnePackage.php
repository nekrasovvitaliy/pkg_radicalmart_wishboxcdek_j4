<?php
/**
 * @copyright   (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\RadicalMart\WishboxCdekOnePackage\Extension;

use Exception;
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Factory\MVCFactoryAwareTrait;
use Joomla\CMS\Plugin\CMSPlugin;
use WishboxCdekLibrary\Event\Service\CalculatorAdapter\GetPackagesEvent;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Event\SubscriberInterface;
use Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistration\Event\Service\OrderRegistrationAdapter\GetOrdersPatchPackagesEvent;
use Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistration\Event\Service\OrderRegistrationAdapter\GetOrdersPostPackagesEvent;
use Joomla\Registry\Registry;
use stdClass;
use WishboxCdek\Request\Order\GetOrderByNumberRequest;
use WishboxCdek\Request\Order\ItemRequestDto;
use WishboxCdek\Request\Order\MoneyDto;
use WishboxCdek\Request\Order\PackageRequestDto;
use WishboxCdekLibrary\Service\CdekClientAwareInterface;
use WishboxCdekLibrary\Service\CdekClientAwareTrait;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since 1.0.0
 */
final class WishboxCdekOnePackage extends CMSPlugin implements SubscriberInterface,
	CdekClientAwareInterface
{
	use MVCFactoryAwareTrait;
	use DatabaseAwareTrait;
	use CdekClientAwareTrait;

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
			'onWishboxRadicalMartCdekOrderRegistrationAdapterGetOrdersPostPackages'  => 'onWishboxRadicalMartCdekOrderRegistrationAdapterGetOrdersPostPackages',
			'onWishboxRadicalMartCdekOrderRegistrationAdapterGetOrdersPatchPackages' => 'onWishboxRadicalMartCdekOrderRegistrationAdapterGetOrdersPatchPackages',
			'onWishboxRadicalMartCdekCalculatorAdapterGetPackages'              => 'onWishboxRadicalMartCdekCalculatorAdapterGetPackages',
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
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function onWishboxRadicalMartCdekOrderRegistrationAdapterGetOrdersPostPackages(GetOrdersPostPackagesEvent $event): void
	{
		$adapter = $event->getOrderRegistrationAdapter();

		$useDefaultPackageWeight = $adapter->order->shipping->params->get('wishboxcdekonepackage.use_default_weight');

		if ($useDefaultPackageWeight)
		{
			$weight = $adapter->order->shipping->params->get('wishboxcdekonepackage.default_weight');
		}
		else
		{
			$weight = $adapter->getTotalWeight();
		}

		if (isset($adapter->order->formData['shipping']['wishboxcdekonepackage']['dimensions'])
			&& is_array($adapter->order->formData['shipping']['wishboxcdekonepackage']['dimensions'])
			&& (!empty($adapter->order->formData['shipping']['wishboxcdekonepackage']['dimensions']['width'])
				&& !empty($adapter->order->formData['shipping']['wishboxcdekonepackage']['dimensions']['height'])
				&& !empty($adapter->order->formData['shipping']['wishboxcdekonepackage']['dimensions']['length'])))
		{
			$dimensions = $adapter->order->formData['shipping']['wishboxcdekonepackage']['dimensions'];
		}
		else
		{
			$dimensions = (array) $adapter->order->shipping->params->get('wishboxcdekonepackage.default_dimensions');
		}

		$products = $adapter->getProducts();

		$items = [];

		foreach ($products as $product)
		{
			$productCost = $product->cost;

			if ($adapter->order->shipping->params->get('wishboxcdekonepackage.use_default_product_cost', 0))
			{
				$productCost = (float) $adapter->order->shipping->params->get('wishboxcdekonepackage.default_product_cost', 0);
			}

			$payment = new MoneyDto(
				value: 0,
				vatSum: 0
			);

			if ($adapter->order->shipping->params->get('wishboxcdekonepackage.product_payment.use_product_payment', 0))
			{
				$vatRate = (int) $adapter->order->shipping->params->get('product_payment.product_payment_vat_rate');
				$vatSum  = $product->price * (1 - (100 / (100 + $vatRate)));
				$payment = new MoneyDto(
					value: $vatSum,
					vatSum: $vatRate
				);
			}

			$items[] = new ItemRequestDto(
				name: $product->name,
				wareKey: $product->code,
				payment: $payment,
				cost: $productCost,
				weight: $product->weight,
				amount: $product->quantity
			);
		}

		$package = new PackageRequestDto(
			number: '1',
			weight: $weight,
			length: $dimensions['length'],
			width: $dimensions['width'],
			height: $dimensions['height'],
			items: $items,
		);

		$event->addResult($package);
	}

	/**
	 * @param   GetOrdersPatchPackagesEvent  $event  Event
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function onWishboxRadicalMartCdekOrderRegistrationAdapterGetOrdersPatchPackages(GetOrdersPatchPackagesEvent $event): void
	{
		$adapter = $event->getOrderRegistrationAdapter();

		$apiClient            = $this->getCdekClient();
		$existingOrderDetails = $apiClient->orders()->getByNumber(
			new GetOrderByNumberRequest(imNumber: $adapter->getOrderNumber())
		);

		if ($adapter->order->shipping->params->get('wishboxcdekonepackage.use_default_weight'))
		{
			$weight = $adapter->order->shipping->params->get('wishboxcdekonepackage.default_weight');
		}
		else
		{
			$weight = $adapter->getTotalWeight();
		}

		if (isset($adapter->order->formData['shipping']['wishboxcdekonepackage']['dimensions'])
			&& is_array($adapter->order->formData['shipping']['wishboxcdekonepackage']['dimensions'])
			&& (!empty($adapter->order->formData['shipping']['wishboxcdekonepackage']['dimensions']['width'])
				&& !empty($adapter->order->formData['shipping']['wishboxcdekonepackage']['dimensions']['height'])
				&& !empty($adapter->order->formData['shipping']['wishboxcdekonepackage']['dimensions']['length'])))
		{
			$dimensions = $adapter->order->formData['shipping']['wishboxcdekonepackage']['dimensions'];
		}
		else
		{
			$dimensions = (array) $adapter->order->shipping->params->get('wishboxcdekonepackage.default_dimensions');
		}

		$existingPackages  = $existingOrderDetails->entity->packages;
		$existingPackage   = $existingPackages[0];
		$existingPackageId = $existingPackage->packageId;

		$products = $adapter->getProducts();

		$items = [];

		foreach ($products as $product)
		{
			$paymentRequest = new MoneyDto(
				value: 0,
				vatSum: 0,
			);

			if ($adapter->order->shipping->params->get('product_payment.use_product_payment', 0))
			{
				$vatRate        = (int) $adapter->order->shipping->params->get('product_payment.product_payment_vat_rate');
				$vatSum         = $product->price * (1 - (100 / (100 + $vatRate)));
				$paymentRequest = new MoneyDto(
					value: $vatSum,
					vatSum: $vatRate,
				);
			}

			$items[] = new ItemRequestDto(
				name: $product->name,
				wareKey: $product->code,
				payment: $paymentRequest,
				cost: $product->cost,
				weight: $product->weight,
				amount: $product->quantity,
			);
		}

		$package = new PackageRequestDto(
			number: '1',
			weight: $weight,
			length: $dimensions['length'],
			width: $dimensions['width'],
			height: $dimensions['height'],
			items: $items,
			packageId: $existingPackageId
		);

		$event->addResult($package);
	}

	/**
	 * @param   GetPackagesEvent  $event  Event
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function onWishboxRadicalMartCdekCalculatorAdapterGetPackages(GetPackagesEvent $event): void
	{
		$adapter = $event->getCalculatorAdapter();

		$method = $adapter->method;

		if ($method->params->get('wishboxcdekonepackage.use_default_weight'))
		{
			$weight = (int) $method->params->get('wishboxcdekonepackage.default_weight');
		}
		else
		{
			$weight = $adapter->getTotalWeight();
		}

		$formData = $adapter->formData;

		$dimensions = $formData['shipping']['wishboxcdekonepackage']['dimensions']
			?? (array) $method->params->get('wishboxcdekonepackage.default_dimensions');

		if (empty($dimensions['width']) || empty($dimensions['height']) || empty($dimensions['length']))
		{
			$dimensions = (array) $method->params->get('wishboxcdekonepackage.default_dimensions');
		}

		$packageRequest = new PackageRequestDto(
			number: '1',
			weight: $weight,
			length: $dimensions['length'],
			width: $dimensions['width'],
			height: $dimensions['height'],
		);

		$event->addResult($packageRequest);
	}

	/**
	 * @param   Form          $form     Form
	 * @param   array|object  $data     Data
	 * @param   array|object  $tmpData  Tmp data
	 *
	 * @return void
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function onRadicalMartPrepareMethodForm(Form $form, array|object $data, array|object $tmpData): void
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
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function onRadicalMartGetOrderForm(
		string            $context,
		Form              $form,
		array             $formData,
		array|null|false  $products,
		object|null|false $shipping,
		object|null|false $payment,
		array             $currency
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
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function onBeforeOrderSave(
		string        $context,
		array         &$data,
		array         $formData,
		object|array  $products,
		stdClass      $shipping,
		stdClass|bool $payment,
		array         $currency,
		bool          $isNew
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
