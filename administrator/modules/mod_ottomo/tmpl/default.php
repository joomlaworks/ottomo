<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;
$e = function ($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); };
?>
<div class="otm-stats-module">
    <div class="otm-stats-module__hero">
        <a class="otm-stats-module__brand" href="index.php?option=com_ottomo&amp;view=dashboard">
            <img class="otm-stats-module__logo--light" src="<?php echo $base; ?>/static/images/logo/ottomo-logo-light.svg" width="108" height="40" alt="Ottomo" />
            <img class="otm-stats-module__logo--dark" src="<?php echo $base; ?>/static/images/logo/ottomo-logo-dark.svg" width="108" height="40" alt="" />
            <p class="otm-stats-module__tagline"><?php echo JText::_('MOD_OTTOMO_STATS_TAGLINE'); ?></p>
        </a>
        <div class="otm-stats-module__actions">
            <a href="index.php?option=com_ottomo&amp;view=dashboard"><?php echo OttomoHelperHTML::icon('speedometer2'); ?><span><?php echo JText::_('MOD_OTTOMO_STATS_OPEN'); ?></span></a>
            <a class="is-primary" href="index.php?option=com_ottomo&amp;view=newsletters&amp;task=edit"><?php echo OttomoHelperHTML::icon('plus-lg'); ?><span><?php echo JText::_('COM_OTTOMO_NEW_CAMPAIGN'); ?></span></a>
        </div>
    </div>

    <ul class="otm-stats-module__totals">
        <li class="otm-stats-module__total">
            <span class="otm-stats-module__icon"><?php echo OttomoHelperHTML::icon('envelope'); ?></span>
            <span class="otm-stats-module__total-body">
                <span class="otm-stats-module__total-value"><a href="index.php?option=com_ottomo&amp;view=newsletters"><?php echo number_format($numOfNewsletters); ?></a></span>
                <span class="otm-stats-module__total-label"><?php echo JText::_('COM_OTTOMO_NEWSLETTERS'); ?></span>
            </span>
        </li>
        <li class="otm-stats-module__total">
            <span class="otm-stats-module__icon"><?php echo OttomoHelperHTML::icon('people'); ?></span>
            <span class="otm-stats-module__total-body">
                <span class="otm-stats-module__total-value"><a href="index.php?option=com_ottomo&amp;view=subscribers"><?php echo number_format($numOfSubscribers); ?></a></span>
                <span class="otm-stats-module__total-label"><?php echo JText::_('COM_OTTOMO_SUBSCRIBERS'); ?></span>
            </span>
        </li>
        <li class="otm-stats-module__total otm-stats-module__total--last">
            <span class="otm-stats-module__icon"><?php echo OttomoHelperHTML::icon('send'); ?></span>
            <span class="otm-stats-module__total-body">
                <?php if ($lastSent): ?>
                <span class="otm-stats-module__total-value"><a href="index.php?option=com_ottomo&amp;view=newsletters&amp;task=edit&amp;id=<?php echo (int)$lastSent->id; ?>" title="<?php echo $e($lastSent->subject); ?>"><?php echo $e($lastSent->subject); ?></a></span>
                <span class="otm-stats-module__total-label"><?php echo JText::sprintf('MOD_OTTOMO_STATS_LAST_SENT', JHtml::_('date', $lastSent->sent, JText::_('COM_OTTOMO_DATE_FORMAT'), 'UTC')); ?> · <span data-role="last-sent-recipients"><?php echo JText::plural('MOD_OTTOMO_STATS_N_RECIPIENTS', (int)$lastSent->stats->recipients); ?></span></span>
                <?php else: ?>
                <span class="otm-stats-module__total-value"><?php echo JText::_('COM_OTTOMO_NEVER'); ?></span>
                <span class="otm-stats-module__total-label"><?php echo JText::_('MOD_OTTOMO_STATS_NOTHING_SENT'); ?></span>
                <?php endif; ?>
            </span>
        </li>
    </ul>

    <?php echo $chart; ?>
</div>
