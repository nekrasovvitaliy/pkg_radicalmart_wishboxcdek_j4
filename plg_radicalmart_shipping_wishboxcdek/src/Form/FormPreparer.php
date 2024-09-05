<?php
/**
 * @copyright  (c) 2013-2024 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license    GNU General Public License version 2 or later
 */
namespace Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Form;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\Component\RadicalMart\Administrator\Table\ShippingMethodTable;

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
}
