<?php
/**
 * @copyright   (c) 2013-2024 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later
 */
namespace Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Form\Preparer\Trait;

use Exception;

/**
 * @method isTariffModeToPoint(): boolean
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

		if ($cityCode <= 0 || $this->isTariffModeToPoint() === true)
		{
			$result = $this->getForm()->removeField('address', $this->shippingFieldAttributeGroup);

			if (!$result)
			{
				throw new Exception('failed to removeField', 500);
			}
		}
	}
}
