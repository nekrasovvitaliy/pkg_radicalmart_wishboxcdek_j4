<?php
/**
 * @copyright 2013-2024 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Form\Preparer\Trait;

use Exception;
use Joomla\Component\Wishboxradicalmartcdek\Administrator\Service\CalculatorDelegate;

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
		$cityCode = $this->getCityCode();
		$isTariffToPoint = $this->isTariffToPoint();

		if ($cityCode > 0
			&& $isTariffToPoint)
		{
			$result = $this->getForm()->setFieldAttribute(
				'officeCode',
				'cityCode',
				$this->getCityCode(),
				$this->shippingFieldAttributeGroup
			);

			if (!$result)
			{
				throw new Exception('failed to set attribute', 500);
			}
		}
		else
		{
			if (!$this->getForm()->removeField('officeCode', $this->shippingFieldAttributeGroup))
			{
				throw new Exception('failed to removeField', 500);
			}
		}

		$calculatorDelegate = new CalculatorDelegate(
			$this->getShipping(),
			$this->getProducts(),
			$this->getFormData()
		);

		$packageRequests = $calculatorDelegate->getPackages();

		$packageRequestWeights = 0;

		foreach ($packageRequests as $packageRequest)
		{
			$packageRequestWeights[] = $packageRequest->getWeight();
		}

		$packageRequestMaxWeight = max($packageRequestWeights);

		$result = $this->getForm()->setFieldAttribute(
			'officeCode',
			'weight',
			$packageRequestMaxWeight,
			$this->shippingFieldAttributeGroup
		);

		if (!$result)
		{
			throw new Exception('failed to set attribute', 500);
		}
	}
}
