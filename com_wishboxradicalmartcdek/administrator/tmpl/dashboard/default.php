<?php
// phpcs:ignoreFile
/**
 * @copyright (c) 2023 Nekrasov Vitaliy
 * @license     GNU General Public License version 2 or later;
 */
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');

$user = Factory::getApplication()->getIdentity();
$userId = $user->get('id');
?>

<form
	action="<?php echo Route::_('index.php?option=com_wishboxcdek&view=dashboard'); ?>"
	method="post"
	name="adminForm" id="adminForm"
>
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<input type="hidden" name="task" value=""/>
				<input type="hidden" name="boxchecked" value="0"/>
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
