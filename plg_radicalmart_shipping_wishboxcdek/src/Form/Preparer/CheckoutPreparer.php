<?php
/**
 * @copyright   (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later
 */
namespace Joomla\Plugin\RadicalMartShipping\WishboxCdek\Form\Preparer;

use Exception;
use Joomla\CMS\Form\Form;
use Joomla\Component\WishboxCdek\Site\Helper\WishboxCdekHelper;
use Joomla\Event\DispatcherAwareInterface;
use Joomla\Event\DispatcherAwareTrait;
use Joomla\Plugin\RadicalMartShipping\WishboxCdek\Form\FormPreparer;
use Joomla\Plugin\RadicalMartShipping\WishboxCdek\Form\Preparer\Trait\CheckoutErrorMessagePreparerTrait;
use Joomla\Plugin\RadicalMartShipping\WishboxCdek\Form\Preparer\Trait\CheckoutOfficecodePreparerTrait;
use Joomla\Plugin\RadicalMartShipping\WishboxCdek\Form\Preparer\Trait\CheckoutOfficeGoogleMapPreparerTrait;
use Joomla\Plugin\RadicalMartShipping\WishboxCdek\Form\Preparer\Trait\CheckoutOfficeYandexMapPreparerTrait;
use Joomla\Plugin\RadicalMartShipping\WishboxCdek\Form\Preparer\Trait\CheckoutTariffcodePreparerTrait;
use stdClass;
use WishboxCdekLibrary\Service\Calculator\CalculatorServiceAwareInterface;
use WishboxCdekLibrary\Service\Calculator\CalculatorServiceAwareTrait;

/**
 * @since 1.0.0
 */
class CheckoutPreparer extends FormPreparer implements CalculatorServiceAwareInterface, DispatcherAwareInterface
{
	use CalculatorServiceAwareTrait;
	use DispatcherAwareTrait;
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
	public stdClass $shipping {
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
	 * @var   array  $producs Products
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
		parent::prepare();

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
			|| WishboxCdekHelper::isTariffToPoint($this->getTariffCode())
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
	 *
	 * @noinspection PhpUnnecessaryLocalVariableInspection
	 */
	protected function getCityCode(): int
	{
		$formData = $this->formData;
		$cityCode = (isset($formData['shipping']) && isset($formData['shipping']['city_code']))
			? (int) $formData['shipping']['city_code']
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
		return $this->shipping->order->price['tariff_code'] ?? 0;
	}
}
