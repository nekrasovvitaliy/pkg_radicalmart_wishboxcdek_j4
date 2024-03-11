<?php
/**
 * @copyright (c) 2023 Nekrasov Vitaliy
 * @license     GNU General Public License version 2 or later;
 */
namespace Joomla\Component\Wishboxradicalmartcdek\Administrator\Service\Html;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\DatabaseDriver;

/**
 * Wishboxradicalmartcdek HTML Helper.
 *
 * @since  1.0.0
 */
class Wishboxradicalmartcdek
{
	use DatabaseAwareTrait;

	/**
	 * Public constructor.
	 *
	 * @param   DatabaseDriver  $db  The Joomla DB driver object for the site's database.
	 *
	 * @since 1.0.0
	 */
	public function __construct(DatabaseDriver $db)
	{
		//$this->setDbo($db);
	}

	/**
	 * @param $value
	 * @param $view
	 * @param $field
	 * @param $i
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public function toggle($value = 0, $view='', $field='', $i='')
	{
		$states = [
			0 => ['icon-unpublish', Text::_('Toggle'), ''],
			1 => ['icon-publish', Text::_('Toggle'), '']
		];

		$state  = ArrayHelper::getValue($states, (int) $value, $states[0]);
		$text   = '<span aria-hidden="true" class="' . $state[0] . '"></span>';
		$html   = '<a href="javascript:void(0);" class="tbody-icon ' . $state[2] . '"';
		$html  .= 'onclick="return Joomla.toggleField(\'cb'.$i.'\',\'' . $view . '.toggle\',\'' . $field
			. '\')" title="' . Text::_($state[1]) . '">' . $text . '</a>';

		return $html;
	}
}
