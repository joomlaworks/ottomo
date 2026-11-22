<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

// Test send result: rendered inside a SuperModal frame. The controller's messages are shown here
// (and taken off the queue, so the template doesn't render them a second time).
defined('_JEXEC') or die ;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
$messages = Factory::getApplication()->getMessageQueue(true);
?>
<div class="otm-frame" data-frame="sample">
    <?php foreach ($messages as $message): ?>
    <?php $error = in_array($message['type'], array('error', 'warning'), true); ?>
    <div class="otm-frame__notice otm-frame__notice--<?php echo $error ? 'error' : 'success'; ?>" role="<?php echo $error ? 'alert' : 'status'; ?>"><?php echo htmlspecialchars($message['message'], ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endforeach; ?>
    <div class="otm-frame__actions">
        <button type="button" class="otm-btn otm-btn--save" data-frame-close><?php echo Text::_('COM_OTTOMO_CLOSE'); ?></button>
    </div>
</div>
