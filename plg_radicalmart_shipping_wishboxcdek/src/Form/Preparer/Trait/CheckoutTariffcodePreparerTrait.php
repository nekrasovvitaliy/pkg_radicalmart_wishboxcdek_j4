<?php
/**
 * @copyright (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license       GNU General Public License version 2 or later
 */

namespace Joomla\Plugin\RadicalMartShipping\WishboxCdek\Form\Preparer\Trait;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\Component\WishboxCdek\Administrator\Extension\WishboxCdekComponent;
use Joomla\Component\WishboxCdek\Administrator\Table\TariffTable;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\RadicalMartShipping\WishboxCdek\Service\Calculator\Adapter\CalculatorAdapterService;
use stdClass;
use Wishbox\ShippingService\ShippingTariff;
use WishboxCdekLibrary\Service\Calculator\CalculatorService;

/**
 * @method int getTariffCode()
 * @method Form getForm()
 * @method array getFormData()
 * @method array getProducts()
 * @method stdClass getShipping()
 * @method CalculatorService getCalculatorService()
 * @method DispatcherInterface getDispatcher()
 *
 * @since 1.0.0
 */
trait CheckoutTariffcodePreparerTrait
{
	/**
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	protected function prepareTariffCodeField(): void
	{
		$app        = Factory::getApplication();
		$cityCode   = $this->getCityCode();
		$tariffCode = $this->getTariffCode();

		/** @var WishboxCdekComponent $component */
		$component = $app->bootComponent('com_wishboxcdek');

		if ($cityCode > 0 && $tariffCode)
		{
			try
			{
				if ($this->getForm()->getName() == 'com_radicalmart.checkout')
				{
					$calculatorAdapterService = new CalculatorAdapterService(
						$this->shipping,
						$this->formData,
						$this->products,
						$this->getDispatcher()
					);

					$shippingTariffs = $this->getCalculatorService()->getShippingTariffs($calculatorAdapterService);
				}
				else
				{
					/** @var integer $tariffCode */
					$tariffCode = $this->shipping->order->price['tariff_code'];

					/** @var TariffTable $tariffTable */
					$tariffTable = $component->getMVCFactory()
						->createTable('Tariff', 'Administrator');

					$tariffTable->load(['code' => $tariffCode]);

					/** @var ShippingTariff[] $shippingTariffs */
					$shippingTariffs = [
						new ShippingTariff((float) $this->shipping->order->price['tariff'], 0)
							->setPeriodMin($this->shipping->order->price['period_min'])
							->setPeriodMax($this->shipping->order->price['period_max'])
							->setCode($tariffCode)
							->setName($tariffTable->name)
					];
				}

				if (count($shippingTariffs))
				{
					$shippingTariffPricesByCodes = self::getTariffPricesByCodes($shippingTariffs);

					if (!$this->getForm()->setFieldAttribute(
						'tariff_code',
						'prices_by_codes',
						json_encode($shippingTariffPricesByCodes),
						$this->shippingFieldAttributeGroup
					))
					{
						throw new Exception('Failed to set attribute', 500);
					}

					$shippingTariffPeriodsByCodes = self::getTariffPeriodsByCodes($shippingTariffs);

					if (!$this->getForm()->setFieldAttribute(
						'tariff_code',
						'periods_by_codes',
						json_encode($shippingTariffPeriodsByCodes),
						$this->shippingFieldAttributeGroup
					))
					{
						throw new Exception('Failed to set attribute', 500);
					}
				}
			}
			catch (Exception $e)
			{
			}
		}
		else
		{
			if (!$this->getForm()->removeField('tariff_code', $this->shippingFieldAttributeGroup))
			{
				throw new Exception('failed to removeField', 500);
			}
		}
	}

	/**
	 * @param   ShippingTariff[]  $shippingTariffs  Shipping tariffs
	 *
	 * @return string[]
	 *
	 * @since 1.0.0
	 */
	private static function getTariffPricesByCodes(array $shippingTariffs): array
	{
		$shippingTariffPricesByCodes = [];

		foreach ($shippingTariffs as $shippingTariff)
		{
			$shippingTariffPricesByCodes[$shippingTariff->getCode()] = $shippingTariff->getShipping();
		}

		return $shippingTariffPricesByCodes;
	}

	/**
	 * @param   ShippingTariff[]  $shippingTariffs  Shipping tariffs
	 *
	 * @return string[]
	 *
	 * @since 1.0.0
	 */
	private static function getTariffPeriodsByCodes(array $shippingTariffs): array
	{
		$shippingTariffPeriodsByCodes = [];

		foreach ($shippingTariffs as $shippingTariff)
		{
			$period                                                   = $shippingTariff->getPeriodMin() . '-' . $shippingTariff->getPeriodMax() . ' дней';
			$shippingTariffPeriodsByCodes[$shippingTariff->getCode()] = $period;
		}

		return $shippingTariffPeriodsByCodes;
	}
}
