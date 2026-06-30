<?php
/**
 * @copyright   (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later
 */

namespace Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistration\Service\OrderRegistration;

use UnexpectedValueException;
use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Defines the trait for a RadicalMartOrderRegistrationService aware class.
 *
 * @since 1.0.0
 */
trait RadicalMartOrderRegistrationServiceAwareTrait
{
	/**
	 * @var RadicalMartOrderRegistrationService|null
	 *
	 * @since 1.0.0
	 */
	private ?RadicalMartOrderRegistrationService $radicalMartOrderRegistrationService = null;

	/**
	 * Get the RadicalMart order registration service.
	 *
	 * @return RadicalMartOrderRegistrationService
	 *
	 * @throws UnexpectedValueException
	 *
	 * @since 1.0.0
	 */
	protected function getRadicalMartOrderRegistrationService(): RadicalMartOrderRegistrationService
	{
		if ($this->radicalMartOrderRegistrationService)
		{
			return $this->radicalMartOrderRegistrationService;
		}

		throw new UnexpectedValueException('RadicalMartOrderRegistrationService not set in ' . __CLASS__);
	}

	/**
	 * Set the RadicalMart order registration service.
	 *
	 * @param   RadicalMartOrderRegistrationService  $radicalMartOrderRegistrationService  RadicalMart order registration service
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function setRadicalMartOrderRegistrationService(
		RadicalMartOrderRegistrationService $radicalMartOrderRegistrationService
	): void
	{
		$this->radicalMartOrderRegistrationService = $radicalMartOrderRegistrationService;
	}
}
