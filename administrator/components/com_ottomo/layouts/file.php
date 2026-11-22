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
<div id="otm-admin-container" class="otm-tmpleditor otm-admin-list otm-admin-view-<?php echo $this->view; ?>">
	<div class="otm-tmpleditor-details otm-generic-form">
		<div class="otm-tmpleditor-banner">
			<div>
				<span class="otm-tmpleditor-label"><?php echo JText::_('COM_OTTOMO_TEMPLATE'); ?></span>
				<h2><?php echo htmlspecialchars($this->row->name, ENT_QUOTES, 'UTF-8'); ?></h2>
			</div>
			<a class="otm-btn otm-btn--generic" href="index.php?option=com_ottomo&amp;view=templates&amp;task=edit&amp;id=<?php echo (int) $this->row->id; ?>">
				<?php echo JText::_('COM_OTTOMO_TEMPLATE_DETAILS'); ?>
			</a>
		</div>
	</div>

	<form action="index.php" method="post" name="adminForm" id="adminForm" data-otm-source="contents">
		<div class="otm-tmpleditor-workspace">
			<div class="otm-tmpleditor-files">
				<h3><?php echo JText::_('COM_OTTOMO_SELECT_A_FILE_TO_EDIT'); ?></h3>
				<ul class="otm-tmpleditor-menu">
					<?php foreach ($this->files as $file): ?>
					<li>
						<a href="<?php echo $file->link; ?>"<?php if ($file->path === $this->file->path) {
						    echo ' class="active"';
						} ?>><?php echo $file->path; ?></a>
					</li>
					<?php endforeach; ?>
				</ul>
			</div>

			<div class="otm-tmpleditor-pane otm-settings">
				<h3><?php echo JText::_('COM_OTTOMO_EDITING_FILE'); ?>: <code><?php echo htmlspecialchars($this->file->path, ENT_QUOTES, 'UTF-8'); ?></code></h3>
				<div id="otm-source-editor"><?php echo $this->file->contents; ?></div>
			</div>
		</div>

		<input type="hidden" name="template" value="<?php echo $this->row->id; ?>" />
		<input type="hidden" name="file" value="<?php echo base64_encode($this->file->path); ?>" />
		<input type="hidden" name="contents" value="<?php echo $this->file->contents; ?>" />
        <input type="hidden" name="option" value="<?php echo $this->option; ?>" />
        <input type="hidden" name="view" value="<?php echo $this->view; ?>" />
        <input type="hidden" name="task" value="<?php echo $this->task; ?>" />
        <input type="hidden" name="tmpl" value="<?php echo $this->tmpl; ?>" />
        <?php echo JHTML::_('form.token'); ?>
    </form>
</div>
