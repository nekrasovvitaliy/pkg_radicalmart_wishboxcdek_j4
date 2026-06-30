<?php
/**
 * @copyright   (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license         GNU General Public License version 2 or later
 */

namespace Joomla\Plugin\RadicalMartShipping\WishboxCdek\Extension;

use Exception;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\RadicalMart\Administrator\Helper\PriceHelper as RadicalMartPriceHelper;
use Joomla\Component\WishboxCdek\Administrator\Extension\WishboxCdekComponent;
use Joomla\Component\WishboxCdek\Administrator\Table\CityTable;
use Joomla\Component\WishboxCdek\Administrator\Table\OfficeTable;
use Joomla\Component\WishboxCdek\Administrator\Table\TariffTable;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\Event\SubscriberInterface;
use Joomla\Plugin\RadicalMartShipping\WishboxCdek\Form\Preparer\CheckoutPreparer;
use Joomla\Plugin\RadicalMartShipping\WishboxCdek\Form\Preparer\OrderPreparer;
use Joomla\Plugin\RadicalMartShipping\WishboxCdek\Form\Preparer\OrdersitePreparer;
use Joomla\Plugin\RadicalMartShipping\WishboxCdek\Form\Preparer\PersonalshippingmethodPreparer;
use Joomla\Plugin\RadicalMartShipping\WishboxCdek\Form\Preparer\ShippingmethodPreparer;
use Joomla\Plugin\RadicalMartShipping\WishboxCdek\Service\Calculator\RadicalMartShippingCalculatorServiceAwareInterface;
use Joomla\Plugin\RadicalMartShipping\WishboxCdek\Service\Calculator\RadicalMartShippingCalculatorServiceAwareTrait;
use Joomla\Registry\Registry;
use stdClass;
use WishboxCdekLibrary\Service\Calculator\CalculatorServiceAwareInterface;
use WishboxCdekLibrary\Service\Calculator\CalculatorServiceAwareTrait;
use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since 1.0.0
 */
