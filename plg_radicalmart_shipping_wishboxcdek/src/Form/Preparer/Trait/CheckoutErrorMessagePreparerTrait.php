<?php
/**
 * @copyright   (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later
 */
namespace Joomla\Plugin\RadicalMartShipping\WishboxCdek\Form\Preparer\Trait;

use Exception;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\Component\WishboxCdek\Site\Exception\NoAvailableTariffsException;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\RadicalMartShipping\WishboxCdek\Service\Calculator\Adapter\CalculatorAdapterService;
use stdClass;
use WishboxCdekLibrary\Service\Calculator\CalculatorService;

/**
 * @method int getTariffCode()
 * @method array getFormData()
 * @method array getProducts()
 * @method stdClass getShipping()
 * @method int getCityCode()
 * @method bool isTariffToPoint()
 * @method Form getForm()
 * @method CalculatorService getCalculatorService()
 * @method DispatcherInterface getDispatcher()
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
		$cityCode = $this->getCityCode();

		if ($cityCode > 0)
		{
			try
			{
				$calculatorAdapterService = new CalculatorAdapterService(
					$this->shipping,
					$this->formData,
					$this->products,
					$this->getDispatcher()
				);

				$this->getCalculatorService()->getShippingTariffs($calculatorAdapterService);
			}
			catch (NoAvailableTariffsException $e)
			{
				$this->setMessage(
					Text::_('PLG_RADICALMART_SHIPPING_WISHBOXCDEK_NO_AVAILABLE_TARIFFS_MESSAGE'),
					'alert alert-info'
				);
			}
			catch (Exception $e)
			{
				$this->setMessage(
					$e->getMessage()
				);
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
		if (!$this->getForm()->setFieldAttribute(
			'error_message',
			'description',
			$message,
			$this->shippingFieldAttributeGroup
		))
		{
			throw new Exception('Failed to set field attribute');
		}

		if (!$this->getForm()->setFieldAttribute(
			'error_message',
			'type',
			'note',
			$this->shippingFieldAttributeGroup
		))
		{
			throw new Exception('Failed to set field attribute');
		}

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
