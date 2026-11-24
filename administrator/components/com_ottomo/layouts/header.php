<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

use Joomla\CMS\Date\Date;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
?>
<?php $logo = function ($class = '') { ?>
						<img class="otm-logo--light<?php echo $class; ?>" src="<?php echo Uri::base(true); ?>/components/com_ottomo/static/images/logo/ottomo-logo-light.svg" width="97" height="36" alt="Ottomo" />
						<img class="otm-logo--dark<?php echo $class; ?>" src="<?php echo Uri::base(true); ?>/components/com_ottomo/static/images/logo/ottomo-logo-dark.svg" width="97" height="36" alt="" />
<?php }; ?>
<div class="otm-menubar">
	<button type="button" id="otm-navtoggle" class="otm-navtoggle" aria-controls="otm-sidebar" aria-expanded="false" aria-label="<?php echo htmlspecialchars(Text::_('COM_OTTOMO_TOGGLE_MENU'), ENT_QUOTES, 'UTF-8'); ?>"><?php echo OttomoHelperHTML::icon('list'); ?></button>
	<a class="otm-menubar__logo" href="index.php?option=com_ottomo&amp;view=dashboard" aria-label="Ottomo"><?php $logo(); ?></a>
</div>
<div class="otm-header" id="otm-sidebar">
	<nav class="otm-main-navigation" aria-label="<?php echo Text::_('COM_OTTOMO'); ?>">
		<div class="otm-navinner">
			<div class="otm-brandbar">
				<h1 class="otm-logo">
					<a href="index.php?option=com_ottomo&amp;view=dashboard" aria-label="Ottomo">
<?php $logo(); ?>
					</a>
				</h1>

				<button type="button" class="otm-navclose" data-otm-navclose aria-label="<?php echo htmlspecialchars(Text::_('COM_OTTOMO_CLOSE'), ENT_QUOTES, 'UTF-8'); ?>"><?php echo OttomoHelperHTML::icon('x-lg'); ?></button>
			</div>

			<ul class="otm-main-menu" id="otm-main-menu">
				<li>
					<a href="index.php?option=com_ottomo&amp;view=dashboard"<?php if ($this->view == 'dashboard') {
					    echo ' class="active"';
					}?>>
						<?php echo OttomoHelperHTML::icon('speedometer2'); ?>
						<span><?php echo Text::_('COM_OTTOMO_DASHBOARD'); ?></span>
					</a>
				</li>
				<li>
					<a href="index.php?option=com_ottomo&amp;view=newsletters"<?php if ($this->view == 'newsletters') {
					    echo ' class="active"';
					}?>>
						<?php echo OttomoHelperHTML::icon('envelope'); ?>
						<span><?php echo Text::_('COM_OTTOMO_NEWSLETTERS'); ?></span>
					</a>
				</li>
				<li>
					<a href="index.php?option=com_ottomo&amp;view=topics"<?php if ($this->view == 'topics') {
					    echo ' class="active"';
					}?>>
						<?php echo OttomoHelperHTML::icon('tags'); ?>
						<span><?php echo Text::_('COM_OTTOMO_TOPICS'); ?></span>
					</a>
				</li>
				<li>
					<a href="index.php?option=com_ottomo&amp;view=subscribers"<?php if ($this->view == 'subscribers') {
					    echo ' class="active"';
					}?>>
						<?php echo OttomoHelperHTML::icon('people'); ?>
						<span><?php echo Text::_('COM_OTTOMO_SUBSCRIBERS'); ?></span>
					</a>
				</li>
				<li>
					<a href="index.php?option=com_ottomo&amp;view=templates"<?php if ($this->view == 'templates') {
					    echo ' class="active"';
					}?>>
						<?php echo OttomoHelperHTML::icon('palette'); ?>
						<span><?php echo Text::_('COM_OTTOMO_TEMPLATES'); ?></span>
					</a>
				</li>
				<li class="otm-menu-divider" aria-hidden="true"></li>
				<li>
					<a href="index.php?option=com_ottomo&amp;view=settings&amp;task=edit"<?php if ($this->view == 'settings') {
					    echo ' class="active"';
					}?>>
						<?php echo OttomoHelperHTML::icon('gear'); ?>
						<span><?php echo Text::_('COM_OTTOMO_SETTINGS'); ?></span>
					</a>
				</li>
			</ul>
		</div>
	</nav>
	<?php
	// Version, release date (the installed manifest) and copyright
	$released = OttomoHelperHTML::releaseDate();
	$date = $released ? new Date($released) : null;
	?>
	<p class="otm-credits">
		<span><a href="https://ottomo.joomlaworks.net" target="_blank" rel="noopener noreferrer">Ottomo</a> v<?php echo htmlspecialchars(OttomoHelperHTML::version(), ENT_QUOTES, 'UTF-8'); ?><?php echo $date ? ' ('.htmlspecialchars($date->format('d-M-Y'), ENT_QUOTES, 'UTF-8').')' : ''; ?></span>
		<span>&copy; 2006 - <?php echo $date ? $date->format('Y') : date('Y'); ?> <a href="https://www.joomlaworks.net" target="_blank" rel="noopener noreferrer">JoomlaWorks Ltd.</a></span>
	</p>
</div>
