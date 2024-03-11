<?php
/**
 * @copyright 2013-2024 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Form;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Object\CMSObject;
use Joomla\Component\RadicalMart\Administrator\Table\ShippingMethodTable;

/**
 * @since 1.0.0
 */
class FormPreparer
{
	/**
	 * @var Form $form Form
	 *
	 * @since 1.0.0
	 */
	protected Form $form;

	/**
	 * @var array|CMSObject $data Data
	 *
	 * @since 1.0.0
	 */
	protected mixed $data;

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
	 * @return integer
	 *
	 * @since 1.0.0
	 */
	protected function getShippingId(): int
	{
		return (int) $this->data->id;
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
}
