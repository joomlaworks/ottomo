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
/*
 * A button (the designer's "+" menu > Button). $row->text and $row->link are already escaped.
 * Keep this shape: an outer table (100% wide; its cell's align places the button), a one-cell table whose cell has the
 * background and corners, and a padded inline-block link with the text colour. The designer's button bar edits these
 * (alignment, full width, colours, corners), and Ottomo makes the cell clickable in classic Outlook when sending.
 */
?>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin: 0 0 24px;"><tr><td align="left"><table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr><td style="border-radius: 0px; background: #000000;"><a href="<?php echo $row->link; ?>" style="display: inline-block; padding: 12px 24px; font-family: Helvetica, Arial, sans-serif; font-size: 15px; font-weight: bold; line-height: 20px; color: #ffffff; text-decoration: none; border-radius: 0px;"><?php echo $row->text; ?></a></td></tr></table></td></tr></table>
