<?php
/**
 * @copyright  (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license    GNU General Public License version 2 or later
 */
namespace Joomla\Plugin\RadicalMartShipping\WishboxCdek\Form\Preparer\Trait;

use Exception;
use Joomla\CMS\Form\Form;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\RadicalMartShipping\WishboxCdek\Service\Calculator\Adapter\CalculatorAdapterService;
use stdClass;

/**
 * @method int getTariffCode()
 * @method Form getForm()
 * @method array getFormData()
 * @method array getProducts()
 * @method stdClass getShipping()
 * @method DispatcherInterface getDispatcher()
 *
 * @since 1.0.0
 */
trait CheckoutOfficeGoogleMapPreparerTrait
{
	/**
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	protected function prepareOfficeGoogleMapField(): void
	{
		$cityCode = $this->getCityCode();

		if ($cityCode > 0)
		{
			$isTariffToPoint = $this->isTariffToPoint();

			if ($isTariffToPoint)
			{
				$result = $this->getForm()->setFieldAttribute(
					'office_google_map',
					'city_code',
					$this->getCityCode(),
					$this->shippingFieldAttributeGroup
				);

				if (!$result)
				{
					throw new Exception('failed to set attribute', 500);
				}

				if (method_exists($this, 'getProducts'))
				{
					$calculatorAdapterService = new CalculatorAdapterService(
						$this->shipping,
						$this->formData,
						$this->products,
						$this->getDispatcher()
					);

					$packageRequests = $calculatorAdapterService->getPackages();

					$packagesData = [];

					foreach ($packageRequests as $packageRequest)
					{
						$packagesData[] = [
							'weight' => $packageRequest->weight * 0.001,
							'width'  => $packageRequest->width,
							'height' => $packageRequest->height,
							'length' => $packageRequest->length
						];
					}

					$packagesData = json_encode($packagesData);
					$result       = $this->getForm()->setFieldAttribute(
						'office_google_map',
						'packages',
						$packagesData,
						$this->shippingFieldAttributeGroup
					);

					if (!$result)
					{
						throw new Exception('failed to set attribute', 500);
					}
				}
			}
		}
		else
		{
			if (!$this->getForm()->removeField('office_google_map', $this->shippingFieldAttributeGroup))
			{
				throw new Exception('failed to removeField', 500);
			}
		}
	}
}
