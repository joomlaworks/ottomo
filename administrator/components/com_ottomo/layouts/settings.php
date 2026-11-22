<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ; ?>
<?php
// Section icons (Bootstrap Icons), menu and headings
$sectionIcons = array('general' => 'send', 'services' => 'cloud', 'compliance' => 'shield-check', 'signup' => 'person-plus', 'campaigns' => 'envelope', 'dashboard' => 'speedometer2', 'layout' => 'file-earmark-text', 'permissions' => 'people');
$sectionIcon = function ($name) use ($sectionIcons) {
    return OttomoHelperHTML::icon(isset($sectionIcons[$name]) ? $sectionIcons[$name] : 'gear');
};
?>
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm" id="adminForm">
	
	<nav class="otm-settings__nav" aria-label="<?php echo htmlspecialchars(JText::_('COM_OTTOMO_SETTINGS'), ENT_QUOTES, 'UTF-8'); ?>">
		<h2 class="otm-settings__title"><?php echo OttomoHelperHTML::icon('gear'); ?><?php echo JText::_('COM_OTTOMO_SETTINGS'); ?></h2>
		<ul class="otm-settings__menu">
			<?php foreach ($this->form->getFieldsets() as $name => $fieldset): ?>
			<li>
				<a class="otm-settings__link otm-settings__link--primary" href="#<?php echo $name; ?>"><?php echo $sectionIcon($name); ?><?php echo JText::_($fieldset->label); ?></a>
				<?php $i = 0;
			    foreach ($this->form->getFieldset($name) as $field): ?>
				<?php if ($field->type == 'Header'): ?>
				<a class="otm-settings__link" href="#<?php echo $name; ?>-<?php echo $i; ?>"><?php echo $field->input; ?></a>	
				<?php endif; ?>
				<?php $i++; endforeach; ?>
			</li>
			<?php endforeach; ?>
			<li><a class="otm-settings__link otm-settings__link--primary" href="#third-party"><?php echo OttomoHelperHTML::icon('info-circle'); ?><?php echo JText::_('COM_OTTOMO_THIRD_PARTY'); ?></a></li>
		</ul>
	</nav>

	<div class="otm-settings__main">
	<?php foreach ($this->form->getFieldsets() as $name => $fieldset): ?>
	<div class="otm-settings otm-settings--global">
		<div class="otm-setting-field">
			<h3 id="<?php echo $name; ?>" class="otm-settings__heading"><?php echo $sectionIcon($name); ?><?php echo JText::_($fieldset->label); ?></h3>
			
			<?php if (isset($fieldset->description) && !empty($fieldset->description)) : ?>
			<p><?php echo JText::_($fieldset->description); ?></p>
			<?php endif; ?>
		
			<?php $i = 0;
	    foreach ($this->form->getFieldset($name) as $field): ?>
			<?php if ($field->type == 'Header'): ?>
			<div class="otm-settings__subheading" id="<?php echo $name; ?>-<?php echo $i; ?>"><?php echo $field->input; ?></div>
			<?php elseif ($field->type == 'Rules'): ?>
			<div class="otm-setting-rules"><?php echo $field->input; ?></div>
			<?php else: ?>
			<div class="otm-setting-single">
				<?php echo $field->label; ?>
				<?php echo $field->input; ?>
			</div>
			<?php endif; ?>
			<?php $i++; endforeach; ?>

		</div>
	</div>
	<?php endforeach; ?>

	<?php
	// The third-party software Ottomo ships or loads: name, version, link, use, license
	$thirdParty = array(
		array('Ace', '1.45.2', 'https://ace.c9.io', 'COM_OTTOMO_THIRD_PARTY_ACE', 'BSD', 'https://github.com/ajaxorg/ace/blob/master/LICENSE'),
		array('Bootstrap Icons', '', 'https://icons.getbootstrap.com', 'COM_OTTOMO_THIRD_PARTY_ICONS', 'MIT', 'https://github.com/twbs/icons/blob/main/LICENSE'),
		array('SuperModal.js', '1.0.0', 'https://github.com/fevangelou/supermodal.js', 'COM_OTTOMO_THIRD_PARTY_SUPERMODAL', 'MIT', 'https://github.com/fevangelou/supermodal.js'),
		array('Unsplash', '', 'https://unsplash.com', 'COM_OTTOMO_THIRD_PARTY_UNSPLASH', 'Unsplash License', 'https://unsplash.com/license'),
	);
	?>
	<div class="otm-settings otm-settings--global">
		<div class="otm-setting-field">
			<h3 id="third-party" class="otm-settings__heading"><?php echo OttomoHelperHTML::icon('info-circle'); ?><?php echo JText::_('COM_OTTOMO_THIRD_PARTY'); ?></h3>
			<p><?php echo JText::_('COM_OTTOMO_THIRD_PARTY_DESC'); ?></p>
			<ul class="otm-third-party">
				<?php foreach ($thirdParty as $item): list($name, $version, $url, $use, $license, $licenseUrl) = $item; ?>
				<li>
					<span class="otm-third-party__name"><a href="<?php echo $url; ?>" target="_blank" rel="noopener"><?php echo $name; ?></a><?php if ($version): ?> <span class="otm-third-party__version"><?php echo $version; ?></span><?php endif; ?></span>
					<span class="otm-third-party__use"><?php echo JText::_($use); ?></span>
					<a class="otm-third-party__license" href="<?php echo $licenseUrl; ?>" target="_blank" rel="noopener"><?php echo $license; ?></a>
				</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
	</div>

	<input type="hidden" name="id" value="<?php echo $this->id; ?>" />
	<input type="hidden" name="component" value="com_ottomo" />
	<input type="hidden" name="option" value="com_ottomo" />
	<input type="hidden" name="view" value="settings" />
	<input type="hidden" id="task" name="task" value="" />
	<input type="hidden" name="return" value="<?php echo JFactory::getApplication()->input->get('return', '', 'base64'); ?>" />
	<?php echo JHTML::_('form.token'); ?>
</form>
