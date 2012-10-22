<?php
/**
* @id $Id$
* @author  Joomla Bamboo
* @package  JB Slideshow3
* @copyright Copyright (C) 2006 - 2010 Joomla Bamboo. http://www.joomlabamboo.com  All rights reserved.
* @license  GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
*/

/// no direct access
defined('_JEXEC') or die('Restricted access');

class JElementItems extends JElement
{
   var   $_name = 'Items';

   function fetchElement($name, $value, &$node, $control_name)
   {
		if(file_exists(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_k2'.DS.'admin.k2.php'))
		{
			$db = &JFactory::getDBO();
			$jnow = &JFactory::getDate();
			$now = $jnow->toMySQL();
			$nullDate = $db->getNullDate();
			$size = ( $node->attributes('size') ? $node->attributes('size') : 5 );
			$query = "SELECT id, title FROM #__k2_items
					WHERE published = 1
					AND trash = 0
					AND ( publish_up = ".$db->Quote($nullDate)." OR publish_up <= ".$db->Quote($now)." )
					AND ( publish_down = ".$db->Quote($nullDate)." OR publish_down >= ".$db->Quote($now)." )
					ORDER BY title";
			$db->setQuery($query);
			$options = $db->loadObjectList();

			return JHTML::_('select.genericlist',  $options, ''.$control_name.'['.$name.'][]', 'class="inputbox" style="width:90%;" multiple="multiple" size="5"', 'id', 'title', $value, $control_name.$name);
		} else {
			return JText::_('K2 is not installed');
		}
	}
}
