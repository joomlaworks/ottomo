<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

// Import subscribers: rendered inside a SuperModal frame (see js/ottomo.js -> Ottomo.frames.import)
defined('_JEXEC') or die ;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
?>
<div class="otm-frame" data-frame="import" data-token="<?php echo Session::getFormToken(); ?>">
    <form action="index.php" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" class="otm-import">

        <section class="otm-import__step">
            <h3 class="otm-frame__step"><?php echo Text::_('COM_OTTOMO_STEP_ONE_SELECT_TOPICS'); ?></h3>
            <div class="otm-import__topics">
                <?php echo $this->topics; ?>
            </div>
        </section>

        <section class="otm-import__step">
            <h3 class="otm-frame__step"><?php echo Text::_('COM_OTTOMO_STEP_TWO_IMPORT_SUBSCRIBERS'); ?></h3>

            <div class="otm-import__sources">
                <div class="otm-field">
                    <label class="otm-field__label" for="otm-import-emails"><?php echo Text::_('COM_OTTOMO_EMAILS'); ?></label>
                    <span class="otm-field__hint"><?php echo Text::_('COM_OTTOMO_EMAILS_NOTE'); ?></span>
                    <textarea id="otm-import-emails" name="subscribers" rows="10"></textarea>
                </div>

                <div>
                    <div class="otm-field">
                        <label class="otm-field__label" for="otm-import-csv"><?php echo Text::_('COM_OTTOMO_UPLOAD_CSV_FILE'); ?></label>
                        <span class="otm-field__hint"><?php echo Text::_('COM_OTTOMO_UPLOAD_CSV_FILE_NOTES'); ?></span>
                        <input type="file" id="otm-import-csv" name="csv" accept=".csv,text/csv" />
                        <span class="otm-field__hint" data-role="upload-status" aria-live="polite"></span>
                        <?php
                        // Sample of the format the import reads: a header row, the email first, then the name (optional)
                        $sample = "email,name\r\nada.lovelace@example.com,Ada Lovelace\r\nalan.turing@example.com,Alan Turing\r\nsubscriber@example.com,\r\n";
                        ?>
                        <a class="otm-sample-link" data-role="csv-sample" download="ottomo-subscribers-sample.csv" href="data:text/csv;charset=utf-8,<?php echo rawurlencode($sample); ?>"><?php echo OttomoHelperHTML::icon('download'); ?><span><?php echo Text::_('COM_OTTOMO_DOWNLOAD_CSV_SAMPLE'); ?></span></a>
                    </div>
                    <div class="otm-field">
                        <span class="otm-field__label" id="otm-import-sources-label"><?php echo Text::_('COM_OTTOMO_IMPORT_SOURCES'); ?></span>
                        <span class="otm-field__hint"><?php echo Text::_('COM_OTTOMO_IMPORT_SOURCES_HINT'); ?></span>
                        <div class="otm-import-sources" role="group" aria-labelledby="otm-import-sources-label">
                            <?php foreach ($this->sources as $source): ?>
                            <label class="otm-import-source<?php echo $source->available ? '' : ' otm-import-source--missing'; ?>">
                                <input type="checkbox" name="sources[]" value="<?php echo htmlspecialchars($source->name, ENT_QUOTES, 'UTF-8'); ?>"<?php echo $source->available ? '' : ' disabled'; ?> />
                                <span class="otm-import-source__name"><?php echo htmlspecialchars($source->label, ENT_QUOTES, 'UTF-8'); ?></span>
                                <span class="otm-import-source__count"><?php echo $source->available ? number_format((int)$source->total) : Text::_('COM_OTTOMO_IMPORT_SOURCE_NOT_INSTALLED'); ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="otm-import__footer">
            <p class="otm-field__hint"><?php echo Text::_('COM_OTTOMO_IMPORT_NOTES'); ?></p>
            <div class="otm-frame__actions">
                <button type="button" class="otm-btn otm-btn--generic" data-frame-close><?php echo Text::_('COM_OTTOMO_CLOSE'); ?></button>
                <button type="submit" class="otm-btn otm-btn--save" id="otm-import-button"><?php echo OttomoHelperHTML::icon('upload'); ?> <span><?php echo Text::_('COM_OTTOMO_IMPORT'); ?></span></button>
            </div>
        </div>

        <div data-state="running" hidden>
            <p class="otm-frame__meta" data-role="label" aria-live="polite"></p>
            <div class="otm-progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="otm-progress__bar"></div></div>
        </div>
        <div class="otm-frame__notice otm-frame__notice--error" role="alert" data-role="errors" hidden></div>

        <input type="hidden" name="option" value="com_ottomo" />
        <input type="hidden" name="task" value="import.run" />
        <input type="hidden" name="request" value="" />
        <input type="hidden" name="format" value="json" />
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>
