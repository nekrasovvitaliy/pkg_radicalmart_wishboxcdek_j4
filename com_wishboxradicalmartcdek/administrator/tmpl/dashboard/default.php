<?php
/**
 * @copyright   (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later;
 *
 * phpcs:ignoreFile
 */
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die;

HTMLHelper::_('bootstrap.tooltip');
?>

<form
	action="<?php echo Route::_('index.php?option=com_wishboxcdek&view=dashboard'); ?>"
	method="post"
	name="adminForm" id="adminForm"
>
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<input type="hidden" name="task" value="" />
				<input type="hidden" name="boxchecked" value="0" />
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
