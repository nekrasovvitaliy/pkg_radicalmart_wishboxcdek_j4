<?php
/**
 * @copyright 2013-2024 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Form\Preparer;

use Exception;
use Joomla\CMS\Form\Form;
use Joomla\Component\Wishboxcdek\Site\Helper\WishboxcdekHelper;
use Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Form\FormPreparer;
use Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Form\Preparer\Trait\CheckoutOfficecodePreparerTrait;
use Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Form\Preparer\Trait\CheckoutTariffcodePreparerTrait;

/**
 * @since 1.0.0
 */
class OrdersitePreparer extends FormPreparer
{
	use CheckoutOfficecodePreparerTrait;
	use CheckoutTariffcodePreparerTrait;

	/**
	 * @var   integer  $shippingId  Shipping id
	 *
	 * @since 1.0.0
	 */
	protected int $shippingId;

	/**
	 * @var   integer  $cityCode  City code
	 *
	 * @since 1.0.0
	 */
	protected int $cityCode;

	/**
	 * @var   integer  $tariffCode  Tariff code
	 *
	 * @since 1.0.0
	 */
	protected int $tariffCode;

	/**
	 * @param   Form     $form        Form
	 * @param   integer  $shippingId  Shipping id
	 * @param   integer  $cityCode    City code
	 * @param   integer  $tariffCode  Tariff code
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function __construct(Form $form, int $shippingId, int $cityCode, int $tariffCode)
	{
		parent::__construct($form);

		$this->shippingId = $shippingId;
		$this->cityCode = $cityCode;
		$this->tariffCode = $tariffCode;
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
	}

	/**
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	protected function prepareAddressField(): void
	{
		if ($this->cityCode <= 0 || $this->tariffCode <= 0 || WishboxcdekHelper::isTariffToPoint($this->tariffCode))
		{
			if (!$this->getForm()->removeAttribute('address', 'shipping'))
			{
				throw new Exception('failed to set attribute', 500);
			}
		}
	}

	/**
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	protected function prepareTariffCodeField(): void
	{
		if ($this->tariffCode > 0)
		{
			if (!$this->getForm()->setFieldAttribute('tariffCode', 'filterCode', $this->tariffCode, 'shipping'))
			{
				throw new Exception('failed to set attribute', 500);
			}

			if (!$this->getForm()->setFieldAttribute('tariffCode', 'default', $this->tariffCode, 'shipping'))
			{
				throw new Exception('failed to set attribute', 500);
			}
		}
		else
		{
			if (!$this->getForm()->removeField('tariffCode', 'shipping'))
			{
				throw new Exception('failed to set attribute', 500);
			}
		}
	}

	/**
	 * @return integer
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	/**
	 * @return integer
	 *
	 * @since 1.0.0
	 */
	protected function getCityCode(): int
	{
		return $this->cityCode;
	}

	/**
	 * @return integer
	 *
	 * @since 1.0.0
	 */
	protected function getTariffCode(): int
	{
		return $this->tariffCode;
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
}
