<?php
/**
 * @copyright   (c) 2013-2025 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later
 */
namespace Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistrator\Event\Model\Orders;

use Exception;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistrator\Administrator\Model\OrdersModel;
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
	 * @param   OrdersModel  $value  The value to set
	 *
	 * @return  OrdersModel
	 *
	 * @throws Exception
	 *
	 * @since  1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	protected function onSetSubject(OrdersModel $value): OrdersModel
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
	 * @param   OrdersModel  $value  Value
	 *
	 * @return  OrdersModel
	 *
	 * @since  1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	protected function onGetSubject(OrdersModel $value): OrdersModel
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
	 * @return OrdersModel
	 *
	 * @since  1.0.0
	 */
	public function getOrdersModel(): OrdersModel
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
