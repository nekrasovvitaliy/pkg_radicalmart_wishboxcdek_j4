<?php
/**
 * @copyright   (c) 2013-2025 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later
 */

use Joomla\CMS\Document\Document;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die;

/** @var array $displayData */

extract($displayData);

/**
 * Layout variables
 * -----------------
 *
 * @var string $redirectUrl
 */

$app = Factory::getApplication();

/** @var Document $document */
$document = $app->getDocument();

$wa = $document->getWebAssetManager();
$wa->getRegistry()
	->addRegistryFile('media/plg_radicalmart_wishboxcdekorderstatusupdater/joomla.asset.json');
$wa->useScript('plg_radicalmart_wishboxcdekorderstatusupdater.script');

$title = Text::_('PLG_RADICALMART_WISHBOXCDEKORDERSTATUSUPDATER_UPDATE_STATUS')
?>
<joomla-toolbar-button list-selection id="toolbar-wishboxcdek-update-statuses">
    <button class="btn btn-success">
        <span class="icon-refresh" aria-hidden="true"></span>
        <?php echo $title; ?>
    </button>
</joomla-toolbar-button>
<form
    action="<?php echo Route::_('index.php?option=com_wishboxcdek'); ?>"
    method="post"
    name="wishboxradicalmartcdekOrders"
    id="wishboxradicalmartcdek-orders"
>
    <input type="hidden" name="component" value="radicalmart" />
    <input type="hidden" name="task" value="orders.updatestatuses" />
    <input type="hidden" name="redirect_url" value="<?php echo $redirectUrl; ?>" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
