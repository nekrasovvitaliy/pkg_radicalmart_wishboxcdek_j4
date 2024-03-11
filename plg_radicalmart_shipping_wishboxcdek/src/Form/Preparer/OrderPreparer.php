<?php
/**
 * @copyright 2013-2024 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 *
 * @noinspection PhpUndefinedClassInspection
 */
namespace Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Form\Preparer;

use Exception;
use Joomla\CMS\Form\Form;
use Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Form\FormPreparer;
use Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Form\Preparer\Trait\CheckoutOfficecodePreparerTrait;
use Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Form\Preparer\Trait\CheckoutAddressPreparerTrait;

/**
 * @since 1.0.0
 */
class OrderPreparer extends FormPreparer
{
	use CheckoutOfficecodePreparerTrait;
	use CheckoutAddressPreparerTrait;

	/**
	 * @var   integer  $shippingId  Shipping id
	 *
	 * @since 1.0.0
	 */
	protected int $shippingId;

	/**
	 * @var   array  $formData  Form data
	 *
	 * @since 1.0.0
	 */
	protected array $formData;

	/**
	 * @param   Form     $form        Form
	 * @param   integer  $shippingId  Shipping id
	 * @param   array    $formData    Form data
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function __construct(Form $form, int $shippingId, array $formData)
	{
		parent::__construct($form);

		$this->shippingId = $shippingId;
		$this->formData = $formData;

	}
	/**
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function prepare(): void
	{
		if (!$this->checkPlugin())
		{
			return;
		}

		$this->prepareOfficeCodeField();
		$this->prepareAddressField();
	}

	/**
	 * @return integer
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	protected function getCityCode(): int
	{
		return (int) $this->formData['shipping']['cityCode'];
	}

	/**
	 * @return integer
	 *
	 * @since 1.0.0
	 */
	protected function getShippingId(): int
	{
		return $this->shippingId;
	}

	/**
	 * @return integer
	 *
	 * @since 1.0.0
	 */
	protected function getTariffCode(): int
	{
		return (int) $this->formData['shipping']['tariffCode'];
	}
}
