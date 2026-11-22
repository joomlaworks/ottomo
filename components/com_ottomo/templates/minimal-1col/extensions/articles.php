<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text; ?>
<?php
$image = $row->introImage ? $row->introImage : $row->fullImage;
$caption = $row->introImage ? $row->introImageCaption : $row->fullImageCaption;
$meta = array();
if ($row->category) {
    $meta[] = '<a href="'.$row->category->link.'" style="color: inherit; text-decoration: none;">'.htmlspecialchars($row->category->name, ENT_QUOTES, 'UTF-8').'</a>';
}
if ($row->date) {
    $meta[] = HTMLHelper::_('date', $row->date, Text::_('DATE_FORMAT_LC3'));
}
if ($row->author) {
    $meta[] = htmlspecialchars($row->author, ENT_QUOTES, 'UTF-8');
}
?>
<div contenteditable="true" style="margin: 0 0 32px;">
	<?php if ($image): ?>
	<a href="<?php echo $row->link; ?>"><img src="<?php echo $image; ?>" alt="<?php echo $row->safeTitle; ?>" width="520" style="display: block; width: 100%; max-width: 520px; height: auto; border: 0; border-radius: 10px; margin: 0 0 16px;" /></a>
	<?php if ($caption): ?><p style="margin: -8px 0 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif; font-size: 12px; line-height: 18px; color: #71717a;"><?php echo htmlspecialchars($caption, ENT_QUOTES, 'UTF-8'); ?></p><?php endif; ?>
	<?php endif; ?>
	<?php if (count($meta)): ?><p style="margin: 0 0 6px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif; font-size: 12px; line-height: 18px; letter-spacing: 0.04em; text-transform: uppercase; color: #71717a;"><?php echo implode(' &middot; ', $meta); ?></p><?php endif; ?>
	<?php if ($row->title): ?>
	<h3 style="margin: 0 0 10px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif; font-size: 22px; line-height: 28px; font-weight: 700; color: #18181b;"><a href="<?php echo $row->link; ?>" style="color: inherit; text-decoration: none;"><?php echo $row->safeTitle; ?></a></h3>
	<?php endif; ?>
	<?php if ($row->introtext): ?><div style="margin: 0 0 12px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif; font-size: 16px; line-height: 26px; color: #3f3f46;"><?php echo $row->introtext; ?></div><?php endif; ?>
	<?php if ($row->fulltext): ?><div style="margin: 0 0 12px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif; font-size: 16px; line-height: 26px; color: #3f3f46;"><?php echo $row->fulltext; ?></div><?php endif; ?>
	<p style="margin: 0;"><a href="<?php echo $row->link; ?>" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif; font-size: 15px; font-weight: 600; color: #18181b; text-decoration: underline;"><?php echo Text::_('COM_OTTOMO_TMPL_READ_MORE'); ?></a></p>
</div>
