<?php
/**
 * @copyright   (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later
 */

namespace Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistration\Service\OrderRegistration\Adapter;

use UnexpectedValueException;
use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Defines the trait for an order registration adapter aware class.
 *
 * @since 1.0.0
 */
trait OrderRegistrationAdapterAwareTrait
{
	/**
	 * @var RadicalMartOrderRegistrationAdapter|null
	 *
	 * @since 1.0.0
	 */
	private ?RadicalMartOrderRegistrationAdapter $orderRegistrationAdapter = null;

	/**
	 * Get the order registration adapter.
	 *
	 * @return RadicalMartOrderRegistrationAdapter
	 *
	 * @throws UnexpectedValueException
	 *
	 * @since 1.0.0
	 */
	protected function getOrderRegistrationAdapter(): RadicalMartOrderRegistrationAdapter
	{
		if ($this->orderRegistrationAdapter)
		{
			return $this->orderRegistrationAdapter;
		}

		throw new UnexpectedValueException('RadicalMartOrderRegistrationAdapter not set in ' . __CLASS__);
	}

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
	): void
	{
		$this->orderRegistrationAdapter = $orderRegistrationAdapter;
	}
}
