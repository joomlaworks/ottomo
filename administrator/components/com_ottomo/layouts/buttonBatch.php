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
$iconMap = array(
    'fa-batch' => 'layers',
    'fa-copy' => 'files',
    'fa-trash-o' => 'trash',
    'fa-check-circle-o' => 'check-circle',
    'fa-circle-o' => 'slash-circle',
);
$iconKey = trim($iconClass);
$bsIcon = isset($iconMap[$iconKey]) ? $iconMap[$iconKey] : 'layers';
?>
<button onclick="<?php echo $onclick; ?>">
	<?php echo OttomoHelperHTML::icon($bsIcon); ?>
	<span><?php echo $text; ?></span>
</button>