<?php
/**
 * @copyright   (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliY@list.ru>
 * @license     GNU General Public License version 2 or later;
 */
namespace Joomla\Component\WishboxRadicalMartCdek\Administrator\Controller;

use Joomla\CMS\MVC\Controller\BaseController;
use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * WishboxRadicalMartCdek primary display controller.
 *
 * @since 1.0.0
 */
class DisplayController extends BaseController
{
	/**
	 * The default view.
	 *
	 * @var    string
	 *
	 * @since  1.0.0
	 */
	protected $default_view = 'dashboard'; // phpcs:ignore
}
