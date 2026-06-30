<?php
/**
 * @copyright   (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later
 */

namespace Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistration\Service\OrderRegistration;

use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Interface to be implemented by classes depending on a RadicalMart order registration service.
 *
 * @since 1.0.0
 */
interface RadicalMartOrderRegistrationServiceAwareInterface
{
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
	): void;
}
