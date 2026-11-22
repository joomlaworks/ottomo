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
<div id="otm-module-<?php echo (int)$module->id; ?>" class="otm-module otm-module--compact<?php echo $moduleclass_sfx ? ' '.$moduleclass_sfx : ''; ?>">
	<form class="otm-module__form" action="<?php echo JRoute::_('index.php'); ?>" method="post" data-error="<?php echo htmlspecialchars(JText::_('MOD_OTTOMO_ERROR'), ENT_QUOTES, 'UTF-8'); ?>" data-topics-error="<?php echo htmlspecialchars(JText::_('MOD_OTTOMO_CHOOSE_AT_LEAST_ONE_TOPIC'), ENT_QUOTES, 'UTF-8'); ?>">
		<?php if ($pretext !== ''): ?>
		<div class="otm-module__pretext"><?php echo $pretext; ?></div>
		<?php endif; ?>

		<?php foreach (array_merge($essentialFields, $profileFields) as $field): ?>
		<div class="otm-module__field otm-module__field--<?php echo $field->name; ?>">
			<label for="<?php echo $field->id; ?>"><?php echo $field->label; ?><?php if ($field->required): ?> <span class="otm-module__required" aria-hidden="true">*</span><?php endif; ?></label>
			<?php echo modOttomoHelper::input($field, 'inputbox form-control'); ?>
		</div>
		<?php endforeach; ?>

		<?php if ($topics->mode): ?>
		<fieldset class="otm-module__topics">
			<legend><?php echo JText::_($topics->mode === 'single' ? 'MOD_OTTOMO_CHOOSE_A_TOPIC' : 'MOD_OTTOMO_CHOOSE_TOPICS'); ?></legend>
			<?php echo modOttomoHelper::topicChoices($topics, $module, 'otm-module__topic'); ?>
		</fieldset>
		<?php else: ?>
		<?php echo modOttomoHelper::hiddenTopics($topics); ?>
		<?php endif; ?>

		<?php echo $honeypot; ?>
		<?php if ($captcha !== ''): ?>
		<div class="otm-module__captcha"><?php echo $captcha; ?></div>
		<?php endif; ?>

		<div class="otm-module__actions">
			<button class="otm-module__submit btn btn-primary" type="submit"><?php echo JText::_('MOD_OTTOMO_SUBSCRIBE'); ?></button>
		</div>
		<div class="otm-module__message" role="status" aria-live="polite"></div>

		<?php echo modOttomoHelper::hiddenFields($module); ?>
	</form>
</div>
