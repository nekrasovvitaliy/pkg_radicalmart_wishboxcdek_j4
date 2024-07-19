<?php
/**
 * @copyright   (c) 2023 Nekrasov Vitaliy
 * @license     GNU General Public License version 2 or later;
 */
namespace Joomla\Component\Wishboxradicalmartcdek\Administrator\Helper;

use Exception;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Wishboxradicalmartcdek helper.
 *
 * @since  1.0.0
 */
class WishboxradicalmartcdekHelper
{
	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return  Registry
	 *
	 * @throws Exception
	 *
	 * @since   1.0.0
	 */
	public static function getActions(): Registry
	{
		$user = Factory::getApplication()->getIdentity();
		$result = new Registry;

		$assetName = 'com_wishboxradicalmartcdek';

		$actions = [
			'core.admin'
		];

		foreach ($actions as $action)
		{
			$result->set($action, $user->authorise($action, $assetName));
		}

		return $result;
	}
}
