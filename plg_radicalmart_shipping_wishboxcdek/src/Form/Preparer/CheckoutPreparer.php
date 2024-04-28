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
use stdClass;

/**
 * @since 1.0.0
 */
class CheckoutPreparer extends FormPreparer
{
	use CheckoutOfficecodePreparerTrait;

	/**
	 * @var stdClass  $shipping  Shipping
	 *
	 * @since 1.0.0
	 */
	protected stdClass $shipping;

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
	 * @var string|null
	 *
	 * @since 1.0.0
	 */
	protected ?string $shippingFieldAttributeGroup = 'shipping';

	/**
	 * @param   Form      $form      Form
	 * @param   stdClass  $shipping  Shipping
	 * @param   integer   $cityCode  City code
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function __construct(Form $form, stdClass $shipping, int $cityCode)
	{
		parent::__construct($form);

		$this->shipping = $shipping;
		$this->cityCode = $cityCode;
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

		if (!$this->cityCode)
		{
			$this->tariffCode = 0;
		}

		$this->prepareOfficeCodeField();
		$this->prepareAddressField();
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
		if ($this->getCityCode() <= 0
			|| $this->getTariffCode() <= 0
			|| WishboxcdekHelper::isTariffToPoint($this->getTariffCode())
		)
		{
			if (!$this->form->removeField('address', 'shipping'))
			{
				throw new Exception('failed to removeField', 500);
			}
		}
	}

	/**
	 * @return integer
	 *
	 * @since 1.0.0
	 */
	protected function getShippingId(): int
	{
		return $this->shipping->id;
	}

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
		return isset($this->shipping->order->price['tariffCode'])
			? $this->shipping->order->price['tariffCode']
			: 0;
	}

	/**
	 * @return boolean
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	protected function isTariffToPoint(): bool
	{
		/** @noinspection PhpUnnecessaryLocalVariableInspection */
		$isTariffToPoint = WishboxcdekHelper::isTariffToPoint($this->getTariffCode());

		return $isTariffToPoint;
	}
}
