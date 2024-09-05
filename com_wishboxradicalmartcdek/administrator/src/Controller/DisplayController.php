<?php
/**
 * @copyright   (c) 2013-2023 Nekrasov Vitaliy <nekrasov_vitaliY@list.ru>
 * @license     GNU General Public License version 2 or later;
 */
namespace Joomla\Component\Wishboxradicalmartcdek\Administrator\Controller;

use Exception;
use Joomla\CMS\MVC\Controller\BaseController;
use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Wishboxradicalmartcdek primary display controller.
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

	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link InputFilter::clean()}.
	 *
	 * @return  static  A \JControllerLegacy object to support chaining.
	 *
	 * @throws Exception
	 *
	 * @since   1.0.0
	 *
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function display($cachable = false, $urlparams = [])
	{
		return parent::display();
	}
}
