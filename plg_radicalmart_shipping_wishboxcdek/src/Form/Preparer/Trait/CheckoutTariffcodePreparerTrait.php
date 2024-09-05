<?php
/**
 * @copyright (c) 2013-2024 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license GNU General Public License version 2 or later
 */
namespace Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Form\Preparer\Trait;

use Exception;
use Joomla\CMS\Factory;
use Joomla\Component\Wishboxradicalmartcdek\Administrator\Service\CalculatorService;
use Wishbox\ShippingService\ShippingTariff;

/**
 * @method getTariffCode(): integer
 * @method getForm(): Form
 * @method getFormData(): Form data
 * @method getProducts(): array
 * @method getShipping(): stdClass
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
		$app = Factory::getApplication();
		$cityCode = $this->getCityCode();

		if ($cityCode > 0)
		{
			try
			{
				if ($this->getForm()->getName() == 'com_radicalmart.checkout')
				{
					$shippingTariffs = CalculatorService::getShippingTariffs(
						$this->getShipping(),
						$this->getFormData(),
						$this->getProducts()
					);
				}
				else
				{
					$tariffCode = $this->getShipping()->order->price['tariffCode'];
					$tariffTable = $app->bootComponent('com_wishboxcdek')
						->getMVCFactory()
						->createTable('Tariff', 'Administrator');
					$tariffTable->load(['code' => $tariffCode]);

					$shippingTariffs = [
						(new ShippingTariff((float) $this->getShipping()->order->price['tariff'], 0))
							->setPeriodMin($this->getShipping()->order->price['period_min'])
							->setPeriodMax($this->getShipping()->order->price['period_max'])
							->setCode($tariffCode)
							->setName($tariffTable->name)
					];
				}

				if (count($shippingTariffs))
				{
					$shippingTariffPricesByCodes = self::getTariffPricesByCodes($shippingTariffs);

					if (!$this->getForm()->setFieldAttribute(
						'tariffCode',
						'prices_by_codes',
						json_encode($shippingTariffPricesByCodes),
						$this->shippingFieldAttributeGroup
					))
					{
						throw new Exception('Failed to set attribute', 500);
					}

					$shippingTariffPeriodsByCodes = self::getTariffPeriodsByCodes($shippingTariffs);

					if (!$this->getForm()->setFieldAttribute(
						'tariffCode',
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
			if (!$this->getForm()->removeField('tariffCode', $this->shippingFieldAttributeGroup))
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
			$period = $shippingTariff->getPeriodMin() . '-' . $shippingTariff->getPeriodMax() . ' дней';
			$shippingTariffPeriodsByCodes[$shippingTariff->getCode()] = $period;
		}

		return $shippingTariffPeriodsByCodes;
	}
}
