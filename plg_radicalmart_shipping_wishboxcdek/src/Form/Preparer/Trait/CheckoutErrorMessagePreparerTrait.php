<?php
/**
 * @copyright   (c) 2013-2025 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later
 */
namespace Joomla\Plugin\RadicalMartShipping\WishboxCdek\Form\Preparer\Trait;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * @method getTariffCode(): integer
 * @method getFormData(): Form data
 * @method getProducts(): array
 * @method getShipping(): stdClass
 * @method getCityCode()
 * @method isTariffToPoint()
 * @method getForm() \Joomla\CMS\Form
 *
 * @since 1.0.0
 */
trait CheckoutErrorMessagePreparerTrait
{
	/**
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	protected function prepareErrorMessageField(): void
	{
		$app = Factory::getApplication();
		$cityCode = $this->getCityCode();

		if ($cityCode > 0)
		{
			try
			{
				/** @var \Joomla\Component\WishboxRadicalMartCdek\Administrator\Model\CalculatorModel $calculatorModel */
				$calculatorModel = $app->bootComponent('com_wishboxradicalmartcdek')
					->getMVCFactory()
					->createModel('Calculator', 'Administrator');

				$calculatorModel->getShippingTariffs(
					$this->getShipping(),
					$this->getFormData(),
					$this->getProducts()
				);
			}
			catch (Exception $e)
			{
				if ($e instanceof NoAvailableTariffsException)
				{
					$this->setMessage(
						Text::_('PLG_RADICALMART_SHIPPING_WISHBOXCDEK_NO_AVAILABLE_TARIFFS_MESSAGE'),
						'alert alert-info'
					);
				}
				else
				{
					$this->setMessage(
						$e->getMessage()
					);
				}
			}
		}
		else
		{
			if (!$this->getForm()->removeField('error_message', $this->shippingFieldAttributeGroup))
			{
				throw new Exception('failed to removeField', 500);
			}
		}
	}

	/**
	 * @param   string  $message  Message
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	private function setMessage(string $message, string $class = 'alert alert-warning'): void
	{
		/** @noinspection PhpUndefinedFieldInspection */
		if (!$this->getForm()->setFieldAttribute(
			'error_message',
			'description',
			$message,
			$this->shippingFieldAttributeGroup
		))
		{
			throw new Exception('Failed to set field attribute');
		}

		/** @noinspection PhpUndefinedFieldInspection */
		if (!$this->getForm()->setFieldAttribute(
			'error_message',
			'type',
			'note',
			$this->shippingFieldAttributeGroup
		))
		{
			throw new Exception('Failed to set field attribute');
		}

		/** @noinspection PhpUndefinedFieldInspection */
		if (!$this->getForm()->setFieldAttribute(
			'error_message',
			'class',
			$class,
			$this->shippingFieldAttributeGroup
		))
		{
			throw new Exception('Failed to set field attribute');
		}
	}
}
