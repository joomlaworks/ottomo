<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

class OttomoModelK2 extends OttomoModel
{
    public function getList()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select($db->quoteName('i').'.*');
        $query->select($db->quoteName('g.title').' AS '.$db->quoteName('groupname'));
        $query->select($db->quoteName('c.name').' AS '.$db->quoteName('category'));
        $query->select($db->quoteName('u.name').' AS '.$db->quoteName('author'));
        $query->from($db->quoteName('#__k2_items').' AS '. $db->quoteName('i'));
        $query->leftJoin($db->quoteName('#__k2_categories').' AS '.$db->quoteName('c').' ON '.$db->quoteName('c.id').' = '.$db->quoteName('i.catid'));
        $query->leftJoin($db->quoteName('#__users').' AS '.$db->quoteName('u').' ON '.$db->quoteName('u.id').' = '.$db->quoteName('i.created_by'));
        $query->leftJoin($db->quoteName('#__viewlevels').' AS '.$db->quoteName('g').' ON '.$db->quoteName('g.id').' = '.$db->quoteName('i.access'));
        $this->setQueryConditions($query);
        if ($this->getState('ordering') && $this->getState('orderingDir')) {
            $query->order($this->getState('ordering').' '.$this->getState('orderingDir'));
        }
        $db->setQuery($query, (int)$this->getState('limitstart'), (int)$this->getState('limit'));
        $rows = $db->loadObjectList();
        return $rows;
    }

    public function getListCount()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select('COUNT(*)');
        $query->from($db->quoteName('#__k2_items').' AS '. $db->quoteName('i'));
        $this->setQueryConditions($query);
        $db->setQuery($query);
        $result = $db->loadResult();
        return $result;
    }

    protected function setQueryConditions(&$query)
    {
        $db = $this->getDbo();
        if ($this->getState('published') > -1) {
            $query->where($db->quoteName('i.published').' = '.$this->getState('published'));
        }
        if ($this->getState('catid')) {
            $query->where($db->quoteName('i.catid').' = '.$this->getState('catid'));
        }
        if ($this->getState('author')) {
            $query->where($db->quoteName('i.created_by').' = '.$this->getState('author'));
        }
        if ($this->getState('featured') > -1) {
            $query->where($db->quoteName('i.featured').' = '.$this->getState('featured'));
        }
        if ($this->getState('trash') > -1) {
            $query->where($db->quoteName('i.trash').' = '.$this->getState('trash'));
        }
        if ($this->getState('search')) {
            $search = trim($this->getState('search'));
            if ($search) {
                $query->where($db->quoteName('i.title').' LIKE '.$db->quote('%'.$db->escape($search, true).'%', false));
            }
        }

    }

    public function getCategories($row = null, $hideTrashed = false, $hideUnpublished = true)
    {

        $db = $this->getDBO();
        if (isset($row->id)) {
            $idCheck = ' AND id != '.( int )$row->id;
        } else {
            $idCheck = null;
        }
        if (!is_null($row) && !isset($row->parent)) {
            $row->parent = 0;
        }
        $query = "SELECT m.* FROM #__k2_categories m WHERE id > 0 {$idCheck}";

        if ($hideUnpublished) {
            $query .= " AND published=1 ";
        }

        if ($hideTrashed) {
            $query .= " AND trash=0 ";
        }

        $query .= " ORDER BY parent, ordering";
        $db->setQuery($query);
        $mitems = $db->loadObjectList();
        $children = array();
        if ($mitems) {
            foreach ($mitems as $v) {
                $v->title = $v->name;
                $v->parent_id = $v->parent;
                $pt = $v->parent;
                $list = @$children[$pt] ? $children[$pt] : array();
                array_push($list, $v);
                $children[$pt] = $list;
            }
        }
        $list = JHTML::_('menu.treerecurse', 0, '', array(), $children, 9999, 0, 0);
        $mitems = array();
        foreach ($list as $item) {
            $mitems[] = JHTML::_('select.option', $item->id, '&nbsp;&nbsp;&nbsp;'.$item->treename);
        }
        return $mitems;
    }

    public function getItemCategory($catid)
    {
        $db = $this->getDBO();
        $db->setQuery("SELECT * FROM #__k2_categories WHERE id = ".(int)$catid);
        $category = $db->loadObject();
        if ($category) {
            JLoader::register('OttomoHelperK2', dirname(__FILE__).'/../helpers/helper.php');
            $category->link = OttomoHelperK2::getCategoryRoute($category->id.':'.$category->alias);
        }
        return $category;
    }

    public function getItemTags($itemID)
    {
        $itemID = (int)$itemID;
        static $K2ItemTagsInstances = array();
        if (isset($K2ItemTagsInstances[$itemID])) {
            return $K2ItemTagsInstances[$itemID];
        }
        $db = $this->getDBO();
        $query = "SELECT tag.* FROM #__k2_tags AS tag 
        JOIN #__k2_tags_xref AS xref ON tag.id = xref.tagID 
        WHERE tag.published=1 AND xref.itemID = ".(int)$itemID." ORDER BY xref.id ASC";
        $db->setQuery($query);
        $rows = $db->loadObjectList();
        JLoader::register('OttomoHelperK2', dirname(__FILE__).'/../helpers/helper.php');
        foreach ($rows as $row) {
            $row->link = OttomoHelperK2::getTagRoute($row->name);
        }
        $K2ItemTagsInstances[$itemID] = $rows;
        return $K2ItemTagsInstances[$itemID];
    }

    public function getItemAuthor($userID, $alias)
    {
        if ($alias) {
            $author = new stdClass();
            $author->name = $alias;
            $author->link = null;
        } else {
            JLoader::register('OttomoHelperK2', dirname(__FILE__).'/../helpers/helper.php');
            $author = JFactory::getUser($userID);
            $author->link = OttomoHelperK2::getUserRoute($author->id);
        }
        return $author;
    }

    public function getItemExtraFields($itemExtraFields)
    {

        static $K2ItemExtraFieldsInstances = array();
        if (isset($K2ItemExtraFieldsInstances[$itemExtraFields])) {
            return $K2ItemExtraFieldsInstances[$itemExtraFields];
        }
        jimport('joomla.filesystem.file');
        $db = $this->getDBO();
        $jsonObjects = is_string($itemExtraFields) ? json_decode($itemExtraFields) : null;
        $imgExtensions = array('jpg', 'jpeg', 'gif', 'png');
        $params = JComponentHelper::getParams('com_k2');
        if (!is_array($jsonObjects) || count($jsonObjects) < 1) {
            return null;
        }
        $extraFieldsIDs = array();
        foreach ($jsonObjects as $object) {
            $extraFieldsIDs[] = (int)$object->id;
        }
        $extraFieldsIDs = array_filter($extraFieldsIDs);
        if (empty($extraFieldsIDs)) {
            return null;
        }
        $condition = implode(',', $extraFieldsIDs);
        $query = "SELECT * FROM #__k2_extra_fields WHERE published=1 AND id IN ({$condition}) ORDER BY ordering ASC";
        $db->setQuery($query);
        $rows = $db->loadObjectList();
        $size = count($rows);
        for ($i = 0; $i < $size; $i++) {
            $value = '';
            $values = array();
            foreach ($jsonObjects as $object) {

                if ($rows[$i]->id == $object->id) {

                    if ($rows[$i]->type == 'textfield' || $rows[$i]->type == 'textarea' || $rows[$i]->type == 'date') {
                        $value = $object->value;
                        if ($rows[$i]->type == 'date' && $value) {
                            $value = JHTML::_('date', $value, JText::_('K2_DATE_FORMAT_LC'), null);
                        }

                    } elseif ($rows[$i]->type == 'labels') {
                        $labels = explode(',', $object->value);
                        if (!is_array($labels)) {
                            $labels = (array)$labels;
                        }
                        $value = '';
                        foreach ($labels as $label) {
                            $label = JString::trim($label);
                            $label = JString::str_ireplace('-', ' ', $label);
                            $value .= '<a href="index.php?option=com_k2&view=itemlist&task=search&searchword='.urlencode($label).'">'.$label.'</a> ';
                        }

                    } elseif ($rows[$i]->type == 'select' || $rows[$i]->type == 'radio') {
                        $options = json_decode($rows[$i]->value);
                        if (is_array($options)) {
                            foreach ($options as $option) {
                                if ($option->value == $object->value) {
                                    $value .= $option->name;
                                }
                            }
                        }
                    } elseif ($rows[$i]->type == 'multipleSelect') {
                        $options = json_decode($rows[$i]->value);
                        if (is_array($options)) {
                            foreach ($options as $option) {
                                if (is_array($object->value) && in_array($option->value, $object->value)) {
                                    $values[] = $option->name;
                                }
                            }
                        }
                        $value = implode(', ', $values);
                    } elseif ($rows[$i]->type == 'csv') {
                        $array = $object->value;
                        if (is_array($array) && count($array)) {
                            $value .= '<table cellspacing="0" cellpadding="0" class="csvTable">';
                            foreach ($array as $key => $row) {
                                $value .= '<tr>';
                                foreach ($row as $cell) {
                                    $value .= ($key > 0) ? '<td>'.$cell.'</td>' : '<th>'.$cell.'</th>';
                                }
                                $value .= '</tr>';
                            }
                            $value .= '</table>';
                        }

                    } else {

                        switch ($object->value[2]) {
                            case 'same':
                            default:
                                $attributes = '';
                                break;

                            case 'new':
                                $attributes = 'target="_blank"';
                                break;

                            case 'popup':
                                $attributes = 'class="classicPopup" rel="{\'x\':'.$params->get('linkPopupWidth').',\'y\':'.$params->get('linkPopupHeight').'}"';
                                break;

                            case 'lightbox':
                                $filename = @basename($object->value[1]);
                                $extension = JFile::getExt($filename);
                                if (!empty($extension) && in_array($extension, $imgExtensions)) {
                                    $attributes = 'class="modal"';
                                } else {
                                    $attributes = 'class="modal" rel="{handler:\'iframe\',size:{x:'.$params->get('linkPopupWidth').',y:'.$params->get('linkPopupHeight').'}}"';
                                }
                                break;
                        }
                        $object->value[0] = JString::trim($object->value[0]);
                        $object->value[1] = JString::trim($object->value[1]);

                        if ($object->value[1] && ($object->value[1] != 'http://' || $object->value[1] != 'https://')) {
                            if ($object->value[0] == '') {
                                $object->value[0] = $object->value[1];
                            }
                            $value = '<a href="'.$object->value[1].'" '.$attributes.'>'.$object->value[0].'</a>';
                        } else {
                            $value = false;
                        }
                    }

                }

            }
            if ($value) {
                $rows[$i]->value = $value;
            } else {
                unset($rows[$i]);
            }
        }
        $K2ItemExtraFieldsInstances[$itemExtraFields] = $rows;
        return $K2ItemExtraFieldsInstances[$itemExtraFields];
    }

}
