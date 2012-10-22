<?php
/// no direct access
defined('_JEXEC') or die('Restricted access');

class JFormFieldK2itemslist extends JFormField
{
   protected   $type = 'k2itemslist';

   protected function getInput()
   {
		if(file_exists(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_k2'.DS.'admin.k2.php'))
				{

		$db =& JFactory::getDBO();
		$size = ( $this->element['size'] ? $this->element['size'] : 5 );
			$query = 'SELECT id, title FROM #__k2_items WHERE published = 1
			AND trash = 0
			AND unix_timestamp(publish_up) <= '.time().' AND (unix_timestamp(publish_down) >= '.time().' OR unix_timestamp(publish_down)=0) ORDER BY title';
		  $db->setQuery($query);
		  $options = $db->loadObjectList();
		$k2items=array();
		foreach ($options as $result) {
			array_push($k2items,$result);

		}

		  return JHTML::_('select.genericlist',  $k2items, ''.$this->formControl.'[params]['.$this->fieldname.'][]',  'class="inputbox" style="width:90%;" multiple="multiple" size="5"', 'id', 'title', $this->value, $this->id);
	}
   }
}
