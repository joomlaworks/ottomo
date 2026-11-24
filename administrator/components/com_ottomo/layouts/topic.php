<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
?>
<div id="otm-admin-container" class="otm-admin-list otm-admin-view-<?php echo $this->view; ?>">

    <div class="otm-settings otm-generic-form otm-user-form">
        <div class="otm-setting-field otm-generic-group">
            <h2><?php echo Text::_('COM_OTTOMO_TOPIC_DETAILS'); ?></h2>

            <?php if ($this->summary): $summary = $this->summary; $base = 'index.php?option=com_ottomo&view=subscribers&search=&limitstart=0&topic='.(int)$this->row->id; ?>
            <ul class="otm-topic-summary" data-role="topic-summary">
                <li><a href="<?php echo Route::_($base.'&status=1'); ?>"><strong data-role="topic-active"><?php echo number_format($summary->active); ?></strong><span><?php echo Text::_('COM_OTTOMO_ACTIVE_SUBSCRIBERS'); ?></span></a></li>
                <li><a href="<?php echo Route::_($base.'&status=0'); ?>"><strong><?php echo number_format($summary->pending); ?></strong><span><?php echo Text::_('COM_OTTOMO_STATUS_PENDING'); ?></span></a></li>
                <li><a href="<?php echo Route::_($base.'&status=2'); ?>"><strong><?php echo number_format($summary->unsubscribed); ?></strong><span><?php echo Text::_('COM_OTTOMO_UNSUBSCRIBED'); ?></span></a></li>
                <li><a href="<?php echo Route::_('index.php?option=com_ottomo&view=newsletters&search=&published=-1&limitstart=0&topic='.(int)$this->row->id); ?>"><strong><?php echo number_format($summary->campaigns); ?></strong><span><?php echo Text::_('COM_OTTOMO_NEWSLETTERS'); ?></span></a></li>
            </ul>
            <?php endif; ?>

            <form action="index.php" method="post" name="adminForm" id="adminForm" data-otm-required="name" data-otm-required-message="<?php echo htmlspecialchars(Text::_('COM_OTTOMO_TOPIC_MUST_HAVE_A_NAME'), ENT_QUOTES, 'UTF-8'); ?>">
                <div class="otm-setting-single">
                    <label for="name"><?php echo Text::_('COM_OTTOMO_NAME'); ?></label>
                    <input type="text" name="name" id="name" value="<?php echo $this->row->name; ?>" />
                </div>
                <div class="otm-setting-single">
                    <label><?php echo Text::_('COM_OTTOMO_PUBLISHED'); ?></label>
                    
                    <?php // echo $this->published;?>

                    <div class="btn-group-yesno">
                        <input class="visually-hidden" type="radio" name="published" id="published1" value="1" <?php if ($this->row->published) {
                            echo 'checked="checked"';
                        } ?>>
                        <label for="published1"><?php echo Text::_('COM_OTTOMO_YES'); ?></label>
                        
                        <input class="visually-hidden" type="radio" name="published" id="published0" value="0" <?php if (!$this->row->published) {
                            echo 'checked="checked"';
                        } ?>>
                        <label for="published0"><?php echo Text::_('COM_OTTOMO_NO'); ?></label>
                    </div>
                    <p class="otm-field__hint"><?php echo Text::_('COM_OTTOMO_TOPIC_PUBLISHED_DESC'); ?></p>
                </div>
                <div class="otm-setting-single">
                    <label><?php echo Text::_('COM_OTTOMO_PRIVATE'); ?></label>
                    <input type="hidden" name="private" value="0" />
                    <label class="otm-check" for="otm-topic-private"><input type="checkbox" name="private" id="otm-topic-private" value="1"<?php echo !empty($this->row->private) ? ' checked' : ''; ?> /> <span><?php echo Text::_('COM_OTTOMO_TOPIC_PRIVATE_LABEL'); ?></span></label>
                    <p class="otm-field__hint"><?php echo Text::_('COM_OTTOMO_TOPIC_PRIVATE_DESC'); ?></p>
                </div>
                <input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
                <input type="hidden" name="option" value="<?php echo $this->option; ?>" />
                <input type="hidden" name="view" value="<?php echo $this->view; ?>" />
                <input type="hidden" name="task" value="<?php echo $this->task; ?>" />
                <input type="hidden" name="tmpl" value="<?php echo $this->tmpl; ?>" />
                <?php echo HTMLHelper::_('form.token'); ?>
            </form>
        </div>
    </div>
</div>