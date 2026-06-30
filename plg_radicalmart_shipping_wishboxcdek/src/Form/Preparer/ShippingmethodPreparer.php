<?php
/**
 * @copyright  (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license    GNU General Public License version 2 or later
 */
namespace Joomla\Plugin\RadicalMartShipping\WishboxCdek\Form\Preparer;

use Exception;
use Joomla\CMS\Form\Form;
use Joomla\Plugin\RadicalMartShipping\WishboxCdek\Form\FormPreparer;

/**
 * @since 1.0.0
 */
class ShippingmethodPreparer extends FormPreparer
{
	/**
	 * @var object|array Data
	 *
	 * @since 1.0.0
	 */
	protected object|array $data;

	/**
	 * @param   Form          $form  Form
	 * @param   array|object  $data  Data
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function __construct(Form $form, array|object $data)
	{
		parent::__construct($form);

		$this->data = $data;
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
			if ($this->getSenderCityCode())
			{
				if (!$this->form->setFieldAttribute(
					'sender_office_code',
					'city_code',
					$this->getSenderCityCode(),
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
		return (int) $this->data->params['sender_city_code'];
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
