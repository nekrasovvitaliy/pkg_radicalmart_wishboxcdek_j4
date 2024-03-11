<?php
/**
 * @copyright 2013-2024 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Form\Preparer\Trait;

use Exception;
use Joomla\Component\Wishboxcdek\Site\Helper\WishboxcdekHelper;

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
		if ($this->getCityCode() > 0
			&& $this->getTariffCode() > 0
			&& WishboxcdekHelper::isTariffToPoint($this->getTariffCode()))
		{
			if (!$this->getForm()->setFieldAttribute('officeCode', 'cityCode', $this->getCityCode(), 'shipping'))
			{
				throw new Exception('failed to set attribute', 500);
			}
		}
		else
		{
			if (!$this->getForm()->removeField('officeCode', 'shipping'))
			{
				throw new Exception('failed to removeField', 500);
			}
		}
	}
}
