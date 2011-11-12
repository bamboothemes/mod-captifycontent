<?php
/**
 * @version		$Id: categoriesmultiple.php 1034 2011-10-04 17:00:00Z joomlaworks $
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.gr
 * @copyright	Copyright (c) 2006 - 2011 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Create a category selector

class JFormFieldK2categories extends JFormField {

		var	$type = 'k2categories';

		function getInput(){
			
			$db = &JFactory::getDBO();
			$query = 'SELECT id,name FROM #__k2_categories m WHERE published=1 AND trash = 0 ORDER BY parent, ordering';
			$db->setQuery( $query );
			$results = $db->loadObjectList();
			$categories=array();
			// Create the 'all categories' listing
			$categories[0]->id = '';
			$categories[0]->title = JText::_("Select all Categories");
			foreach ($results as $result) {
				$result->title = $result->name;
				array_push($categories,$result);

			}

			return JHTML::_('select.genericlist',  $categories, ''.$this->formControl.'[params]['.$this->fieldname.'][]', 'class="inputbox" style="width:90%;"  multiple="multiple" size="5"', 'id', 'title', $this->value, $this->id );
			
		}
}

