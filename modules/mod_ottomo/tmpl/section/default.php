<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;
?>
<section id="otm-module-<?php echo (int)$module->id; ?>" class="otm-module otm-module--section<?php echo $moduleclass_sfx ? ' '.$moduleclass_sfx : ''; ?>"<?php if (!empty($module->showtitle) && !empty($module->title)): ?> aria-label="<?php echo htmlspecialchars($module->title, ENT_QUOTES, 'UTF-8'); ?>"<?php endif; ?>>
	<form class="otm-module__form" action="<?php echo JRoute::_('index.php'); ?>" method="post" data-error="<?php echo htmlspecialchars(JText::_('MOD_OTTOMO_ERROR'), ENT_QUOTES, 'UTF-8'); ?>" data-topics-error="<?php echo htmlspecialchars(JText::_('MOD_OTTOMO_CHOOSE_AT_LEAST_ONE_TOPIC'), ENT_QUOTES, 'UTF-8'); ?>">
		<?php if ($pretext !== ''): ?>
		<div class="otm-module__pretext"><?php echo $pretext; ?></div>
		<?php endif; ?>

		<?php if ($topics->mode): ?>
		<fieldset class="otm-module__topics">
			<legend><?php echo JText::_($topics->mode === 'single' ? 'MOD_OTTOMO_CHOOSE_A_TOPIC' : 'MOD_OTTOMO_CHOOSE_TOPICS'); ?></legend>
			<div class="otm-module__chips"><?php echo modOttomoHelper::topicChoices($topics, $module, 'otm-module__topic'); ?></div>
		</fieldset>
		<?php else: ?>
		<?php echo modOttomoHelper::hiddenTopics($topics); ?>
		<?php endif; ?>

		<?php if (count($profileFields)): ?>
		<div class="otm-module__profile">
			<?php foreach ($profileFields as $field): ?>
			<div class="otm-module__field otm-module__field--<?php echo $field->name; ?>">
				<label for="<?php echo $field->id; ?>"><?php echo $field->label; ?><?php if ($field->required): ?> <span class="otm-module__required" aria-hidden="true">*</span><?php endif; ?></label>
				<?php echo modOttomoHelper::input($field, 'inputbox form-control'); ?>
			</div>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>

		<div class="otm-module__row">
			<?php foreach ($essentialFields as $field): ?>
			<div class="otm-module__field otm-module__field--<?php echo $field->name; ?>">
				<label for="<?php echo $field->id; ?>"><?php echo $field->label; ?><?php if ($field->required): ?> <span class="otm-module__required" aria-hidden="true">*</span><?php endif; ?></label>
				<?php echo modOttomoHelper::input($field, 'inputbox form-control'); ?>
			</div>
			<?php endforeach; ?>
			<?php if ($captcha !== ''): ?>
			<div class="otm-module__captcha"><?php echo $captcha; ?></div>
			<?php endif; ?>
			<div class="otm-module__actions">
				<button class="otm-module__submit btn btn-primary" type="submit"><?php echo JText::_('MOD_OTTOMO_SUBSCRIBE'); ?></button>
			</div>
		</div>
		<?php echo $honeypot; ?>
		<div class="otm-module__message" role="status" aria-live="polite"></div>

		<?php echo modOttomoHelper::hiddenFields($module); ?>
	</form>
</section>
