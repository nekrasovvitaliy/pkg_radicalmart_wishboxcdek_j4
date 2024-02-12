<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license     GNU General Public License version 2 or later
 */
namespace Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Exception;

use Exception;
use Joomla\CMS\Language\Text;
use stdClass;

/**
 * @since 1.0.0
 */
class EmptyProductCodeException extends Exception
{
	/**
	 * @var stdClass $product Product
	 *
	 * @since 1.0.0
	 */
	protected stdClass $product;

	/**
	 * @param   stdClass  $product  Product
	 *
	 * @since 1.0.0
	 */
	public function __construct(stdClass $product)
	{
		$this->product = $product;
		$message = Text::sprintf(
			'PLG_RADICALMART_SHIPPING_WISHBOXCDEK_MESSAGE_PRODUCT_S_HAS_EMPTY_CODE',
			$this->product->id . ':' . $this->product->title
		);

		parent::__construct($message, 200);
	}
}
