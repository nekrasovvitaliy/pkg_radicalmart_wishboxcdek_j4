<?php
/**
 * @copyright  (c) 2013-2024 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license    GNU General Public License version 2 or later
 */
namespace Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Form\Preparer\Trait;

use Exception;
use Joomla\Component\Wishboxcdek\Site\Helper\WishboxcdekHelper;
use Joomla\Component\Wishboxradicalmartcdek\Administrator\Service\CalculatorDelegate;
use Joomla\Component\Wishboxradicalmartcdek\Administrator\Service\CalculatorService;

/**
 * @method getTariffCode(): integer
 * @method getForm(): Form
 * @method getFormData(): Form data
 * @method getProducts(): array
 * @method getShipping(): stdClass
 *
 * @since 1.0.0
 */
trait CheckoutOfficeYandexMapPreparerTrait
{
	/**
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	protected function prepareOfficeYandexMapField(): void
	{
		$cityCode = $this->getCityCode();

		if ($cityCode > 0)
		{
			$tariffCode = $this->getTariffCode();

			if ($tariffCode && WishboxcdekHelper::isTariffToPoint($tariffCode))
			{
				$result = $this->getForm()->setFieldAttribute(
					'office_yandex_map',
					'cityCode',
					$this->getCityCode(),
					$this->shippingFieldAttributeGroup
				);

				if (!$result)
				{
					throw new Exception('failed to set attribute', 500);
				}

				if (method_exists($this, 'getProducts'))
				{
					$calculatorDelegate = new CalculatorDelegate(
						$this->getShipping(),
						$this->getFormData(),
						$this->getProducts()
					);

					$packageRequests = $calculatorDelegate->getPackages();

					$packagesData = [];

					foreach ($packageRequests as $packageRequest)
					{
						$packagesData[] = [
							'weight' => $packageRequest->getWeight() * 0.001,
							'width'  => $packageRequest->getWidth(),
							'height' => $packageRequest->getHeight(),
							'length' => $packageRequest->getLength()
						];
					}

					$packagesData = json_encode($packagesData);
					$result       = $this->getForm()->setFieldAttribute(
						'office_yandex_map',
						'packages_data',
						$packagesData,
						$this->shippingFieldAttributeGroup
					);

					if (!$result)
					{
						throw new Exception('failed to set attribute', 500);
					}

					try
					{
						$shippingTariff = CalculatorService::getMinShippingTariff(
							$this->getShipping(),
							$this->getFormData(),
							$this->getProducts()
						);

						if (!$this->getForm()->setFieldAttribute(
							'office_yandex_map',
							'shipping_tariff',
							json_encode($shippingTariff->toArray()),
							$this->shippingFieldAttributeGroup
						))
						{
							throw new Exception('failed to set attribute', 500);
						}
					}
					catch (Exception $e)
					{
					}
				}
			}
		}
		else
		{
			if (!$this->getForm()->removeField('office_yandex_map', $this->shippingFieldAttributeGroup))
			{
				throw new Exception('failed to removeField', 500);
			}
		}
	}
}
