<?php
/**
 * @copyright  (c) 2013-2025 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license    GNU General Public License version 2 or later
 */
namespace Joomla\Plugin\RadicalMartShipping\WishboxCdek\Form\Preparer\Trait;

use Exception;
use Joomla\CMS\Factory;

/**
 * @method getTariffCode(): integer
 * @method getForm(): Form
 * @method getFormData(): Form data
 * @method getProducts(): array
 * @method getShipping(): stdClass
 *
 * @since 1.0.0
 */
trait CheckoutOfficecodePreparerTrait
{
	/**
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	protected function prepareOfficeCodeField(): void
	{
		$app = Factory::getApplication();
		$cityCode = $this->getCityCode();

		if ($cityCode > 0 && $this->isTariffModeToPoint() === true)
		{
			$result = $this->getForm()->setFieldAttribute(
				'office_code',
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

				if (!$this->getForm()->setFieldAttribute(
					'office_code',
					'packages',
					$packagesData,
					$this->shippingFieldAttributeGroup
				))
				{
					throw new Exception('failed to set attribute', 500);
				}
			}
		}
		else
		{
			if (!$this->getForm()->removeField('office_code', $this->shippingFieldAttributeGroup))
			{
				throw new Exception('failed to removeField', 500);
			}
		}
	}
}
