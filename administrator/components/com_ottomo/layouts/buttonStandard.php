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
<?php
// Toolbar icons (Bootstrap Icons, inline SVG)
$icons = array(' fa-plus' => 'plus-lg', ' fa-upload' => 'upload', ' fa-download' => 'download', ' fa-export-all' => 'cloud-arrow-down', ' fa-trash-unsubscribed' => 'trash');
$icon = isset($icons[$iconClass]) ? $icons[$iconClass] : '';
?>
<button type="button" onclick="<?php echo $onclick; ?>" class="otm-btn<?php echo $icon ? ' otm-btn--icon' : ''; ?><?php echo $buttonClass; ?>" data-task="<?php echo htmlspecialchars($task, ENT_QUOTES, 'UTF-8'); ?>">
	<?php if ($icon): ?><?php echo OttomoHelperHTML::icon($icon); ?><?php endif; ?>
	<span><?php echo $text; ?></span>
</button>
