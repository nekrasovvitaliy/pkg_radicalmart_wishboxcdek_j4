<?php
/**
 * @copyright   (c) 2013-2024 Nekrasov Vitaliy
 * @license     GNU General Public License version 2 or later
 */
namespace Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Form\Preparer\Trait;

use Exception;

/**
 * @method getTariffCode(): integer
 * @method getForm(): Form
 * @method getCityCode(): integer
 *
 * @since 1.0.0
 */
trait CheckoutAddressPreparerTrait
{
	/**
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	protected function prepareAddressField(): void
	{
		$cityCode = $this->getCityCode();
		$isTariffToPoint = $this->isTariffToPoint();

		if ($cityCode <= 0 || $isTariffToPoint)
		{
			$result = $this->getForm()->removeField('address', $this->shippingFieldAttributeGroup);

			if (!$result)
			{
				throw new Exception('failed to removeField', 500);
			}
		}
	}
}
