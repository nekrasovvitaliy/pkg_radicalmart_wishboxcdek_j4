<?php
/**
 * @copyright   (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */
use Joomla\CMS\Form\Form;

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
	<?php if ($shipping->params->get('field_city_code', 1)): ?>
        <div class="uk-width-1-1">
            <?php echo $form->renderField('city_code', 'shipping'); ?>
        </div>
	<?php endif; ?>
	<?php if ($shipping->params->get('field_office_code', 1)): ?>
        <div class="uk-width-1-1">
			<?php echo $form->renderField('office_code', 'shipping'); ?>
        </div>
	<?php endif; ?>

    <div class="uk-width-1-1">
		<?php echo $form->renderField('office_google_map', 'shipping'); ?>
    </div>

    <div class="uk-width-1-1">
		<?php echo $form->renderField('office_yandex_map', 'shipping'); ?>
    </div>

	<?php if ($shipping->params->get('field_address', 1)): ?>
        <div class="uk-width-1-1">
			<?php echo $form->renderField('address', 'shipping'); ?>
        </div>
	<?php endif; ?>

    <div class="uk-width-1-1">
		<?php echo $form->renderField('tariff_code', 'shipping'); ?>
    </div>

    <div class="uk-width-1-1">
		<?php echo $form->renderField('error_message', 'shipping'); ?>
    </div>

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