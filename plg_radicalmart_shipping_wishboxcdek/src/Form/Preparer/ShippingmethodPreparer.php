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

/**
 * @since 1.0.0
 */
class ShippingmethodPreparer extends FormPreparer
{
	/**
	 * @var   integer  $senderCityCode  Sender city code
	 *
	 * @since 1.0.0
	 */
	protected int $senderCityCode;

	/**
	 * @param   Form             $form  Form
	 * @param   array|CMSObject  $data  Data
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function __construct(Form $form, array|CMSObject $data)
	{
		parent::__construct($form);

		$this->data = $data;
		$this->senderCityCode = $this->getSenderCityCode();
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

		$this->prepareSenderOfficeCodeField();
	}

	/**
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	protected function prepareSenderOfficeCodeField(): void
	{
		if (is_object($this->data))
		{
			if ($this->senderCityCode)
			{
				if (!$this->form->setFieldAttribute(
					'senderOfficeCode',
					'cityCode',
					$this->senderCityCode,
					'params'
				))
				{
					throw new Exception('', 500);
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
	protected function getSenderCityCode(): int
	{
		return (int) $this->data->params['senderCityCode'];
	}

	/**
	 * @return integer
	 *
	 * @since 1.0.0
	 */
	protected function getShippingId(): int
	{
		return (int) $this->data->id;
	}
}
