<?php
/**
 * @copyright   2013-2024 Nekrasov Vitaliy
 * @license     GNU General Public License version 2 or later
 */
namespace Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Form\Preparer;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Object\CMSObject;
use Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Form\FormPreparer;
use Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Form\Preparer\Trait\CheckoutAddressPreparerTrait;
use Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Form\Preparer\Trait\CheckoutOfficecodePreparerTrait;
use Joomla\Registry\Registry;
use stdClass;

/**
 * @since 1.0.0
 */
class PersonalshippingmethodPreparer extends FormPreparer
{
	use CheckoutOfficecodePreparerTrait;
	use CheckoutAddressPreparerTrait;

	/**
	 * Shipping
	 *
	 * @var array|CMSObject|stdClass
	 *
	 * @since 1.0.0
	 */
	protected array|CMSObject|stdClass $shipping;

	/**
	 * @var   integer  $cityCode  City code
	 *
	 * @since 1.0.0
	 */
	protected int $cityCode;

	/**
	 * @var string|null
	 *
	 * @since 1.0.0
	 */
	protected ?string $shippingFieldAttributeGroup = null;

	/**
	 * @param   Form                      $form      Form
	 * @param   array|CMSObject|Registry  $data      Data
	 * @param   array|CMSObject|stdClass  $shipping  Tmp data
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function __construct(
		Form $form,
		array|CMSObject|Registry $data,
		array|CMSObject|stdClass $shipping
	)
	{
		parent::__construct($form);

		$this->data = $data;
		$this->shipping = $shipping;
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
		$app = Factory::getApplication();
		$method = $app->input->getMethod();

		if ($method == 'GET')
		{
			if (is_array($this->data))
			{
				return isset($this->data['shipping']['shipping_method_' . $this->getShippingId()]['cityCode'])
					? (int) $this->data['shipping']['shipping_method_' . $this->getShippingId()]['cityCode']
					: 0;
			}

			return isset($this->data->shipping['shipping_method_' . $this->getShippingId()]['cityCode'])
				? (int) $this->data->shipping['shipping_method_' . $this->getShippingId()]['cityCode']
				: 0;
		}
		else
		{
			$data = $app->input->post->get('jform');

			return isset($data['shipping']['shipping_method_' . $this->getShippingId()]['cityCode'])
				? (int) $data['shipping']['shipping_method_' . $this->getShippingId()]['cityCode']
				: 0;
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
	 * @return boolean
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	protected function isTariffToPoint(): bool
	{
		$tariffMode = $this->shipping->params->get('tariffMode', '');

		return in_array($tariffMode, ['С-С', 'Д-С']);
	}
}
