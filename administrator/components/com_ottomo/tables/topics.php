<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

class OttomoTableTopics extends JTable
{
    public function __construct($db)
    {
        parent::__construct('#__ottomo_topics', 'id', $db);
        if (!$this->id) {
            $this->published = 1;
        }
    }

    public function check()
    {
        if (trim($this->name) == '') {
            $this->setError(JText::_('COM_OTTOMO_TOPIC_MUST_HAVE_A_NAME'));
            return false;
        }
        return true;
    }

    public function delete($id = null)
    {
        JArrayHelper::toInteger($id);

        $db = JFactory::getDbo();

        $query = $db->getQuery(true);
        $query->delete($db->quoteName('#__ottomo_topics'));
        $query->where($db->quoteName('id').' IN('.implode(',', $id).')');
        $db->setQuery($query);
        $db->execute();

        $query = $db->getQuery(true);
        $query->delete($db->quoteName('#__ottomo_email_campaigns_xref'));
        $query->where($db->quoteName('topicID').' IN('.implode(',', $id).')');
        $db->setQuery($query);
        $db->execute();

        $query = $db->getQuery(true);
        $query->delete($db->quoteName('#__ottomo_subscribers_xref'));
        $query->where($db->quoteName('topicID').' IN('.implode(',', $id).')');
        $db->setQuery($query);
        $db->execute();

        return true;
    }

}
