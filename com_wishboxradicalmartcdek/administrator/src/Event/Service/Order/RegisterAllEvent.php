<?php
/**
 * @copyright   (c) 2013-2024 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later;
 */
namespace Joomla\Component\Wishboxradicalmartcdek\Administrator\Event\Service\Order;

use Exception;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\Component\Wishboxradicalmartcdek\Administrator\Service\OrderService;
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
	 * @param   OrderService  $value  The value to set
	 *
	 * @return  OrderService
	 *
	 * @throws Exception
	 *
	 * @since  1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	protected function onSetSubject(OrderService $value): OrderService
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
	 * @param   OrderService  $value  Value
	 *
	 * @return  OrderService
	 *
	 * @since  1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	protected function onGetSubject(OrderService $value): OrderService
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
	 * @return string[]
	 *
	 * @since  1.0.0
	 */
	public function getOrderServise(): array
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
