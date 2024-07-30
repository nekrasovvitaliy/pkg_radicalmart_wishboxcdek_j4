<?php
/**
 * @copyright   (c) 2013-2024 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Plugin\Radicalmart\Wishboxcdekonepackage\Extension;

use Exception;
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Factory\MVCFactoryAwareTrait;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Wishboxcdek\Site\Interface\RegistratorDelegateInterface;
use Joomla\Component\Wishboxcdek\Site\Trait\ApiClientTrait;
use Joomla\Component\Wishboxradicalmartcdek\Administrator\Service\CalculatorDelegate;
use Joomla\Component\Wishboxradicalmartcdek\Administrator\Service\RegistratorDelegate;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;
use stdClass;
use WishboxCdekSDK2\Model\Request\Orders\OrdersPatch\PackageRequest as OrdersPatchPackageRequest;
use WishboxCdekSDK2\Model\Request\Orders\OrdersPatch\Package\ItemRequest as OrdersPatchPackageItemRequest;
use WishboxCdekSDK2\Model\Request\Orders\OrdersPost\MoneyRequest;
use WishboxCdekSDK2\Model\Request\Orders\OrdersPost\Package\ItemRequest as OrdersPostPackageItemRequest;
use WishboxCdekSDK2\Model\Request\Orders\OrdersPost\PackageRequest as OrdersPostPackageRequest;
use WishboxCdekSDK2\Model\Request\Calculator\TarifflistPost\PackageRequest as TarifflistPostPackageRequest;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since 1.0.0
 */
