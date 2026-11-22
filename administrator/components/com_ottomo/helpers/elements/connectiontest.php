<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;


/** "Test connection" button (js/ottomo.js -> setupConnectionTest). Tests the saved settings. */
class JFormFieldConnectionTest extends FormField
{
    public $type = 'connectiontest';

    public function getInput()
    {
        return '<div class="otm-connection-test" data-connection-test data-token="'.Session::getFormToken().'">'
            .'<button type="button" class="otm-btn otm-btn--generic" data-action="test-connection">'.Text::_('COM_OTTOMO_CONNECTION_TEST_BUTTON').'</button>'
            .'<span class="otm-connection-test__hint">'.Text::_('COM_OTTOMO_CONNECTION_TEST_HINT').'</span>'
            .'<div class="otm-status" data-role="result" role="status" aria-live="polite" hidden></div>'
            .'</div>';
    }
}
