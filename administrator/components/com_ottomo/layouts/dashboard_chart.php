<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

// Statistics widget, shared by the component dashboard and the control panel module (mod_ottomo (administrator)).
// Styles: css/ottomo-graphs.css (self-contained, .otm-stats). Behaviour: js/ottomo.js (Statistics).
defined('_JEXEC') or die ;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
$ranges = array('day' => 'COM_OTTOMO_DAY', 'week' => 'COM_OTTOMO_WEEK', 'month' => 'COM_OTTOMO_MONTH');
?>
<div class="otm-stats">
    <form name="otm-statistics-form" id="otm-statistics-form" class="otm-stats__form" action="<?php echo Route::_('index.php'); ?>" method="get">

        <?php if ($this->params->get('source') != 'ses'): ?>
        <div class="otm-stats__toolbar">
            <div class="otm-stats__topic">
                <label class="otm-stats__sr" for="topicId"><?php echo Text::_('COM_OTTOMO_TOPIC'); ?></label>
                <?php echo $this->topicsFilter; ?>
            </div>
            <div class="otm-stats__dates">
                <span class="otm-stats__date">
                    <label for="startDate"><?php echo Text::_('COM_OTTOMO_DATE_FROM'); ?></label>
                    <input type="date" name="startDate" id="startDate" value="" />
                </span>
                <span class="otm-stats__date">
                    <label for="endDate"><?php echo Text::_('COM_OTTOMO_DATE_TO'); ?></label>
                    <input type="date" name="endDate" id="endDate" value="" />
                </span>
                <button type="button" class="otm-stats__apply" id="otm-statistics-form-apply"><?php echo Text::_('COM_OTTOMO_APPLY'); ?></button>
            </div>
        </div>

        <div class="otm-stats__kpis" data-role="stats-primary" aria-live="polite"></div>
        <div class="otm-stats__secondary" data-role="stats-secondary" hidden></div>

        <div class="otm-stats__ranges" role="group" aria-label="<?php echo htmlspecialchars(Text::_('COM_OTTOMO_PERIOD'), ENT_QUOTES, 'UTF-8'); ?>">
            <?php foreach ($ranges as $range => $label): ?>
            <button type="button" class="otm-statistics-range<?php echo $range == 'month' ? ' otm-active' : ''; ?>" data-range="<?php echo $range; ?>" aria-pressed="<?php echo $range == 'month' ? 'true' : 'false'; ?>"><?php echo Text::_($label); ?></button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <input type="hidden" name="option" value="com_ottomo" />
        <input type="hidden" name="task" value="newsletters.statistics" />
        <input type="hidden" name="range" value="30" />
        <input type="hidden" name="format" value="json" />
        <input type="hidden" name="source" value="<?php echo htmlspecialchars((string)$this->params->get('source'), ENT_QUOTES, 'UTF-8'); ?>" />
    </form>

    <div id="otm-chart" class="otm-stats__chart"></div>
</div>
