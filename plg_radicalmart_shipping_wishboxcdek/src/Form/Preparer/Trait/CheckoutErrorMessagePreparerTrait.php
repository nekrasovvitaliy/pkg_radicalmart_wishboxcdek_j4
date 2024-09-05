<?php
/**
 * @copyright (c) 2013-2024 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license GNU General Public License version 2 or later
 */
namespace Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Form\Preparer\Trait;

use Exception;
use Joomla\Component\Wishboxradicalmartcdek\Administrator\Service\CalculatorService;

/**
 * @method getTariffCode(): integer
 * @method getFormData(): Form data
 * @method getProducts(): array
 * @method getShipping(): stdClass
 * @method getCityCode()
 * @method isTariffToPoint()
 * @method getForm() \Joomla\CMS\Form
 *
 * @since 1.0.0
 */
trait CheckoutErrorMessagePreparerTrait
{
	/**
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	protected function prepareErrorMessageField(): void
	{
		$cityCode = $this->getCityCode();

		if ($cityCode > 0)
		{
			$isTariffToPoint = $this->isTariffToPoint();

			if ($isTariffToPoint)
			{
				try
				{
					CalculatorService::getShippingTariffs(
						$this->getShipping(),
						$this->getFormData(),
						$this->getProducts()
					);
				}
				catch (Exception $e)
				{
					/** @noinspection PhpUndefinedFieldInspection */
					if (!$this->getForm()->setFieldAttribute(
						'error_message',
						'description',
						$e->getMessage(),
						$this->shippingFieldAttributeGroup
					))
					{
						throw new Exception('Failed to set field attribute');
					}

					/** @noinspection PhpUndefinedFieldInspection */
					if (!$this->getForm()->setFieldAttribute(
						'error_message',
						'type',
						'note',
						$this->shippingFieldAttributeGroup
					))
					{
						throw new Exception('Failed to set field attribute');
					}
				}
			}
		}
		else
		{
			if (!$this->getForm()->removeField('error_message', $this->shippingFieldAttributeGroup))
			{
				throw new Exception('failed to removeField', 500);
			}
		}
	}
}
