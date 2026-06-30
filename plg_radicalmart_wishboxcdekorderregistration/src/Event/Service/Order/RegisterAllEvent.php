<?php
/**
 * @copyright   (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later
 */
namespace Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistration\Event\Service\Order;

use Exception;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistration\Service\OrderRegistration\RadicalMartOrderRegistrationService;
use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since 1.0.0
 */
abstract class RegisterAllEvent extends AbstractEvent
{
	/**
	 * Setter for the subject argument.
	 *
	 * @param   RadicalMartOrderRegistrationService  $value  The value to set
	 *
	 * @return  RadicalMartOrderRegistrationService
	 *
	 * @throws Exception
	 *
	 * @since  1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	protected function onSetSubject(RadicalMartOrderRegistrationService $value): RadicalMartOrderRegistrationService
	{
		return $value;
	}

	/**
	 * Setter for the orderIds argument.
	 *
	 * @param   integer[]  $value  The value to set
	 *
	 * @return  integer[]
	 *
	 * @throws Exception
	 *
	 * @since  1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	protected function onSetOrderIds(array $value): array
	{
		if (empty($value))
		{
			throw new Exception('Value must be not empty');
		}

		return $value;
	}

	/**
	 * Getter for the subject argument.
	 *
	 * @param   RadicalMartOrderRegistrationService  $value  Value
	 *
	 * @return  RadicalMartOrderRegistrationService
	 *
	 * @since  1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	protected function onGetSubject(RadicalMartOrderRegistrationService $value): RadicalMartOrderRegistrationService
	{
		return $value;
	}

	/**
	 * Getter for the orderIds argument.
	 *
	 * @param   integer[]  $value  Value
	 *
	 * @return  integer[]
	 *
	 * @since  1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	protected function onGetOrderIds(array $value): array
	{
		return $value;
	}

	/**
	 * @return RadicalMartOrderRegistrationService
	 *
	 * @since  1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function getRadicalMartOrderRegistrationService(): RadicalMartOrderRegistrationService
	{
		return $this->arguments['subject'];
	}

	/**
	 * @return string[]
	 *
	 * @since  1.0.0
	 */
	public function getOrderIds(): array
	{
		return $this->arguments['orderIds'];
	}
}
