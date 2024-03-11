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
		if ($this->getCityCode() <= 0
			|| $this->getTariffCode() <= 0
			|| WishboxcdekHelper::isTariffToPoint($this->getTariffCode()))
		{
			if (!$this->getForm()->removeField('address', 'shipping'))
			{
				throw new Exception('failed to removeField', 500);
			}
		}
	}
}