class Wishboxcdekonepackage extends CMSPlugin implements SubscriberInterface
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
			'onWishboxRadicalMartCdekRegistratorDelegateGetOrdersPostPackages'
			=> 'onWishboxRadicalMartCdekRegistratorDelegateGetOrdersPostPackages',
			'onWishboxRadicalMartCdekRegistratorDelegateGetOrdersPatchPackages'
			=> 'onWishboxRadicalMartCdekRegistratorDelegateGetOrdersPatchPackages',
			'onWishboxRadicalMartCdekCalculatorDelegateGetPackages'     => 'onWishboxRadicalMartCdekCalculatorDelegateGetPackages',
			'onRadicalMartPrepareMethodForm'                            => 'onRadicalMartPrepareMethodForm',
			'onRadicalMartGetOrderForm' => 'onRadicalMartGetOrderForm',
			'onRadicalMartBeforeOrderSave' => 'onRadicalMartBeforeOrderSave'
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

		/** @var RegistratorDelegateInterface $delegate */
		$delegate = $event->getArgument(1);

		$useDefaultPackageWeight = $delegate->order->shipping->params->get('wishboxcdekonepackageUseDefaultWeight');

		if ($useDefaultPackageWeight)
		{
			$weight = $delegate->order->shipping->params->get('wishboxcdekonepackageDefaultWeight');
		}
		else
		{
			$weight = $delegate->getTotalWeight();
		}

		if (isset($delegate->order->formData['shipping']['wishboxcdekonepackageDimensions'])
			&& is_array($delegate->order->formData['shipping']['wishboxcdekonepackageDimensions'])
			&& (!empty($delegate->order->formData['shipping']['wishboxcdekonepackageDimensions']['width'])
			&& !empty($delegate->order->formData['shipping']['wishboxcdekonepackageDimensions']['height'])
			&& !empty($delegate->order->formData['shipping']['wishboxcdekonepackageDimensions']['length'])))
		{
			$dimensions = $delegate->order->formData['shipping']['wishboxcdekonepackageDimensions'];
		}
		else
		{
			$dimensions = (array) $delegate->order->shipping->params->get('wishboxcdekonepackageDefaultDimensions');
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
			$items[] = (new OrdersPostPackageItemRequest)
				->setName($product->name)
				->setWareKey($product->code)
				// Оплата за товар при получении, без НДС (за единицу товара)
				->setPayment((new MoneyRequest)->setValue(0))
				// Объявленная стоимость товара (за единицу товара)
				->setCost(1)
				->setWeight(1)
				->setAmount($product->quantity);
		}

		$package->setItems($items);
		$packageRequests[] = $package;

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
		$existingOrdersGetResponse = $apiClient->getOrderInfoByImNumber($delegate->getOrderNumber());

		$useDefaultPackageWeight = $delegate->order->shipping->params->get('wishboxcdekonepackageUseDefaultWeight');

		if ($useDefaultPackageWeight)
		{
			$weight = $delegate->order->shipping->params->get('wishboxcdekonepackageDefaultWeight');
		}
		else
		{
			$weight = $delegate->getTotalWeight();
		}

		if (isset($delegate->order->formData['shipping']['wishboxcdekonepackageDimensions'])
			&& is_array($delegate->order->formData['shipping']['wishboxcdekonepackageDimensions'])
			&& (!empty($delegate->order->formData['shipping']['wishboxcdekonepackageDimensions']['width'])
			&& !empty($delegate->order->formData['shipping']['wishboxcdekonepackageDimensions']['height'])
			&& !empty($delegate->order->formData['shipping']['wishboxcdekonepackageDimensions']['length'])))
		{
			$dimensions = $delegate->order->formData['shipping']['wishboxcdekonepackageDimensions'];
		}
		else
		{
			$dimensions = (array) $delegate->order->shipping->params->get('wishboxcdekonepackageDefaultDimensions');
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
			$items[] = (new OrdersPatchPackageItemRequest)
				->setName($product->name)
				->setWareKey($product->code)
				->setPayment((new \WishboxCdekSDK2\Model\Request\Orders\OrdersPatch\MoneyRequest)->setValue($product->payment))
				->setCost(1)
				->setWeight(1)
				->setAmount($product->quantity);
		}

		$package->setItems($items);
		$packageRequests[] = $package;

		$event->setArgument(0, $packageRequests);
		$event->setArgument(1, $delegate);
		$event->setArgument(2, $existingOrdersGetResponse);
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
		/** @var TarifflistPostPackageRequest[] $packageRequests */
		$packageRequests = $event->getArgument(0);

		/** @var CalculatorDelegate $delegate */
		$delegate = $event->getArgument(1);

		$useDefaultPackageWeight = $delegate->method->params->get('wishboxcdekonepackageUseDefaultWeight');

		if ($useDefaultPackageWeight)
		{
			$weight = $delegate->getMethod()->params->get('wishboxcdekonepackageDefaultWeight');
		}
		else
		{
			$weight = $delegate->getTotalWeight();
		}

		$formData = $delegate->getFormData();

		if (isset($formData['shipping']['wishboxcdekonepackageDimensions']))
		{
			$dimensions = $formData['shipping']['wishboxcdekonepackageDimensions'];
		}
		else
		{
			$dimensions = (array) $delegate->method->params->get('wishboxcdekonepackageDefaultDimensions');
		}

		if (empty($dimensions['height']) || empty($dimensions['height']) || empty($dimensions['height']))
		{
			$dimensions = (array) $delegate->method->params->get('wishboxcdekonepackageDefaultDimensions');
		}

		$packageRequest = (new TarifflistPostPackageRequest)
			->setWeight($weight)
			->setHeight($dimensions['height'])
			->setWidth($dimensions['width'])
			->setLength($dimensions['length']);
		$packageRequests[] = $packageRequest;

		$event->setArgument(0, $packageRequests);
		$event->setArgument(1, $delegate);
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
	public function onRadicalMartBeforeOrderSave(
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

		$registry = new Registry($data['shipping']);
		$d = $registry->get('data');
		$d->wishboxcdekonepackageDimensions = $shipping->params->get('wishboxcdekonepackageDefaultDimensions');
		$registry->set('data', $d);
		$data['shipping'] = $registry->toString();
	}
}
