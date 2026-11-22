<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

/**
 * The subscribers an export covers (set by the export view, kept in the session, applied by export.run):
 * mode all | filtered (search, topic, status of the Subscribers list) | selected (ids).
 */
class OttomoHelperExport
{
    public static function apply($model, $scope)
    {
        $mode = isset($scope['mode']) ? $scope['mode'] : 'all';
        if ($mode === 'selected') {
            $model->setState('ids', isset($scope['ids']) ? array_map('intval', (array)$scope['ids']) : array(0));
        } elseif ($mode === 'filtered') {
            $model->setState('search', isset($scope['search']) ? (string)$scope['search'] : '');
            $model->setState('topic', isset($scope['topic']) ? (int)$scope['topic'] : 0);
            $model->setState('status', isset($scope['status']) ? (int)$scope['status'] : -1);
        }
        return $model;
    }

    /** CSV columns: the first one is the email (what the CSV import reads), then the profile */
    public static function columns()
    {
        return array('email', 'name', 'status', 'topics', 'created', 'company', 'position', 'address', 'city', 'state', 'zip', 'country', 'phone', 'mobile', 'birthday');
    }
}
