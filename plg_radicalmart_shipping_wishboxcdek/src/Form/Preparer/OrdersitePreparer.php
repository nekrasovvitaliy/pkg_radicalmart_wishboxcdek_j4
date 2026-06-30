<?php
/**
 * @copyright  (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license    GNU General Public License version 2 or later
 */
namespace Joomla\Plugin\RadicalMartShipping\WishboxCdek\Form\Preparer;

use Exception;
use Joomla\CMS\Form\Form;
use Joomla\Plugin\RadicalMartShipping\WishboxCdek\Form\FormPreparer;
use Joomla\Plugin\RadicalMartShipping\WishboxCdek\Form\Preparer\Trait\CheckoutAddressPreparerTrait;
use Joomla\Plugin\RadicalMartShipping\WishboxCdek\Form\Preparer\Trait\CheckoutOfficecodePreparerTrait;
use Joomla\Plugin\RadicalMartShipping\WishboxCdek\Form\Preparer\Trait\CheckoutTariffcodePreparerTrait;
use stdClass;

/**
 * @since 1.0.0
 */
class OrdersitePreparer extends FormPreparer
{
	use CheckoutOfficecodePreparerTrait;
	use CheckoutAddressPreparerTrait;
	use CheckoutTariffcodePreparerTrait;

	/**
	 * @var   stdClass  $shipping  Shipping
	 *
	 * @since 1.0.0
	 */
	protected stdClass $shipping {
		get {
			return $this->shipping;
		}
	}

	/**
	 * @var   array  $formData  Form data
	 *
	 * @since 1.0.0
	 */
	protected array $formData {
		get {
			return $this->formData;
		}
	}

	/**
	 * @var   stdClass[]  $products  Products
	 *
	 * @since 1.0.0
	 */
	protected array $products {
		get {
			return $this->products;
		}
	}

	/**
	 * @var string|null
	 *
	 * @since 1.0.0
	 */
	protected ?string $shippingFieldAttributeGroup = 'shipping';

	/**
	 * @param   Form        $form      Form
	 * @param   stdClass    $shipping  Shipping
	 * @param   array       $formData  Form data
	 * @param   stdClass[]  $products  Products
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
		$this->prepareTariffCodeField();
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
	protected function getTariffCode(): int
	{
		return $this->shipping->order->price['tariff_code'] ?? 0;
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

}
