<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Component\ComponentHelper;

require_once JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/compat.php';

class OttomoHelperK2
{
    public static function getItemRoute($id, $catid = 0)
    {
        $needles = array('item' => (int)$id, 'category' => (int)$catid);
        $link = 'index.php?option=com_k2&view=item&id='.$id;
        if ($item = self::_findItem($needles)) {
            $link .= '&Itemid='.$item->id;
        }
        return $link;
    }

    public static function getCategoryRoute($catid)
    {
        $needles = array('category' => (int)$catid);
        $link = 'index.php?option=com_k2&view=itemlist&task=category&id='.$catid;
        if ($item = self::_findItem($needles)) {
            $link .= '&Itemid='.$item->id;
        }
        return $link;
    }

    public static function getTagRoute($tag)
    {
        $needles = array('tag' => $tag);
        $link = 'index.php?option=com_k2&view=itemlist&task=tag&tag='.urlencode($tag);
        if ($item = self::_findItem($needles)) {
            $link .= '&Itemid='.$item->id;
        } elseif (defined('K2_TAGS_ITEMID') && K2_TAGS_ITEMID) {
            $link .= '&Itemid='.K2_TAGS_ITEMID;
        }
        return $link;
    }

    public static function getUserRoute($userID)
    {
        $needles = array('user' => (int)$userID);
        $user = OttomoCompat::user($userID);
        $alias = ApplicationHelper::stringURLSafe($user->name);
        $link = 'index.php?option=com_k2&view=itemlist&task=user&id='.$userID.':'.$alias;
        if ($item = self::_findItem($needles)) {
            $link .= '&Itemid='.$item->id;
        } elseif (defined('K2_USERS_ITEMID') && K2_USERS_ITEMID) {
            $link .= '&Itemid='.K2_USERS_ITEMID;
        }
        return $link;
    }

    protected static function _findItem($needles)
    {
        $component = ComponentHelper::getComponent('com_k2');
        $application = \Joomla\CMS\Application\CMSApplication::getInstance('site');
        $menus = $application->getMenu('site');
        $items = $menus->getItems('component_id', $component->id);
        $match = null;
        foreach ($needles as $needle => $id) {
            if (is_array($items) && count($items)) {
                foreach ($items as $item) {
                    if ($needle == 'user' || $needle == 'category') {
                        if ((@$item->query['task'] == $needle) && (@$item->query['id'] == $id)) {
                            $match = $item;
                            break;
                        }
                    } elseif ($needle == 'tag') {
                        if ((@$item->query['task'] == $needle) && (@$item->query['tag'] == $id)) {
                            $match = $item;
                            break;
                        }
                    } else {
                        if ((@$item->query['view'] == $needle) && (@$item->query['id'] == $id)) {
                            $match = $item;
                            break;
                        }
                    }
                    if (!is_null($match)) {
                        break;
                    }
                }
                // Second pass [START]
                // Only for multiple categories links. Triggered only if we do not have find any match (link to direct category)
                if (is_null($match)) {
                    foreach ($items as $item) {
                        if ($needle == 'category') {
                            if (!isset($item->K2Categories)) {
                                $menuparams = json_decode($item->params);
                                $item->K2Categories = isset($menuparams->categories) ? $menuparams->categories : array();
                            }
                            if (isset($item->K2Categories) && is_array($item->K2Categories)) {
                                foreach ($item->K2Categories as $catid) {
                                    if ((@$item->query['view'] == 'itemlist') && (@$item->query['task'] == '') && (@(int)$catid == $id)) {
                                        $match = $item;
                                        break;
                                    }
                                }
                            }
                        }
                        if (!is_null($match)) {
                            break;
                        }
                    }
                }
                // Second pass [END]
            }
            if (!is_null($match)) {
                break;
            }
        }
        return $match;
    }

}
