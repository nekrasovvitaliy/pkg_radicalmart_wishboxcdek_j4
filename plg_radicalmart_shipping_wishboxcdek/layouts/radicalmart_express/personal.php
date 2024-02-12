<?php
/**
 * @version 1.0.0
 * @package Joomla.Plugin
 * @subpackage Radicalmart_shipping.Wishboxcdek
 * @author Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @copyright Copyright (c) 2023 Nekrasov Vitaliy
 * @license GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die;

extract($displayData);

/**
 * Layout variables
 * -----------------
 *
 * @var  \Joomla\CMS\Form\Form $form      Form object.
 * @var  object                $item      Customer object.
 * @var  object                $shipping  Checkout shipping method object.
 * @var  array                 $fieldsets Checkout shipping method object.
 * @var  string                $group     Fields group target.
 *
 */

if (empty($shipping))
{
	return false;
}
?>
<div id="personal_shipping_method_<?php echo $shipping->id; ?>" class="options-form form-horizontal">
	<div class="row">
		<?php if ($shipping->params->get('field_country', 1)): ?>
			<div class="col-md-12 mb-3"><?php echo $form->renderField('country', $group); ?></div>
		<?php endif; ?>
		<?php if ($shipping->params->get('field_city', 1)): ?>
			<div class="col-md-8 mb-3"><?php echo $form->renderField('city', $group); ?></div>
		<?php endif; ?>
		<?php if ($shipping->params->get('field_zip', 1)): ?>
			<div class="col-md-4 mb-3"><?php echo $form->renderField('zip', $group); ?></div>
		<?php endif; ?>
		<?php if ($shipping->params->get('field_street', 1)): ?>
			<div class="col-md-8 mb-3"><?php echo $form->renderField('street', $group); ?></div>
		<?php endif; ?>
		<?php if ($shipping->params->get('field_house', 1)): ?>
			<div class="col-md-4 mb-3"><?php echo $form->renderField('house', $group); ?></div>
		<?php endif; ?>
		<?php if ($shipping->params->get('field_building', 1)): ?>
			<div class="col-md-3 mb-3"><?php echo $form->renderField('building', $group); ?></div>
		<?php endif; ?>
		<?php if ($shipping->params->get('field_entrance', 1)): ?>
			<div class="col-md-3 mb-3"><?php echo $form->renderField('entrance', $group); ?></div>
		<?php endif; ?>
		<?php if ($shipping->params->get('field_floor', 1)): ?>
			<div class="col-md-3 mb-3"><?php echo $form->renderField('floor', $group); ?></div>
		<?php endif; ?>
		<?php if ($shipping->params->get('field_apartment', 1)): ?>
			<div class="col-md-3 mb-3"><?php echo $form->renderField('apartment', $group); ?></div>
		<?php endif; ?>
	</div>
</div>

