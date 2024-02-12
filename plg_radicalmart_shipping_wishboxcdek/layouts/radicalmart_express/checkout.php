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
 * @var  Joomla\CMS\Form\Form $form     Form object.
 * @var  object               $item     Checkout object.
 * @var  object               $shipping Checkout shipping method object.
 *
 */

if (empty($shipping))
{
	return false;
}
?>
<div class="row">
	<?php if ($shipping->params->get('field_country', 1)): ?>
		<div class="col-md-12 mb-3"><?php echo $form->renderField('country', 'shipping', null, ['hiddenLabel' => true]); ?></div>
	<?php endif; ?>
	<?php if ($shipping->params->get('field_city', 1)): ?>
		<div class="col-md-8 mb-3"><?php echo $form->renderField('city', 'shipping', null, ['hiddenLabel' => true]); ?></div>
	<?php endif; ?>
	<?php if ($shipping->params->get('field_zip', 1)): ?>
		<div class="col-md-4 mb-3"><?php echo $form->renderField('zip', 'shipping', null, ['hiddenLabel' => true]); ?></div>
	<?php endif; ?>
	<?php if ($shipping->params->get('field_street', 1)): ?>
		<div class="col-md-8 mb-3"><?php echo $form->renderField('street', 'shipping', null, ['hiddenLabel' => true]); ?></div>
	<?php endif; ?>
	<?php if ($shipping->params->get('field_house', 1)): ?>
		<div class="col-md-4 mb-3"><?php echo $form->renderField('house', 'shipping', null, ['hiddenLabel' => true]); ?></div>
	<?php endif; ?>
	<?php if ($shipping->params->get('field_building', 1)): ?>
		<div class="col-md-3 mb-3"><?php echo $form->renderField('building', 'shipping', null, ['hiddenLabel' => true]); ?></div>
	<?php endif; ?>
	<?php if ($shipping->params->get('field_entrance', 1)): ?>
		<div class="col-md-3 mb-3"><?php echo $form->renderField('entrance', 'shipping', null, ['hiddenLabel' => true]); ?></div>
	<?php endif; ?>
	<?php if ($shipping->params->get('field_floor', 1)): ?>
		<div class="col-md-3 mb-3"><?php echo $form->renderField('floor', 'shipping', null, ['hiddenLabel' => true]); ?></div>
	<?php endif; ?>
	<?php if ($shipping->params->get('field_apartment', 1)): ?>
		<div class="col-md-3 mb-3"><?php echo $form->renderField('apartment', 'shipping', null, ['hiddenLabel' => true]); ?></div>
	<?php endif; ?>
	<?php if ($shipping->params->get('field_comment', 1)): ?>
		<div class="col-md-12 mb-3"><?php echo $form->renderField('comment', 'shipping', null, ['hiddenLabel' => true]); ?></div>
	<?php endif; ?>
</div>