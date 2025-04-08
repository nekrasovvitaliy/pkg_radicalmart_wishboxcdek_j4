<?php
/**
 * @copyright  (c) 2013-2025 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license    GNU General Public License version 2 or later
 */
namespace Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Form;

use Exception;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Extension\Component;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\Component\RadicalMart\Administrator\Table\ShippingMethodTable;
use Joomla\Component\Wishboxcdek\Site\Helper\WishboxcdekHelper;

/**
 * @since 1.0.0
 */
abstract class FormPreparer
{
	/**
	 * @var Form $form Form
	 *
	 * @since 1.0.0
	 */
	protected Form $form;

	/**
	 * @param   Form  $form  Form
	 *
	 * @since 1.0.0
	 */
	public function __construct(Form $form)
	{
		$this->form = $form;
	}

	/**
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function prepare(): void
	{
		$componentParams = ComponentHelper::getParams('com_wishboxradicsalmartcdek');

		/** @noinspection PhpUndefinedFieldInspection */
		if (!$this->getForm()->setFieldAttribute(
			'officeCode',
			'deliverypoint_type',
			$componentParams->get('deliverypoint_type', 'ALL'),
			$this->shippingFieldAttributeGroup
		))
		{
			throw new Exception('Failed to set field attribute');
		}

		/** @noinspection PhpUndefinedFieldInspection */
		if (!$this->getForm()->setFieldAttribute(
			'officeCode',
			'deliverypoint_allowed_cod',
			$componentParams->get('offices_filter_deliverypoint_allowed_cod', '0'),
			$this->shippingFieldAttributeGroup
		))
		{
			throw new Exception('Failed to set field attribute');
		}
	}

	/**
	 * @return boolean
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	protected function checkPlugin(): bool
	{
		$app = Factory::getApplication();

		/** @var ShippingMethodTable $table */
		$table = $app->bootComponent('com_radicalmart')
			->getMVCFactory()
			->createTable('ShippingMethod', 'Administrator');

		$shippingId = $this->getShippingId();

		$table->load($shippingId);

		if ($table->plugin != 'wishboxcdek')
		{
			return false;
		}

		return true;
	}

	/**
	 * @return Form
	 *
	 * @since 1.0.0
	 */
	protected function getForm(): Form
	{
		return $this->form;
	}

	/**
	 * @return integer
	 *
	 * @since 1.0.0
	 */
	abstract protected function getShippingId(): int;

	/**
	 *
	 * @return boolean|null
	 *
	 * @since 1.0.0
	 */
	public function isTariffModeToPoint(): ?bool
	{
		if (method_exists($this, 'getTariffCode'))
		{
			$tariffCode = $this->getTariffCode();

			if ($tariffCode)
			{
				return WishboxcdekHelper::isTariffToPoint($tariffCode);
			}
			else
			{
				return null;
			}
		}

		if (method_exists($this, 'getShipping'))
		{
			$shipping = $this->getShipping();
			$tariffMode = $shipping->params->get('tariffMode');
			list($from, $to) = explode('-', $tariffMode);
			$result = $to == 'С';

			return $result;
		}

		return false;
	}
}
