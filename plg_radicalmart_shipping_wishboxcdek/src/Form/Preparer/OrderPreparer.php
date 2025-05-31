<?php
/**
 * @copyright   (c) 2013-2025 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later
 *
 * @noinspection PhpUndefinedClassInspection
 */
namespace Joomla\Plugin\RadicalMartShipping\WishboxCdek\Form\Preparer;

use Exception;
use Joomla\CMS\Form\Form;
use Joomla\Component\WishboxCdek\Site\Helper\WishboxCdekHelper;
use Joomla\Plugin\RadicalMartShipping\WishboxCdek\Form\FormPreparer;
use Joomla\Plugin\RadicalMartShipping\WishboxCdek\Form\Preparer\Trait\CheckoutOfficecodePreparerTrait;
use Joomla\Plugin\RadicalMartShipping\WishboxCdek\Form\Preparer\Trait\CheckoutAddressPreparerTrait;
use Joomla\Plugin\RadicalMartShipping\WishboxCdek\Form\Preparer\Trait\OrderTrackingNumberPreparerTrait;
use stdClass;

/**
 * @since 1.0.0
 */
class OrderPreparer extends FormPreparer
{
	use CheckoutOfficecodePreparerTrait;
	use CheckoutAddressPreparerTrait;
	use OrderTrackingNumberPreparerTrait;

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
	 * @var   stdClass[]  $products  Products
	 *
	 * @since 1.0.0
	 */
	protected array $products;

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
	 * @param   array     $products  Products
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function __construct(Form $form, stdClass $shipping, array $formData, array $products)
	{
		parent::__construct($form);

		$this->shipping = $shipping;
		$this->formData = $formData;
		$this->products = $products;
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
		$this->prepareTrackingNumber();
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
		return (int) $this->formData['shipping']['city_code'];
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
		return $this->formData['shipping']['price']['tariff_code'] ?? 0;
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
		$isTariffToPoint = WishboxCdekHelper::isTariffToPoint($this->getTariffCode());

		return $isTariffToPoint;
	}

	/**
	 * @return stdClass
	 *
	 * @since 1.0.0
	 */
	public function getShipping(): stdClass
	{
		return $this->shipping;
	}

	/**
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function getFormData(): array
	{
		return $this->formData;
	}

	/**
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function getProducts(): array
	{
		return $this->products;
	}
}
