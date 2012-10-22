<?php
/*
// JoomlaWorks "SuperBlogger" Plugin for Joomla! 1.5.x - Version 1.1
// Copyright (c) 2006 - 2009 JoomlaWorks Ltd. All rights reserved.
// This code cannot be redistributed without permission from JoomlaWorks.
// More info at http://www.joomlaworks.gr
// Designed and developed by the JoomlaWorks team
// ***Last update: June 4th, 2009***
//Adapted by Joomla Bamboo
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Create a category selector
class JElementSectionsMultiple extends JElement {

	var	$_name = 'sections';

	function fetchElement($name, $value, &$node, $control_name){
		$db = &JFactory::getDBO();
		$query = 'SELECT * FROM #__sections WHERE published=1';
		$db->setQuery( $query );
		$results = $db->loadObjectList();

		$sections=array();

		// Create the 'all categories' listing
		$sections[0]->id = '';
		$sections[0]->title = JText::_("Select all sections");

		// Create category listings, grouped by section
		foreach ($results as $result) {
			array_push($sections,$result);
		}


		// Output
		return JHTML::_('select.genericlist',  $sections, ''.$control_name.'['.$name.'][]', 'class="inputbox" style="width:90%;"  multiple="multiple" size="10"', 'id', 'title', $value );
	}

} // end class
