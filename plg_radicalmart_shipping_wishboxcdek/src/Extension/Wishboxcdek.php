<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license     GNU General Public License version 2 or later
 */
namespace Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Extension;

use Exception;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\RadicalMart\Administrator\Helper\PriceHelper as RadicalMartPriceHelper;
use Joomla\Component\Wishboxcdek\Site\Service\Calculator;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Form\Preparer\CheckoutPreparer;
use Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Form\Preparer\OrderPreparer;
use Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Form\Preparer\OrdersitePreparer;
use Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Form\Preparer\PersonalshippingmethodPreparer;
use Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Form\Preparer\ShippingmethodPreparer;
use Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Service\CalculatorDelegate;
use Joomla\Registry\Registry;
use stdClass;
use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since 1.0.0
 */
class Wishboxcdek extends CMSPlugin implements SubscriberInterface
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 *
	 * @since  1.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Loads the application object.
	 *
	 * @var  CMSApplication
	 *
	 * @since  1.0.0
	 */
	protected $app = null;

	/**
	 * Enable on RadicalMart
	 *
	 * @var  boolean
	 *
	 * @since  2.0.0
	 */
	public bool $radicalmart = true;

	/**
	 * Enable on RadicalMartExpress
	 *
	 * @var boolean
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public bool $radicalmart_express = true; // phpcs:ignore

	/**
	 * @var array $customerData Customer data
	 *
	 * @since 1.0.0
	 */
	public array $customerData;

	/**
	 * @var integer $receiverCityCode Receiver city code
	 *
	 * @since 1.0.0
	 */
	public int $receiverCityCode;

	/**
	 * @var integer $tariffCode Receiver city code
	 *
	 * @since 1.0.0
	 */
	public int $tariffCode = 0;

	/**
	 * Returns an array of events this subscriber will listen to.
	 *
	 * @return  array
	 *
	 * @since 1.0.0
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onRadicalMartNormaliseRequestData'         => 'onRadicalMartNormaliseRequestData',
			'onRadicalMartGetOrderShippingMethods'      => 'onRadicalMartGetOrderShippingMethods',
			'onRadicalMartGetOrderForm'                 => 'onRadicalMartGetOrderForm',
			'onRadicalMartGetOrderTotal'                => 'onGetOrderTotal',
			'onRadicalMartGetOrderCustomerUpdateData'   => 'onGetOrderCustomerUpdateData',
			'onRadicalMartGetCheckoutCustomerData'      => 'onGetCheckoutCustomerData',
			'onRadicalMartGetCustomerMethodForm'        => 'onRadicalMartGetPersonalMethodForm',
			'onRadicalMartGetPersonalShippingMethods'   => 'onGetPersonalShippingMethods',
			'onRadicalMartGetPersonalMethodForm'        => 'onRadicalMartGetPersonalMethodForm',
			'onContentPrepareForm'                      => 'onContentPrepareForm',
			'onRadicalMartPrepareMethodForm'            => 'onRadicalMartPrepareMethodForm',
			'onRadicalMartBeforeOrderSave'              => 'onRadicalMartBeforeOrderSave'
		];
	}

	/**
	 * Prepare RadicalMart method prices data.
	 *
	 * @param   string  $context  Context selector string.
	 * @param   object  $objData  Form data object.
	 * @param   Form    $form     The form object.
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
	public function onRadicalMartNormaliseRequestData(string $context, object $objData, Form $form): void
	{
		if ($context === 'com_radicalmart.shippingmethod')
		{
			foreach ($objData->prices as &$price)
			{
				$price['base'] = RadicalMartPriceHelper::clean($price['base'], $price['currency']);
			}
		}
	}

	/**
	 * Prepare RadicalMart order shipping method data.
	 *
	 * @param   string  $context   Context selector string.
	 * @param   object  $method    Method data.
	 * @param   array   $formData  Order form data.
	 * @param   array   $products  Order products data.
	 * @param   array   $currency  Order currency data.
	 *
	 * @return void
	 *
	 * @throws  Exception
	 *
	 * @since  1.0.0
	 *
	 * @noinspection PhpUnused
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function onRadicalMartGetOrderShippingMethods(
		string $context,
		object $method,
		array $formData,
		array $products,
		array $currency
	): void
	{
		// Set disabled
		$method->disabled = false;

		// Set price
		if (!empty($formData['shipping']['price']))
		{
			$price = $formData['shipping']['price'];
		}
		else
		{
			$price = (isset($method->prices[$currency['group']]))
				? $method->prices[$currency['group']]
				: ['base' => 0];
		}

		// Set base price
		$code = $currency['code'];

		$price['base'] = RadicalMartPriceHelper::clean($price['base'], $code);

		if ($context == 'com_radicalmart.checkout')
		{
			$this->receiverCityCode = $this->getReceiverCityCode($method->id);

			if ($this->receiverCityCode)
			{
				$calculatorDelegate = new CalculatorDelegate(
					$method,
					$products,
					$this->receiverCityCode
				);
				$calculator = new Calculator($calculatorDelegate);
				$tariff = $calculator->getTariff();

				if ($tariff)
				{
					$calculatePrice = (bool) $method->params->get('calculatePrice', 0);

					if ($calculatePrice)
					{
						$price['base'] = $tariff->shipping;
					}

					$price['tariff']  = $tariff->shipping;
					$this->tariffCode = (int) $tariff->code;
				}
			}
		}
		elseif ($context == 'com_radicalmart.order')
		{
			$this->receiverCityCode = $formData['shipping']['cityCode'];
			$this->tariffCode = $formData['shipping']['tariffCode'];
		}

		$price['base_string']   = (empty($price['base'])) ? Text::_('COM_RADICALMART_PRICE_FREE')
			: RadicalMartPriceHelper::toString($price['base'], $code);
		$price['base_seo']      = (empty($price['base'])) ? Text::_('COM_RADICALMART_PRICE_FREE')
			: RadicalMartPriceHelper::toString($price['base'], $code, 'seo');
		$price['base_number']   = RadicalMartPriceHelper::toString($price['base'], $code, false);

		// Set final price
		$price['final']        = $price['base'];
		$price['final_string'] = (empty($price['final'])) ? Text::_('COM_RADICALMART_PRICE_FREE')
			: RadicalMartPriceHelper::toString($price['final'], $code);
		$price['final_seo']    = (empty($price['final'])) ? Text::_('COM_RADICALMART_PRICE_FREE')
			: RadicalMartPriceHelper::toString($price['final'], $code, 'seo');
		$price['final_number'] = RadicalMartPriceHelper::toString($price['final'], $code, false);

		// Set order
		$method->order              = new stdClass;
		$method->order->id          = $method->id;
		$method->order->title       = $method->title;
		$method->order->code        = $method->code;
		$method->order->description = $method->description;
		$method->order->price       = $price;

		// Set layout
		if ($context === 'com_radicalmart.checkout')
		{
			$method->layout = 'plugins.radicalmart_shipping.wishboxcdek.radicalmart.checkout';
		}
	}

	/**
	 * @param   integer  $shippingId  Shipping id
	 *
	 * @return integer
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	private function getReceiverCityCode(int $shippingId): int
	{
		$app = Factory::getApplication();
		$user = $app->getIdentity();
		$data = $app->getUserState('com_radicalmart.checkout.data');

		$receiverCityCode = 0;

		if (isset($data['shipping']['cityCode']))
		{
			$receiverCityCode = (int) $data['shipping']['cityCode'];
		}
		else
		{
			if ($user->id > 0)
			{
				$customerShippingData = $this->getCustomerShippingData($shippingId);
				$receiverCityCode = (isset($customerShippingData['cityCode']))
					? (int) $customerShippingData['cityCode']
					: 0;
			}
		}

		return $receiverCityCode;
	}

	/**
	 * @param   integer  $tariffCode  Tariff code
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	private function setTariffCodeToUserState(int $tariffCode): void
	{
		$app = Factory::getApplication();
		$data = $app->getUserState('com_radicalmart.checkout.data');
		$data['shipping']['tariffCode'] = $tariffCode;
		$app->setUserState('com_radicalmart.checkout.data', $data);
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
		// Remove fields
		$fields = ['country', 'city', 'zip', 'street', 'house', 'building', 'entrance', 'floor', 'apartment', 'comment'];

		foreach ($fields as $field)
		{
			if ((int) $shipping->params->get('field_' . $field, 1) === 0)
			{
				$form->removeField($field, 'shipping');
			}
		}

		// Remove empty fields in site_order form
		if (str_contains($form->getName(), 'order_site'))
		{
			foreach ($form->getFieldset('shipping') as $field)
			{
				if (empty($formData['shipping'][$field->fieldname]))
				{
					$form->removeField($field->fieldname, 'shipping');
				}
			}
		}

		// Set default price
		if (!empty($shipping->order->price['base']))
		{
			$form->setFieldAttribute(
				'base',
				'default',
				$shipping->order->price['base'],
				'shipping.price'
			);
		}

		$formName = $form->getName();

		if ($formName == 'com_radicalmart.checkout')
		{
			$preparer = new CheckoutPreparer($form, $shipping->id, $this->receiverCityCode, $this->tariffCode);
			$preparer->prepare();
		}
		elseif ($formName == 'com_radicalmart.order_site')
		{
			$preparer = new OrdersitePreparer($form, $shipping->id, $this->receiverCityCode, $this->tariffCode);
			$preparer->prepare();
		}
		elseif ($formName == 'com_radicalmart.order')
		{
			$preparer = new OrderPreparer($form, $shipping->id, $formData);
			$preparer->prepare();
		}
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
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function onRadicalMartPrepareMethodForm(Form $form, array|CMSObject|Registry $data, array|CMSObject $tmpData): void
	{
		$formName = $form->getName();

		if ($formName == 'com_radicalmart.shippingmethod')
		{
			$preparer = new ShippingmethodPreparer($form, $data);
			$preparer->prepare();
		}
	}

	/**
	 * Prepare RadicalMart & RadicalMart Express order totals.
	 *
	 * @param   string               $context   Context selector string.
	 * @param   array                $total     Order total data.
	 * @param   array                $formData  Form data array.
	 * @param   array|null|boolean   $products  Shipping method data.
	 * @param   object|null|boolean  $shipping  Shipping method data.
	 * @param   object|null|boolean  $payment   Payment method data.
	 * @param   array                $currency  Order currency data.
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
	public function onGetOrderTotal(
		string $context,
		array &$total,
		array $formData,
		array|null|bool $products,
		object|null|bool $shipping,
		object|null|bool $payment,
		array $currency
	): void
	{
		if (!empty($shipping->order->price['base']))
		{
			$total['base'] += $shipping->order->price['base'];
		}

		if (!empty($shipping->order->price['final']))
		{
			$total['final'] += $shipping->order->price['final'];
		}

	}

	/**
	 * Get RadicalMart & RadicalMart Express order customer update data.
	 *
	 * @param   string  $context   Context selector string.
	 * @param   object  $order     Order data.
	 * @param   object  $customer  Customer data method data.
	 *
	 * @return array|false Update customer data if success, False if not.
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function onGetOrderCustomerUpdateData(string $context, object $order, object $customer): array|bool
	{
		$result = false;

		if (!empty($order->formData['shipping']))
		{
			$result = [];

			foreach ($order->formData['shipping'] as $key => $value)
			{
				if ($key === 'price' || $key === 'id')
				{
					continue;
				}

				$result[$key] = $value;
			}
		}

		return $result;
	}

	/**
	 * Get RadicalMart & RadicalMart Express checkout customer data.
	 *
	 * @param   string  $context       Context selector string.
	 * @param   object  $shipping      Shipping method object.
	 * @param   array   $customerData  Customer data method data.
	 *
	 * @return array|false Customer shipping data for merge.
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function onGetCheckoutCustomerData(string $context, object $shipping, array $customerData): array|false
	{
		$this->customerData = $customerData;

		return (!empty($customerData)) ? $customerData : false;
	}

	/**
	 * Prepare RadicalMart & RadicalMart Express customer and personal forms.
	 *
	 * @param   string        $context   Context selector string.
	 * @param   Form          $form      The object of the custom shipping method form.
	 * @param   object|array  $data      The data expected for the form.
	 * @param   object|array  $shipping  Shipping method data.
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
	public function onRadicalMartGetPersonalMethodForm(
		string $context,
		Form $form,
		object|array $data,
		object|array $shipping
	): void
	{
		$fields = ['country', 'city', 'zip', 'street', 'house', 'building', 'entrance', 'floor', 'apartment', 'comment'];

		foreach ($fields as $field)
		{
			if ((int) $shipping->params->get('field_' . $field, 1) === 0)
			{
				$form->removeField($field);
			}
		}

		$preparer = new PersonalshippingmethodPreparer($form, $data, $shipping);
		$preparer->prepare();
	}

	/**
	 * @param   Form       $form  Form
	 * @param   CMSObject  $data  Data
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function onRadicalMartPreparePricesForm(Form $form, CMSObject $data): void
	{

	}

	/**
	 * Prepare RadicalMart personal shipping method data.
	 *
	 * @param   string  $context  Context selector string.
	 * @param   object  $method   Method data.
	 *
	 * @return void
	 *
	 * @throws  Exception
	 *
	 * @since  1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function onGetPersonalShippingMethods(string $context, object $method)
	{

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
	 */
	public function onContentPrepareForm(Event $event): void
	{
		/** @var Form $form */
		$form = $event->getArgument(0);

		/** @var array|stdClass $data */
		$data = $event->getArgument(1);

		$formName = $form->getName();

		$notAllowedFormNames = [
			'com_radicalmart.checkout',
			'com_radicalmart.order_site',
			'com_radicalmart.shippingmethod',
			'com_radicalmart.shippingmethod.prices'
		];

		if (in_array($formName, $notAllowedFormNames))
		{
			return;
		}

		if ($formName == 'com_radicalmart.shippingmethod.prices')
		{

		}

		$event->setArgument(0, $form);
		$event->setArgument(1, $data);
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
		$d->dimensions = $shipping->params->get('defaultDimensions');
		$registry->set('data', $d);
		$data['shipping'] = $registry->toString();
	}

	/**
	 * @param   integer  $shippingId  Shipping id
	 *
	 * @return array
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function getCustomerShippingData(int $shippingId): array
	{
		$app = Factory::getApplication();
		$db = Factory::getContainer()->get(DatabaseInterface::class);
		$user = $app->getIdentity();

		$query = $db->getQuery(true)
			->select(['c.id', 'c.contacts', 'c.shipping', 'c.payment', 'c.plugins'])
			->from($db->quoteName('#__radicalmart_customers', 'c'))
			->where($db->quoteName('c.id') . ' = :id')
			->bind(':id', $user->id, ParameterType::INTEGER);

		if ($data = $db->setQuery($query, 0, 1)->loadAssoc())
		{
			return (new Registry($data['shipping']))->toArray()['shipping_method_' . $shippingId];
		}

		return [];
	}
}
