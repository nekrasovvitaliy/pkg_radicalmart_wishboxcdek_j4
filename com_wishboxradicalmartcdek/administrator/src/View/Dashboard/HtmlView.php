<?php
/**
 * @copyright   (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later;
 */
namespace Joomla\Component\WishboxRadicalMartCdek\Administrator\View\Dashboard;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\HTML\Helpers\Sidebar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\WishboxRadicalMartCdek\Administrator\Helper\WishboxRadicalMartCdekHelper;

/**
 * View class for the dashboard.
 *
 * @since  1.0.0
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Template name
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function display($tpl = null)
	{
		$this->addToolbar();
		$this->sidebar = Sidebar::render();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	protected function addToolbar(): void
	{
		$app = Factory::getApplication();
		$canDo = WishboxRadicalMartCdekHelper::getActions();
		$option = strtolower($app->getInput()->get('option', ''));
		ToolbarHelper::title(Text::_(mb_strtoupper($option)), 'generic');

		if ($canDo->get('core.admin'))
		{
			ToolbarHelper::preferences($option);
		}

		// Set sidebar action
		Sidebar::setAction('index.php?option=' . $option . '&view=' . $this->getName());
	}
}
