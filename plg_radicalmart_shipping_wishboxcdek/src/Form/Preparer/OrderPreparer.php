<?php
/**
 * @copyright   (c) 2013-2024 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later
 *
 * @noinspection PhpUndefinedClassInspection
 */
namespace Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Form\Preparer;

use Exception;
use Joomla\CMS\Form\Form;
use Joomla\Component\Wishboxcdek\Site\Helper\WishboxcdekHelper;
use Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Form\FormPreparer;
use Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Form\Preparer\Trait\CheckoutOfficecodePreparerTrait;
use Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Form\Preparer\Trait\CheckoutAddressPreparerTrait;
use stdClass;

/**
 * @since 1.0.0
 */
class OrderPreparer extends FormPreparer
{
	use CheckoutOfficecodePreparerTrait;
	use CheckoutAddressPreparerTrait;

	/**
	 * @var   stdClass  $shipping  Shipping
	 *
	 * @since 1.0.0
	 */
	protected stdClass $shipping;

	/**
	 * @var   array  $formData  Form data
	 *
	 * @since 1.0.0
	 */
	protected array $formData;

	/**
	 * @var string|null
	 *
	 * @since 1.0.0
	 */
	protected ?string $shippingFieldAttributeGroup = 'shipping';

	/**
	 * @param   Form      $form      Form
	 * @param   stdClass  $shipping  Shipping
	 * @param   array     $formData  Form data
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function __construct(Form $form, stdClass $shipping, array $formData)
	{
		parent::__construct($form);

		$this->shipping = $shipping;
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
		return $this->shipping->id;
	}

	/**
	 * @return integer
	 *
	 * @since 1.0.0
	 */
	protected function getTariffCode(): int
	{
		$tariffCode = $this->formData['shipping']['price']['tariffCode'] ?? 0;

		return $tariffCode;
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
