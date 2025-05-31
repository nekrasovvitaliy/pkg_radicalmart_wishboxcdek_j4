<?php
/**
 * @copyright  (c) 2013-2025 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license    GNU General Public License version 2 or later
 */
namespace Joomla\Plugin\RadicalMartShipping\WishboxCdek\Form\Preparer\Trait;

use Exception;
use Joomla\Component\WishboxRadicalMartCdek\Administrator\Model\CalculatorModel;

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
		$this->getForm()->removeField('office_yandex_map', $this->shippingFieldAttributeGroup);

		return;

		$cityCode = $this->getCityCode();

		if ($cityCode > 0)
		{
			if ($cityCode > 0 && $this->isTariffModeToPoint())
			{
				$result = $this->getForm()->setFieldAttribute(
					'office_yandex_map',
					'city_code',
					$this->getCityCode(),
					$this->shippingFieldAttributeGroup
				);

				if (!$result)
				{
					throw new Exception('failed to set attribute', 500);
				}

				if (method_exists($this, 'getProducts'))
				{
					$calculatorDelegateModel = $app->bootComponent('com_wishboxradicalmartcdek')
						->getMVCFactory()
						->createModel('CalculatorDelegate', 'Administrator');

					$calculatorDelegateModel->setMethod($this->getShipping())
						->setFormData($this->getFormData())
						->setProducts($this->getProducts());

					$packageRequests = $calculatorDelegateModel->getPackages();

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
						/** @var CalculatorModel $calculatorModel */
						$calculatorModel = $app->bootComponent('com_wishboxcdek')
							->getMVCFactory()
							->createModel('Calculator', 'Site');

						$calculatorModel->getShippingTariffs(
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
			else
			{
				if (!$this->getForm()->removeField('office_yandex_map', $this->shippingFieldAttributeGroup))
				{
					throw new Exception('failed to removeField', 500);
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
