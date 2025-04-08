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
class AfterRegisterEvent extends AbstractEvent
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
	 * Setter for the key argument.
	 *
	 * @param   integer  $value  The value to set
	 *
	 * @return  integer
	 *
	 * @throws Exception
	 *
	 * @since  1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	protected function onSetKey(int $value): int
	{
		if ($value < 0)
		{
			throw new Exception('Value must be not less than zero');
		}

		return $value;
	}

	/**
	 * Setter for the order argument.
	 *
	 * @param   object  $value  The value to set
	 *
	 * @return  object
	 *
	 * @throws Exception
	 *
	 * @since  1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	protected function onSetOrder(object $value): object
	{
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
	 * Getter for the key argument.
	 *
	 * @param   integer  $value  Value
	 *
	 * @return  integer
	 *
	 * @since  1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	protected function onGetKey(int $value): int
	{
		return $value;
	}

	/**
	 * Getter for the order argument.
	 *
	 * @param   object  $value  Value
	 *
	 * @return  object
	 *
	 * @since  1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	protected function onGetOrder(object $value): object
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
	 * @return integer
	 *
	 * @since  1.0.0
	 */
	public function getKey(): int
	{
		return $this->arguments['key'];
	}
}
