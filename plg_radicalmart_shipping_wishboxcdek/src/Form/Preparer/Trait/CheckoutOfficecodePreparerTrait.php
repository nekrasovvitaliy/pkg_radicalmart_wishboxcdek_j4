<?php
/**
 * @copyright 2013-2024 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Form\Preparer\Trait;

use Exception;

/**
 * @method getTariffCode(): integer
 * @method getForm(): Form
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
			&& $isTariffToPoint
		)
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
	}
}
