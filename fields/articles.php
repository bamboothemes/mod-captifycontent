<?php
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class JFormFieldArticles extends JFormField
{
   protected   $type = 'Articles';

   protected function getInput($name, $value, &$node, $control_name)
   {
		$db =& JFactory::getDBO();
		$size = ( $this->element['size'] ? $this->element['size'] : 5 );
		$query = 'SELECT id, title FROM #__content WHERE unix_timestamp(publish_up) <= '.time().' AND (unix_timestamp(publish_down) >= '.time().' OR unix_timestamp(publish_down)=0) ORDER BY title';
		$db->setQuery($query);
		$options = $db->loadObjectList();

		return JHTML::_('select.genericlist',  $options, ''.$this->formControl.'[params]['.$this->fieldname.'][]',  'class="inputbox" style="width:90%;" multiple="multiple" size="5"', 'id', 'title', $this->value, $this->id);
   }
}
