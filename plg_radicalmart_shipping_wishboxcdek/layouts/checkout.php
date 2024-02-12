<?php
/**
 * @version 1.0.0
 * @package Joomla.Plugin
 * @subpackage Radicalmart_shipping.Wishboxcdek
 * @author Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @copyright Copyright (c) 2023 Nekrasov Vitaliy
 * @license GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */
use Joomla\CMS\Form\Form;

defined('_JEXEC') or die;

extract($displayData);

/**
 * Layout variables
 * -----------------
 *
 * @var  Form    $form     Form object.
 * @var  object  $item     Checkout object.
 * @var  object  $shipping Checkout shipping method object.
 * @var  boolean $new      Button target.
 */

if (empty($shipping))
{
	return false;
}

?>
<div class="row">
	<?php if ($shipping->params->get('field_country', 1)): ?>
        <div class="col-md-12 mb-3"><?php echo $form->renderField('country', 'shipping'); ?></div>
	<?php endif; ?>
	<?php if ($shipping->params->get('field_city', 1)): ?>
		<div class="col-md-8 mb-3"><?php echo $form->renderField('city', 'shipping'); ?></div>
	<?php endif; ?>

    <div class="col-md-12 mb-3">
        <?php echo $form->renderField('cityCode', 'shipping'); ?>
    </div>

    <div class="col-md-12 mb-3">
		<?php echo $form->renderField('officeCode', 'shipping'); ?>
    </div>

    <div class="col-md-12 mb-3">
		<?php echo $form->renderField('tariffCode', 'shipping'); ?>
    </div>

	<?php if ($shipping->params->get('field_zip', 1)): ?>
		<div class="col-md-4 mb-3"><?php echo $form->renderField('zip', 'shipping'); ?></div>
	<?php endif; ?>
	<?php if ($shipping->params->get('field_street', 1)): ?>
		<div class="col-md-8 mb-3"><?php echo $form->renderField('street', 'shipping'); ?></div>
	<?php endif; ?>

	<?php if ($shipping->params->get('field_house', 1)): ?>
		<div class="col-md-4 mb-3"><?php echo $form->renderField('house', 'shipping'); ?></div>
	<?php endif; ?>

	<?php if ($shipping->params->get('field_building', 1)): ?>
		<div class="col-md-3 mb-3"><?php echo $form->renderField('building', 'shipping'); ?></div>
	<?php endif; ?>

	<?php if ($shipping->params->get('field_entrance', 1)): ?>
		<div class="col-md-3 mb-3"><?php echo $form->renderField('entrance', 'shipping'); ?></div>
	<?php endif; ?>

	<?php if ($shipping->params->get('field_floor', 1)): ?>
		<div class="col-md-3 mb-3"><?php echo $form->renderField('floor', 'shipping'); ?></div>
	<?php endif; ?>

	<?php if ($shipping->params->get('field_apartment', 1)): ?>
		<div class="col-md-3 mb-3"><?php echo $form->renderField('apartment', 'shipping'); ?></div>
	<?php endif; ?>

	<?php if ($shipping->params->get('field_comment', 1)): ?>
		<div class="col-md-12 mb-3"><?php echo $form->renderField('comment', 'shipping'); ?></div>
	<?php endif; ?>
</div>
