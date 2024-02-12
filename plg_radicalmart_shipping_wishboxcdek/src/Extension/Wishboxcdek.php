<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license     GNU General Public License version 2 or later
 */
namespace Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Extension;

use Exception;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\RadicalMart\Administrator\Helper\PriceHelper as RadicalMartPriceHelper;
use Joomla\Component\RadicalMart\Administrator\Table\ShippingMethodTable;
use Joomla\Component\Wishboxcdek\Site\Service\Calculator;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Service\CalculatorDelegate;
use Joomla\Registry\Registry;
use stdClass;
use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since       1.0.0
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
	 * Returns an array of events this subscriber will listen to.
	 *
	 * @return  array
	 *
	 * @since 1.0.0
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onRadicalMartNormaliseRequestData'       => 'onRadicalMartNormaliseRequestData',
			'onRadicalMartGetOrderShippingMethods'    => 'onRadicalMartGetOrderShippingMethods',
			'onRadicalMartGetOrderForm'               => 'onGetOrderForm',
			'onRadicalMartGetOrderTotal'              => 'onGetOrderTotal',
			'onRadicalMartGetOrderCustomerUpdateData' => 'onGetOrderCustomerUpdateData',
			'onRadicalMartGetCheckoutCustomerData'    => 'onGetCheckoutCustomerData',
			'onRadicalMartGetCustomerMethodForm'      => 'onGetCustomerMethodForm',
			'onRadicalMartGetPersonalShippingMethods' => 'onGetPersonalShippingMethods',
			'onRadicalMartGetPersonalMethodForm'      => 'onGetCustomerMethodForm',
			'onContentPrepareForm'                    => 'onContentPrepareForm',
			'onRadicalMartPrepareMethodForm'          => 'onRadicalMartPrepareMethodForm'
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
		/** @var SiteApplication $app */
		$app = Factory::getApplication();

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
			$data = $app->getUserState('com_radicalmart.checkout.data');

			if ($data)
			{
				$calculatorDelegate = new CalculatorDelegate(
					$method,
					$data,
					$products
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

					$price['tariff'] = $tariff->shipping;

					$data['shipping']['tariffCode'] = $tariff->code;

					$app->setUserState('com_radicalmart.checkout.data', $data);
				}
			}
		}

		if ($context == 'com_radicalmart.order')
		{
			$calculatorDelegate = new CalculatorDelegate(
				$method,
				$formData,
				$products
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

				$price['tariff'] = $tariff->shipping;

				$formData['shipping']['tariffCode'] = $tariff->code;
			}
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
	public function onGetOrderForm(
		string $context,
		Form $form,
		array $formData,
		array|null|false $products,
		object|null|false $shipping,
		object|null|false $payment,
		array $currency
	): void
	{
		$app = Factory::getApplication();

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

		$data = $app->getUserState('com_radicalmart.checkout.data');

		if (isset($data['shipping']['officeCode']))
		{
			$formData['shipping']['officeCode'] = $data['shipping']['officeCode'];
		}

		if (isset($data['shipping']['tariffCode']))
		{
			$formData['shipping']['tariffCode'] = $data['shipping']['tariffCode'];
		}

		if (isset($data['shipping']['cityCode']))
		{
			$form->setFieldAttribute(
				'officeCode',
				'cityCode',
				$data['shipping']['cityCode'],
				'shipping'
			);
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
	public function onGetCustomerMethodForm(string $context, Form $form, object|array $data, object|array $shipping): void
	{
		$fields = ['country', 'city', 'zip', 'street', 'house', 'building', 'entrance', 'floor', 'apartment', 'comment'];

		foreach ($fields as $field)
		{
			if ((int) $shipping->params->get('field_' . $field, 1) === 0)
			{
				$form->removeField($field);
			}
		}

		if (isset($data->shipping['shipping_method_' . $shipping->id])
			&& isset($data->shipping['shipping_method_' . $shipping->id]['cityCode']))
		{
			$cityCode = (int) $data->shipping['shipping_method_' . $shipping->id]['cityCode'];
			$form->setFieldAttribute('officeCode', 'cityCode', $cityCode);
		}
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
	 * @noinspection PhpUndefinedFieldInspection
	 */
	public function onContentPrepareForm(Event $event): void
	{
		/** @var Form $form */
		$form = $event->getArgument(0);

		/** @var array $data */
		$data = $event->getArgument(1);

		$formName = $form->getName();

		if ($formName == 'com_radicalmart.shippingmethod')
		{
			if (is_object($data))
			{
				$senderCityCode = $data->params['senderCityCode'];

				if (!$form->setFieldAttribute(
					'senderOfficeCode',
					'cityCode',
					$senderCityCode,
					'params'
				))
				{
					throw new Exception('', 500);
				}
			}
		}

		$app = Factory::getApplication();

		$availableFormNames = [
			'com_radicalmart.checkout',
			'com_radicalmart.order'
		];

		if (!in_array($form->getName(), $availableFormNames))
		{
			return;
		}

		if (!isset($data['shipping']))
		{
			return;
		}

		/** @var ShippingMethodTable $table */
		$table = $app->bootComponent('com_radicalmart')
			->getMVCFactory()
			->createTable('ShippingMethod', 'Administrator');

		$table->load((int) $data['shipping']['id']);

		if ($table->plugin != 'wishboxcdek')
		{
			return;
		}

		$app = Factory::getApplication();

		if ($form->getName() == 'com_radicalmart.checkout')
		{
			if (isset($data['shipping']['id']))
			{
				$this->onContentPrepareCheckoutForm($form, $data);
			}
		}

		if ($form->getName() == 'com_radicalmart.order')
		{
			$cityCode = $data['shipping']['cityCode'];
			$form->setFieldAttribute('officeCode', 'cityCode', $cityCode, 'shipping');
		}

		if ($form->getName() == 'com_radicalmart.shippingmethod')
		{
			$senderCityCode = $data->params['senderCityCode'];

			if (!$form->setFieldAttribute('senderOfficeCode', 'cityCode', $senderCityCode, 'params'))
			{
				throw new Exception('', 500);
			}
		}

		$event->setArgument(0, $form);
		$event->setArgument(1, $data);
	}

	/**
	 * @param   Form             $form     Form
	 * @param   CMSObject|array  $data     Data
	 * @param   CMSObject|array  $tmpData  Tmp data
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function onRadicalMartPrepareMethodForm(Form $form, CMSObject|array $data, CMSObject|array $tmpData): void
	{

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
	 * @param   Form   $form  Form
	 * @param   array  $data  Data
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	private function onContentPrepareCheckoutForm(Form $form, array $data): void
	{
		$app = Factory::getApplication();

		$userStateData = $app->getUserState('com_radicalmart.checkout.data');

		$cityCode = (int) $data['shipping']['cityCode'];

		if (!$cityCode)
		{
			$cityCode = isset($userStateData['shipping']['cityCode'])
				? (int) $userStateData['shipping']['cityCode']
				: 0;
		}

		if ($cityCode > 0)
		{
			if (!$form->setFieldAttribute('officeCode', 'cityCode', $cityCode, 'shipping'))
			{
				throw new Exception('failed to set attribute', 500);
			}
		}
		else
		{
			if (!$form->setFieldAttribute('officeCode', 'type', 'hidden', 'shipping'))
			{
				throw new Exception('failed to set attribute', 500);
			}
		}

		$tariffCode = (int) $data['shipping']['tariffCode'];

		if (!$tariffCode)
		{
			$tariffCode = isset($userStateData['shipping']['tariffCode'])
				? (int) $userStateData['shipping']['tariffCode']
				: 0;
		}

		if ($tariffCode > 0)
		{
			if (!$form->setFieldAttribute('tariffCode', 'filterCode', $tariffCode, 'shipping'))
			{
				throw new Exception('failed to set attribute', 500);
			}

			if (!$form->setFieldAttribute('tariffCode', 'default', $tariffCode, 'shipping'))
			{
				throw new Exception('failed to set attribute', 500);
			}
		}
		else
		{
			if (!$form->setFieldAttribute('tariffCode', 'type', 'hidden', 'shipping'))
			{
				throw new Exception('failed to set attribute', 500);
			}
		}
	}
}