final class WishboxCdek extends CMSPlugin implements SubscriberInterface, CalculatorServiceAwareInterface,
	RadicalMartShippingCalculatorServiceAwareInterface
{
	use DatabaseAwareTrait;
	use CalculatorServiceAwareTrait;
	use RadicalMartShippingCalculatorServiceAwareTrait;

	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 *
	 * @since  1.0.0
	 */
	protected $autoloadLanguage = true;

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
	 * @since        1.0.0
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
			'onRadicalMartNormaliseRequestData'       => 'onNormaliseRequestData',
			'onRadicalMartGetOrderShippingMethods'    => 'onGetOrderShippingMethods',
			'onRadicalMartGetOrderForm'               => 'onGetOrderForm',
			'onRadicalMartGetOrderTotal'              => 'onGetOrderTotal',
			'onRadicalMartGetOrderCustomerUpdateData' => 'onGetOrderCustomerUpdateData',
			'onRadicalMartGetCheckoutCustomerData'    => 'onGetCheckoutCustomerData',
			'onRadicalMartGetCustomerMethodForm'      => 'onGetPersonalMethodForm',
			'onRadicalMartGetPersonalMethodForm'      => 'onGetPersonalMethodForm',
			'onRadicalMartPrepareMethodForm'          => 'onRadicalMartPrepareMethodForm',
			'onRadicalMartBeforeOrderSave'            => 'onBeforeOrderSave',
			'onRadicalMartGetOrderShipping'           => 'onRadicalMartGetOrderShipping',
			'onRadicalMartLoadOrderMethodFormData'    => 'onRadicalMartLoadOrderMethodFormData',
			'onRadicalMartPrepareOrderMethodSaveData' => 'onPrepareOrderMethodSaveData'
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
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function onNormaliseRequestData(string $context, object $objData, Form $form): void
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
	 * Prepare RadicalMart shipping  data.
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
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 * @noinspection PhpUnusedLocalVariableInspection
	 */
	public function onRadicalMartGetOrderShipping(
		string $context,
		object $method,
		array  $formData,
		array  $products,
		array  $currency
	): void
	{
		$data = (!empty($formData['shipping'])) ? $formData['shipping'] : [];

		foreach (new Registry($method->params->get('fields_default', []))->toArray() as $item)
		{
			if (!empty($item['value']) && empty($data[$item['field']]))
			{
				$data[$item['field']] = $item['value'];
			}
		}

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

		// Set a base price
		$code = $currency['code'];

		$price['base'] = RadicalMartPriceHelper::clean($price['base'], $code);

		if ($context == 'com_radicalmart.checkout'
			|| ($context == 'com_radicalmart.order' && isset($data['recalculate_price']) && $data['recalculate_price']))
		{
			if (!empty($formData['shipping']['city_code']))
			{
				try
				{
					$shippingTariff = $this->getRadicalMartShippingCalculatorService()
						->getShippingTariff($method, $formData, $products);

					if ($shippingTariff)
					{
						$priceBase = 0;

						if ($method->params->get('include_shipping_price_in_order', 0))
						{
							$priceBase = $shippingTariff->shipping;
						}

						$shippingMarkupParams = $method->params->get('shipping_markup');

						if ($priceBase && $shippingMarkupParams->use)
						{
							$priceBase = $priceBase * (float) $shippingMarkupParams->ratio;
							$priceBase = $priceBase + (float) $shippingMarkupParams->value;
						}

						$price['base']        = $priceBase;
						$price['tariff']      = $shippingTariff->shipping;
						$price['tariff_code'] = (int) $shippingTariff->code;
						$price['period_min']  = (int) $shippingTariff->periodMin;
						$price['period_max']  = (int) $shippingTariff->periodMax;
					}
				}
				catch (Exception $e)
				{
					$method->disabled = true;
				}
			}
		}

		$price['base_string'] = (empty($price['base'])) ? Text::_('COM_RADICALMART_PRICE_FREE')
			: RadicalMartPriceHelper::toString($price['base'], $code);
		$price['base_seo']    = (empty($price['base'])) ? Text::_('COM_RADICALMART_PRICE_FREE')
			: RadicalMartPriceHelper::toString($price['base'], $code, 'seo');
		$price['base_number'] = RadicalMartPriceHelper::toString($price['base'], $code, false);

		// Set the final price
		$price['final'] = $price['base'];

		$price['final_string'] = empty($price['final'])
			? Text::_('PLG_RADICALMART_SHIPPING_WISHBOXCDEK_PRICE_ACCORDING_TO_SHIPPING_SERVICE_TARIFFS')
			: RadicalMartPriceHelper::toString($price['final'], $code);

		$price['final_seo']    = (empty($price['final']))
			? Text::_('PLG_RADICALMART_SHIPPING_WISHBOXCDEK_PRICE_ACCORDING_TO_SHIPPING_SERVICE_TARIFFS')
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

		$method->notification = $this->prepareMethodNotification($data);
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
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function onGetOrderShippingMethods(
		string $context,
		object $method,
		array  $formData,
		array  $products,
		array  $currency
	): void
	{
		// Set disabled
		$method->disabled = false;
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
	public function onGetOrderForm(
		string            $context,
		Form              $form,
		array             $formData,
		array|null|false  $products,
		object|null|false $shipping,
		object|null|false $payment,
		array             $currency
	): void
	{
		// Remove fields
		$fields = [
			'country', 'city', 'zip', 'street', 'house', 'building', 'entrance', 'floor', 'apartment', 'comment'
		];

		foreach ($fields as $field)
		{
			if ((int) $shipping->params->get('field_' . $field, 1) === 0)
			{
				$form->removeField($field, 'shipping');
			}
		}

		// Remove empty fields in the site_order form
		if (str_contains($form->getName(), 'order_site'))
		{
			$fields = [
				'country',
				'city',
				'zip',
				'street',
				'house',
				'building',
				'entrance',
				'floor',
				'apartment',
				'comment',
				'date',
				'note',
				'tracking_number'
			];

			foreach ($fields as $field)
			{
				if (empty($formData['shipping'][$field]))
				{
					$form->removeField($field, 'shipping');
				}
			}
		}

		// Set the default price
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
			$preparer = new CheckoutPreparer(
				$form,
				$shipping,
				$formData,
				$products
			);
			$preparer->setCalculatorService($this->getCalculatorService());
			$preparer->setDispatcher($this->getApplication()->getDispatcher());
			$preparer->prepare();
		}
		elseif ($formName == 'com_radicalmart.order_site')
		{
			$preparer = new OrdersitePreparer($form, $shipping, $formData, $products);
			$preparer->prepare();
		}
		elseif ($formName == 'com_radicalmart.order')
		{
			$preparer = new OrderPreparer($form, $shipping, $formData, $products);
			$preparer->prepare();
		}
	}

	/**
	 * @param   Form          $form     Form
	 * @param   array|object  $data     Data
	 * @param   array|object  $tmpData  Tmp data
	 *
	 * @return void
	 *
	 * @throws Exception
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
			$preparer = new ShippingmethodPreparer($form, $data);
			$preparer->prepare();
		}
	}

	/**
	 * Prepare loaded RadicalMart form data.
	 *
	 * @param   string   $context   Context selector string.
	 * @param   array    $data      Method saved  data.
	 * @param   object   $method    Order a shipping method object.
	 * @param   array    $formData  Order form data.
	 * @param   array    $products  Order products data.
	 * @param   array    $currency  Order currency data.
	 * @param   boolean  $isNew     Is a new order.
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 * @noinspection PhpUnusedParameterInspection
	 * @noinspection PhpUnusedLocalVariableInspection
	 */
	public function onRadicalMartLoadOrderMethodFormData(
		string $context,
		array  &$data,
		object $method,
		array  $formData,
		array  $products,
		array  $currency,
		bool   $isNew
	): void
	{
		// Set all order data to form data
		foreach (new Registry($method->order)->toArray() as $key => $value)
		{
			$data[$key] = $value;
		}

		if ($context == 'com_radicalmart.checkout'
			|| ($context == 'com_radicalmart.order' && isset($data['recalculate_price']) && $data['recalculate_price']))
		{
			if (!empty($formData['shipping']['city_code']))
			{
				try
				{
					$shippingTariff = $this->getRadicalMartShippingCalculatorService()
						->getShippingTariff($method, $formData, $products);

					if ($shippingTariff)
					{
						$tariffCode          = $shippingTariff->getCode();
						$data['tariff_code'] = $tariffCode;
					}
				}
				catch (Exception $e)
				{
				}
			}
		}

		// Cleanup actions
		$data['recalculate_price'] = 0;
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
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function onGetOrderTotal(
		string           $context,
		array            &$total,
		array            $formData,
		array|null|bool  $products,
		object|null|bool $shipping,
		object|null|bool $payment,
		array            $currency
	): void
	{
		if ($shipping->params->get('include_shipping_price_in_order', 0))
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
	 * @since        1.0.0
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
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function onGetCheckoutCustomerData(string $context, object $shipping, array $customerData): array|false
	{
		if (empty($customerData))
		{
			return false;
		}

		return $customerData;
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
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function onGetPersonalMethodForm(
		string       $context,
		Form         $form,
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

	}

	/**
	 * Prepare and clean RadicalMart & RadicalMart Express order save data.
	 *
	 * @param   string  $context   Context selector string.
	 * @param   array   $data      Method saved  data.
	 * @param   object  $method    An order shipping method object.
	 * @param   array   $formData  Order form data.
	 * @param   array   $products  Order products data.
	 * @param   array   $currency  Order currency data.
	 * @param   bool    $isNew     Is a new order.
	 *
	 * @return void
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function onPrepareOrderMethodSaveData(
		string $context,
		array  &$data,
		object $method,
		array  $formData,
		array  $products,
		array  $currency,
		bool   $isNew
	): void
	{
		unset($data['recalculate_price']);
		unset($data['data']['recalculate_price']);
	}

	/**
	 * Method to recursive merge customer data.
	 *
	 * @param   array  $source  Source customer data.
	 * @param   array  $new     New customer data.
	 *
	 * @return array Merging customer data.
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	protected static function mergeCustomerData(array $source = [], array $new = []): array
	{
		$result = $source;

		foreach ($new as $key => $value)
		{
			if (empty($value))
			{
				continue;
			}

			if (is_array($value))
			{
				$value = self::mergeCustomerData((!empty($source[$key])) ? $source[$key] : [], $value);
			}

			if (empty($source[$key]))
			{
				$result[$key] = $value;
			}
		}

		return $result;
	}

	/**
	 * @param   integer  $shippingId  Shipping id
	 *
	 * @return array
	 *
	 * @throws Exception
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	protected function getCustomerShippingData(int $shippingId): array
	{
		$app  = $this->getApplication();
		$db   = $this->getDatabase();
		$user = $app->getIdentity();

		$query = $db->createQuery()
			->select(['c.id', 'c.contacts', 'c.shipping', 'c.payment', 'c.plugins'])
			->from($db->qn('#__radicalmart_customers', 'c'))
			->where($db->qn('c.id') . ' = :id')
			->bind(':id', $user->id, ParameterType::INTEGER);

		if ($data = $db->setQuery($query, 0, 1)->loadAssoc())
		{
			$shipping = new Registry($data['shipping'])->toArray();

			if (isset($shipping['shipping_method_' . $shippingId]))
			{
				return $shipping['shipping_method_' . $shippingId];
			}
		}

		return [];
	}

	/**
	 * Method to prepare shipping notification information.
	 *
	 * @param   array  $data  Shipping form data.
	 *
	 * @return array
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	protected function prepareMethodNotification(array $data): array
	{
		$app = $this->getApplication();

		$result = [];

		if (empty($data))
		{
			return $result;
		}

		$addressString = $this->addressToString($data);

		/** @var WishboxCdekComponent $component */
		$component = $app->bootComponent('com_wishboxcdek');

		if (!empty($data['city_code']))
		{
			/** @var CityTable $cityTable */
			$cityTable = $component->getMVCFactory()
				->createTable('City', 'Administrator');

			$cityTable->load(['code' => $data['city_code']]);
			$result['PLG_RADICALMART_SHIPPING_WISHBOXCDEK_SHIPPING_CITY'] = $cityTable->cityname;
		}

		if (!empty($data['office_code']))
		{
			/** @var OfficeTable $officeTable */
			$officeTable = $component->getMVCFactory()
				->createTable('Office', 'Administrator');

			$officeTable->load(['code' => $data['office_code']]);
			$result['PLG_RADICALMART_SHIPPING_WISHBOXCDEK_SHIPPING_OFFICE_ADDRESS'] = $officeTable->address;
		}

		if (!empty($data['tariff_code']))
		{
			/** @var TariffTable $tariffTable */
			$tariffTable = $component->getMVCFactory()
				->createTable('Tariff', 'Administrator');

			$tariffTable->load(['code' => $data['tariff_code']]);
			$result['PLG_RADICALMART_SHIPPING_WISHBOXCDEK_SHIPPING_TARIFF'] = $tariffTable->name;
		}

		if (!empty($addressString))
		{
			$result['PLG_RADICALMART_SHIPPING_WISHBOXCDEK_SHIPPING_ADDRESS'] = $addressString;
		}

		if (!empty($data['comment']))
		{
			$result['PLG_RADICALMART_SHIPPING_WISHBOXCDEK_SHIPPING_COMMENT'] = $data['comment'];
		}

		if (!empty($data['date']))
		{
			$result['PLG_RADICALMART_SHIPPING_WISHBOXCDEK_SHIPPING_DATE'] = new Date($data['date'])->format(
				Text::_('DATE_FORMAT_LC4')
			);
		}

		if (!empty($data['note']))
		{
			$result['PLG_RADICALMART_SHIPPING_WISHBOXCDEK_SHIPPING_NOTE'] = $data['note'];
		}

		return $result;
	}

	/**
	 * Method to convert address data to string.
	 *
	 * @param   array  $data  Address data
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	protected function addressToString(array $data = []): string
	{
		$address = [];

		if (!empty($data['zip']))
		{
			$address[] = $data['zip'];
		}

		if (!empty($data['country']))
		{
			$address[] = $data['country'];
		}

		if (!empty($data['region']))
		{
			$address[] = $data['region'];
		}

		if (!empty($data['city']))
		{
			$address[] = $data['city'];
		}

		$mb = function_exists('mb_strtolower');

		foreach (['street', 'house', 'building', 'entrance', 'floor', 'apartment'] as $key)
		{
			if (!empty($data[$key]))
			{
				$title     = Text::_('PLG_RADICALMART_SHIPPING_WISHBOXCDEK_FIELD_' . $key);
				$title     = ($mb) ? mb_strtolower($title) : strtolower($title);
				$address[] = $title . ' ' . $data[$key];
			}
		}

		return (!empty($address)) ? implode(', ', $address) : '';
	}
}
