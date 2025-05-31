<?php
/**
 * @copyright   (c) 2013-2025 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later;
 */
namespace Joomla\Component\WishboxRadicalMartCdek\Administrator\Helper;

use Exception;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use Joomla\Registry\Registry;
use stdClass;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * WishboxRadicalMartCdek helper.
 *
 * @since  1.0.0
 */
class WishboxRadicalMartCdekHelper
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

	/**
	 * @param   string  $cdekNumber  Cdek number
	 *
	 * @return integer
	 *
	 * @since 1.0.0
	 */
	public static function getOrderIdByCdekNumber(string $cdekNumber): int
	{
		$db = Factory::getContainer()->get(DatabaseDriver::class);

		$query = $db->createQuery()
			->select($db->qn('id'))
			->from($db->qn('#__radicalmart_orders'))
			->where('JSON_EXTRACT(shipping, ' . $db->q('$.data.tracking_number') . ') = ' . $db->q($cdekNumber));

		$db->setQuery($query);

		return (int) $db->loadResult();
	}

	/**
	 * @param   stdClass  $product          Product
	 * @param   string    $destinationUnit  Destination unit
	 *
	 * @return float
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public static function getProductWeight(stdClass $product, string $destinationUnit): float
	{
		if (!in_array($destinationUnit, ['g', 'kg', 't']))
		{
			throw new Exception('Invalid unit value', 500);
		}

		$weight = $product->shipping->get('weight', 0);
		$unit = $product->shipping->get('weight_unit', '');

		if (!in_array($unit, ['g', 'kg', 't']))
		{
			throw new Exception('Invalid unit value', 500);
		}

		if ($destinationUnit == 'kg' && $unit == 'g')
		{
			return $weight / 1000;
		}

		if ($destinationUnit == 't' && $unit == 'g')
		{
			return $weight / 1000000;
		}

		if ($destinationUnit == 't' && $unit == 'kg')
		{
			return $weight / 1000;
		}

		if ($destinationUnit == 'kg' && $unit == 't')
		{
			return $weight * 1000;
		}

		return $weight;
	}
}
