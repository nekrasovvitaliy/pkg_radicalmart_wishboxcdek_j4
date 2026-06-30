<?php
/**
 * @copyright   (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later
 */

namespace Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistration\Service\OrderRegistration\Adapter;

use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Interface to be implemented by classes depending on an order registration adapter.
 *
 * @since 1.0.0
 */
interface OrderRegistrationAdapterAwareInterface
{
	/**
	 * Set the order registration adapter.
	 *
	 * @param   RadicalMartOrderRegistrationAdapter  $orderRegistrationAdapter  Order registration adapter
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function setOrderRegistrationAdapter(
		RadicalMartOrderRegistrationAdapter $orderRegistrationAdapter
	): void;
}
