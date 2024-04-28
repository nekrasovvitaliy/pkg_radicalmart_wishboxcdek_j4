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
use Joomla\Component\RadicalMart\Administrator\Helper\LayoutsHelper;

defined('_JEXEC') or die;

/* @deprecated  RadicalMart Shipping - Wishboxcdek v? */
if (LayoutsHelper::isSiteLayoutOverride('plugins.radicalmart_shipping.wishboxcdek.checkout'))
{
	echo LayoutsHelper::renderSiteLayout('plugins.radicalmart_shipping.wishboxcdek.checkout', $displayData);

	return;
}

extract($displayData);

/**
 * Layout variables
 * -----------------
 *
 * @var  Form   $form     Form object.
 * @var  object $item     Checkout object.
 * @var  object $shipping Checkout shipping method object.
 *
 */
?>
<div class="uk-grid-small" uk-grid="">
	<?php if ($shipping->params->get('field_cityCode', 1)): ?>
        <div class="uk-width-1-1">
            <?php echo $form->renderField('cityCode', 'shipping'); ?>
        </div>
	<?php endif; ?>
	<?php if ($shipping->params->get('field_officeCode', 1)): ?>
        <div class="uk-width-1-1">
			<?php echo $form->renderField('officeCode', 'shipping'); ?>
        </div>
	<?php endif; ?>
	<?php if ($shipping->params->get('field_address', 1)): ?>
        <div class="uk-width-1-1">
			<?php echo $form->renderField('address', 'shipping'); ?>
        </div>
	<?php endif; ?>

	<?php if ($shipping->params->get('field_country', 1)): ?>
		<div class="uk-width-1-1"><?php echo $form->renderField('country', 'shipping'); ?></div>
	<?php endif; ?>
	<?php if ($shipping->params->get('field_city', 1)): ?>
		<div class="uk-width-2-3@s"><?php echo $form->renderField('city', 'shipping'); ?></div>
	<?php endif; ?>
	<?php if ($shipping->params->get('field_zip', 1)): ?>
		<div class="uk-width-1-3@s"><?php echo $form->renderField('zip', 'shipping'); ?></div>
	<?php endif; ?>
	<?php if ($shipping->params->get('field_street', 1)): ?>
		<div class="uk-width-2-3@s"><?php echo $form->renderField('street', 'shipping'); ?></div>
	<?php endif; ?>
	<?php if ($shipping->params->get('field_house', 1)): ?>
		<div class="uk-width-1-3@s"><?php echo $form->renderField('house', 'shipping'); ?></div>
	<?php endif; ?>
	<?php if ($shipping->params->get('field_building', 1)): ?>
		<div class="uk-width-1-4@s"><?php echo $form->renderField('building', 'shipping'); ?></div>
	<?php endif; ?>
	<?php if ($shipping->params->get('field_entrance', 1)): ?>
		<div class="uk-width-1-4@s"><?php echo $form->renderField('entrance', 'shipping'); ?></div>
	<?php endif; ?>
	<?php if ($shipping->params->get('field_floor', 1)): ?>
		<div class="uk-width-1-4@s"><?php echo $form->renderField('floor', 'shipping'); ?></div>
	<?php endif; ?>
	<?php if ($shipping->params->get('field_apartment', 1)): ?>
		<div class="uk-width-1-4@s"><?php echo $form->renderField('apartment', 'shipping'); ?></div>
	<?php endif; ?>
	<?php if ($shipping->params->get('field_comment', 1)): ?>
		<div class="uk-width-1-1"><?php echo $form->renderField('comment', 'shipping'); ?></div>
	<?php endif; ?>
</div>