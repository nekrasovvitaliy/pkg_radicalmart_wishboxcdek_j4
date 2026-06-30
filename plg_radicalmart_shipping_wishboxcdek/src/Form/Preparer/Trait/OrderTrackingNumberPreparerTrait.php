<?php
/**
 * @copyright (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license GNU General Public License version 2 or later
 */
namespace Joomla\Plugin\RadicalMartShipping\WishboxCdek\Form\Preparer\Trait;

use Exception;
use Joomla\CMS\Component\ComponentHelper;

/**
 * @method getTariffCode(): integer
 * @method getForm(): \Joomla\CMS\Form\Form
 * @method getFormData(): Form data
 * @method getProducts(): array
 * @method getShipping(): stdClass
 *
 * @since 1.0.0
 */
trait OrderTrackingNumberPreparerTrait
{
	/**
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	protected function prepareTrackingNumber(): void
	{
		$componentParams = ComponentHelper::getParams('com_wishboxradicalmartcdek');

		$trackingNumberReadonlyAttribute = (bool) $componentParams->get('tracking_number_readonly_attribute', '1');

		$form = $this->getForm();

		if (!$form->setFieldAttribute('tracking_number', 'readonly', $trackingNumberReadonlyAttribute, $this->shippingFieldAttributeGroup))
		{
			throw new Exception('failed to setFieldAttribute', 500);
		}
	}
}
