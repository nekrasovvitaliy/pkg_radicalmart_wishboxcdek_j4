<?php
/**
 * @copyright 2013-2024 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Form\Preparer;

use Exception;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Object\CMSObject;
use Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Form\FormPreparer;
use Joomla\Registry\Registry;
use stdClass;

/**
 * @since 1.0.0
 */
class PersonalshippingmethodPreparer extends FormPreparer
{
	/**
	 * @var   array|CMSObject|stdClass  $shipping  Shipping
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

		$this->cityCode = $this->getCityCode();

		$this->prepareOfficeCodeField();
	}

	/**
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	protected function prepareOfficeCodeField(): void
	{
		if (is_object($this->data))
		{
			if ($this->cityCode)
			{
				if (!$this->form->setFieldAttribute(
					'officeCode',
					'cityCode',
					$this->cityCode
				))
				{
					throw new Exception('form->setFieldAttribute return false', 500);
				}
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
	protected function getCityCode(): int
	{
		/** @noinspection PhpUndefinedFieldInspection */
		return (int) $this->data->shipping['shipping_method_' . $this->getShippingId()]['cityCode'];
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
