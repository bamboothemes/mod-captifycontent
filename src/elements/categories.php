<?php

/*

// JoomlaWorks "SuperBlogger" Plugin for Joomla! 1.5.x - Version 1.1

// Copyright (c) 2006 - 2009 JoomlaWorks Ltd. All rights reserved.

// This code cannot be redistributed without permission from JoomlaWorks.

// More info at http://www.joomlaworks.gr

// Designed and developed by the JoomlaWorks team

// ***Last update: June 4th, 2009***

*/



// no direct access

defined( '_JEXEC' ) or die( 'Restricted access' );



// Create a category selector

class JElementCategories extends JElement {



	var	$_name = 'categories';



	function fetchElement($name, $value, &$node, $control_name){

		$db = &JFactory::getDBO();

		$query = 'SELECT * FROM #__sections WHERE published=1';

		$db->setQuery( $query );

		$sections = $db->loadObjectList();



		$categories=array();



		// Create the 'all categories' listing

		$categories[0]->id = '';

		$categories[0]->title = JText::_("Select all categories");



		// Create category listings, grouped by section

		foreach ($sections as $section) {

			$optgroup = JHTML::_('select.optgroup',$section->title,'id','title');

			$query = 'SELECT id,title FROM #__categories WHERE published=1 AND section='.$section->id;

			$db->setQuery( $query );

			$results = $db->loadObjectList();

			array_push($categories,$optgroup);

			foreach ($results as $result) {

				array_push($categories,$result);

			}

		}



		// Create the 'Uncategorised' listing

		$optgroup = JHTML::_('select.optgroup',JText::_("Uncategorised"),'id','title');

		array_push($categories,$optgroup);

		$uncategorised=array();

		$uncategorised['id'] = '0';

		$uncategorised['title'] = JText::_("Uncategorised");

		array_push($categories,$uncategorised);



		// Output

		return JHTML::_('select.genericlist',  $categories, ''.$control_name.'['.$name.'][]', 'class="inputbox" style="width:90%;"  multiple="multiple" size="10"', 'id', 'title', $value );

	}



} // end class

