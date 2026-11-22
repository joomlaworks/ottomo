<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

function OttomoBuildRoute(&$query)
{
    $segments = array();
    if (isset($query['view'])) {
        $view = $query['view'];
        $segments[] = $view;
        unset($query['view']);
    }
    if (isset($query['task'])) {
        $task = $query['task'];
        $segments[] = $task;
        unset($query['task']);
    }
    if (isset($query['id'])) {
        $id = $query['id'];
        $segments[] = $id;
        unset($query['id']);
    }
    if (isset($query['hash'])) {
        $hash = $query['hash'];
        $segments[] = $hash;
        unset($query['hash']);
    }
    if (isset($query['newsletter'])) {
        $newsletter = $query['newsletter'];
        $segments[] = $newsletter;
        unset($query['newsletter']);
    }
    if (isset($query['subscriber'])) {
        $subscriber = $query['subscriber'];
        $segments[] = $subscriber;
        unset($query['subscriber']);
    }
    if (isset($query['topics'])) {
        $topics = $query['topics'];
        $segments[] = $topics;
        unset($query['topics']);
    }
    if (isset($query['type'])) {
        $type = $query['type'];
        $segments[] = $type;
        unset($query['type']);
    }
    if (isset($query['redirect'])) {
        $redirect = $query['redirect'];
        $segments[] = $redirect;
        unset($query['redirect']);
    }
    return $segments;
}

/**
 * Joomla 4+ expects the segments to be used up (left over segments are a 404); Joomla turns the first "-" of each
 * segment into ":" (Ottomo's values never contain ":", but tracking redirects are URL-safe base64, with "-").
 */
function OttomoParseRoute(&$segments)
{
    $values = array();
    foreach ($segments as $segment) {
        $values[] = str_replace(':', '-', (string)$segment);
    }
    $segments = array();
    return ottomoParseSegments($values);
}

function ottomoParseSegments($segments)
{
    $vars = array();
    if ($segments[0] == 'subscribers') {
        $vars['view'] = $segments[0];
        $vars['id'] = isset($segments[1]) ? $segments[1] : '';
        $vars['hash'] = isset($segments[2]) ? $segments[2] : '';
        $vars['newsletter'] = isset($segments[3]) ? $segments[3] : '';
        return $vars;
    } elseif ($segments[0] == 'newsletters') {
        $vars['view'] = $segments[0];
        $vars['id'] = $segments[1];
        if (isset($segments[2])) {
            $vars['hash'] = $segments[2];
        }
        if (isset($segments[3])) {
            $vars['subscriber'] = $segments[3];
        }
        return $vars;
    } elseif ($segments[0] == 'archive') {
        $vars['view'] = $segments[0];
        return $vars;
    } elseif ($segments[0] == 'subscribers.activate') {
        $vars['task'] = $segments[0];
        $vars['hash'] = $segments[1];
        return $vars;
    } elseif ($segments[0] == 'subscribers.unsubscribe') {
        $vars['task'] = $segments[0];
        $vars['id'] = isset($segments[1]) ? $segments[1] : '';
        $vars['hash'] = isset($segments[2]) ? $segments[2] : '';
        $vars['newsletter'] = isset($segments[3]) ? $segments[3] : '';
        $vars['topics'] = isset($segments[4]) ? $segments[4] : '';
        return $vars;
    } elseif ($segments[0] == 'newsletters.track') {
        $vars['task'] = $segments[0];
        $vars['id'] = isset($segments[1]) ? $segments[1] : '';
        $vars['hash'] = isset($segments[2]) ? $segments[2] : '';
        $vars['subscriber'] = isset($segments[3]) ? $segments[3] : '';
        $vars['type'] = isset($segments[4]) ? $segments[4] : '';
        $vars['redirect'] = isset($segments[5]) ? $segments[5] : '';
        return $vars;
    }
    $vars['view'] = $segments[0];
    if (isset($segments[1])) {
        $vars['task'] = $segments[1];
    }
    if ($vars['view'] == 'subscribers' && isset($vars['task']) && $vars['task'] == 'activate') {
        if (isset($segments[2])) {
            $vars['hash'] = $segments[2];
        }
        return $vars;
    }
    if (isset($segments[2])) {
        $vars['id'] = $segments[2];
    }
    if (isset($segments[3])) {
        $vars['hash'] = $segments[3];
    }
    if ($vars['view'] == 'newsletters' && isset($vars['task']) && $vars['task'] == 'track') {
        if (isset($segments[4])) {
            $vars['subscriber'] = $segments[4];
        }
        if (isset($segments[5])) {
            $vars['type'] = $segments[5];
        }
        if (isset($segments[6])) {
            $vars['redirect'] = $segments[6];
        }
        return $vars;
    }
    if (isset($segments[4])) {
        $vars['newsletter'] = $segments[4];
    }
    if (isset($segments[5])) {
        $vars['topics'] = $segments[5];
    }
    return $vars;
}
