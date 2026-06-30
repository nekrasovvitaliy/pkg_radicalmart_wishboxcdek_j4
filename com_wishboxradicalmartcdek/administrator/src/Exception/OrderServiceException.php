<?php
/**
 * @copyright   (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later;
 */
namespace Joomla\Component\WishboxRadicalMartCdek\Administrator\Exception;

use Exception;

/**
 * @since 1.0.0
 */
class OrderServiceException extends Exception
{
	/**
	 * @var integer
	 *
	 * @since 1.0.0
	 */
	protected int $orderId;

	/**
	 * @param   string   $message  Message
	 * @param   integer  $code     Code
	 * @param   integer  $orderId  Order id
	 *
	 * @since 1.0.0
	 */
	public function __construct(
		string $message,
		int $code = 0,
		int $orderId = 0
	)
	{
		parent::__construct($message, $code);

		$this->orderId = $orderId;
	}

	/**
	 * @return integer
	 *
	 * @since 1.0.0
	 */
	public function getOrderId(): int
	{
		return $this->orderId;
	}
}
