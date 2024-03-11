<?php
/**
 * @copyright 2013-2024 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Form\Preparer\Trait;

use Exception;

/**
 * @method getCityCode(): integer
 * @method getTariffCode(): integer
 * @method getForm(): Form
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
		if ($this->getTariffCode() > 0)
		{
			if (!$this->getForm()->setFieldAttribute('tariffCode', 'filterCode', $this->getTariffCode(), 'shipping'))
			{
				throw new Exception('failed to set attribute', 500);
			}

			if (!$this->getForm()->setFieldAttribute('tariffCode', 'default', $this->getTariffCode(), 'shipping'))
			{
				throw new Exception('failed to set attribute', 500);
			}
		}
		else
		{
			if (!$this->getForm()->removeField('tariffCode', 'shipping'))
			{
				throw new Exception('failed to removeField', 500);
			}
		}
	}
}
