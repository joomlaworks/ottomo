<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;
?>
<div class="otm-header">
	<nav class="otm-main-navigation" aria-label="<?php echo JText::_('COM_OTTOMO'); ?>">
		<div class="otm-navinner">
			<div class="otm-brandbar">
				<h1 class="otm-logo">
					<a href="index.php?option=com_ottomo&amp;view=dashboard" aria-label="Ottomo">
						<img class="otm-logo--light" src="<?php echo JURI::base(true); ?>/components/com_ottomo/static/images/logo/ottomo-logo-light.svg" width="97" height="36" alt="Ottomo" />
						<img class="otm-logo--dark" src="<?php echo JURI::base(true); ?>/components/com_ottomo/static/images/logo/ottomo-logo-dark.svg" width="97" height="36" alt="" />
					</a>
				</h1>

				<button type="button" id="otm-navtoggle" class="otm-navtoggle" aria-controls="otm-main-menu" aria-expanded="false" aria-label="<?php echo htmlspecialchars(JText::_('COM_OTTOMO_TOGGLE_MENU'), ENT_QUOTES, 'UTF-8'); ?>"><?php echo OttomoHelperHTML::icon('list'); ?></button>
			</div>

			<ul class="otm-main-menu" id="otm-main-menu">
				<li>
					<a href="index.php?option=com_ottomo&amp;view=dashboard"<?php if ($this->view == 'dashboard') {
					    echo ' class="active"';
					}?>>
						<?php echo OttomoHelperHTML::icon('speedometer2'); ?>
						<span><?php echo JText::_('COM_OTTOMO_DASHBOARD'); ?></span>
					</a>
				</li>
				<li>
					<a href="index.php?option=com_ottomo&amp;view=newsletters"<?php if ($this->view == 'newsletters') {
					    echo ' class="active"';
					}?>>
						<?php echo OttomoHelperHTML::icon('envelope'); ?>
						<span><?php echo JText::_('COM_OTTOMO_NEWSLETTERS'); ?></span>
					</a>
				</li>
				<li>
					<a href="index.php?option=com_ottomo&amp;view=topics"<?php if ($this->view == 'topics') {
					    echo ' class="active"';
					}?>>
						<?php echo OttomoHelperHTML::icon('tags'); ?>
						<span><?php echo JText::_('COM_OTTOMO_TOPICS'); ?></span>
					</a>
				</li>
				<li>
					<a href="index.php?option=com_ottomo&amp;view=subscribers"<?php if ($this->view == 'subscribers') {
					    echo ' class="active"';
					}?>>
						<?php echo OttomoHelperHTML::icon('people'); ?>
						<span><?php echo JText::_('COM_OTTOMO_SUBSCRIBERS'); ?></span>
					</a>
				</li>
				<li>
					<a href="index.php?option=com_ottomo&amp;view=templates"<?php if ($this->view == 'templates') {
					    echo ' class="active"';
					}?>>
						<?php echo OttomoHelperHTML::icon('palette'); ?>
						<span><?php echo JText::_('COM_OTTOMO_TEMPLATES'); ?></span>
					</a>
				</li>
				<li class="otm-menu-divider" aria-hidden="true"></li>
				<li>
					<a href="index.php?option=com_ottomo&amp;view=settings&amp;task=edit"<?php if ($this->view == 'settings') {
					    echo ' class="active"';
					}?>>
						<?php echo OttomoHelperHTML::icon('gear'); ?>
						<span><?php echo JText::_('COM_OTTOMO_SETTINGS'); ?></span>
					</a>
				</li>
			</ul>
		</div>
	</nav>
</div>
