<?php

/// no direct access
defined('_JEXEC') or die('Restricted access');

class JElementArticles extends JElement
{
	var   $_name = 'Articles';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$db =& JFactory::getDBO();
		$size = ( $node->attributes('size') ? $node->attributes('size') : 5 );
		$query = 'SELECT id, title FROM #__content WHERE unix_timestamp(publish_up) <= '.time().' AND (unix_timestamp(publish_down) >= '.time().' OR unix_timestamp(publish_down)=0) ORDER BY title';
		$db->setQuery($query);
		$options = $db->loadObjectList();

		return JHTML::_('select.genericlist',  $options, ''.$control_name.'['.$name.'][]',  'class="inputbox" style="width:90%;" multiple="multiple" size="5"', 'id', 'title', $value, $control_name.$name);
	}
}
