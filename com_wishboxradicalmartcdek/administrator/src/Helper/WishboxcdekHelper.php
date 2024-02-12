<?php
/**
 * @copyright (c) 2023 Nekrasov Vitaliy
 * @license     GNU General Public License version 2 or later;
 */
namespace Joomla\Component\Wishboxcdek\Administrator\Helper;

use Exception;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use Joomla\Registry\Registry;
use RuntimeException;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Wishboxcdek helper.
 *
 * @since  1.0.0
 */
class WishboxcdekHelper
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

		$assetName = 'com_wishboxcdek';

		$actions = [
			'core.admin'
		];

		foreach ($actions as $action)
		{
			$result->set($action, $user->authorise($action, $assetName));
		}

		return $result;
	}

	/**
	 * Get a tariff list in text/value format for a select field
	 *
	 * @return array
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public static function getTariffOptions(): array
	{
		$app = Factory::getApplication();
		$options = [];
		$db = Factory::getContainer()->get(DatabaseDriver::class);
		$query = $db->getQuery(true)
			->select(
				[
					$db->qn('code', 'value'),
					$db->qn('name', 'text'),
				]
			)
			->from($db->qn('#__wishboxcdek_tariffs', 't'));

		$query->order($db->qn('t.code'));

		// Get the options.
		$db->setQuery($query);

		try
		{
			$options = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');
		}

		return $options;
	}
}
