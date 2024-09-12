<?php
/**
 * @copyright   (c) 2013-2024 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later
 */
namespace Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Form\Preparer;

use Exception;
use Joomla\CMS\Form\Form;
use Joomla\Component\Wishboxcdek\Site\Helper\WishboxcdekHelper;
use Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Form\FormPreparer;
use Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Form\Preparer\Trait\CheckoutErrorMessagePreparerTrait;
use Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Form\Preparer\Trait\CheckoutOfficecodePreparerTrait;
use Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Form\Preparer\Trait\CheckoutOfficeGoogleMapPreparerTrait;
use Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Form\Preparer\Trait\CheckoutOfficeYandexMapPreparerTrait;
use Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Form\Preparer\Trait\CheckoutTariffcodePreparerTrait;
use stdClass;

/**
 * @since 1.0.0
 */
class CheckoutPreparer extends FormPreparer
{
	use CheckoutOfficecodePreparerTrait;
	use CheckoutOfficeGoogleMapPreparerTrait;
	use CheckoutOfficeYandexMapPreparerTrait;
	use CheckoutTariffcodePreparerTrait;
	use CheckoutErrorMessagePreparerTrait;

	/**
	 * @var stdClass  $shipping  Shipping
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
	 * @var   array  $producs Products
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
		$this->prepareOfficeYandexMapField();
		$this->prepareAddressField();
		$this->prepareTariffCodeField();
		$this->prepareErrorMessageField();
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
	protected function getFormData(): array
	{
		return $this->formData;
	}

	/**
	 * @return array
	 *
	 * @since 1.0.0
	 */
	protected function getProducts(): array
	{
		return $this->products;
	}

	/**
	 * @return integer
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnnecessaryLocalVariableInspection
	 */
	protected function getCityCode(): int
	{
		$formData = $this->getFormData();
		$cityCode = (isset($formData['shipping']) && isset($formData['shipping']['cityCode']))
			? (int) $formData['shipping']['cityCode']
			: 0;

		return $cityCode;
	}

	/**
	 * @return integer
	 *
	 * @since 1.0.0
	 */
	protected function getTariffCode(): int
	{
		return $this->shipping->order->price['tariffCode'] ?? 0;
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
		$tariffCode = $this->getTariffCode();

		/** @noinspection PhpUnnecessaryLocalVariableInspection */
		$isTariffToPoint = WishboxcdekHelper::isTariffToPoint($tariffCode);

		return $isTariffToPoint;
	}
}
