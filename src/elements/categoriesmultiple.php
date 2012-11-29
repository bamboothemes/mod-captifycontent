<?php

/*

// "K2" Component by JoomlaWorks for Joomla! 1.5.x - Version 2.1

// Copyright (c) 2006 - 2009 JoomlaWorks Ltd. All rights reserved.

// Released under the GNU/GPL license: http://www.gnu.org/copyleft/gpl.html

// More info at http://www.joomlaworks.gr and http://k2.joomlaworks.gr

// Designed and developed by the JoomlaWorks team

// *** Last update: September 9th, 2009 ***

*/



// no direct access

defined('_JEXEC') or die('Restricted access');



class JElementCategoriesmultiple extends JElement

{



	var	$_name = 'categoriesmultiple';



	function fetchElement($name, $value, &$node, $control_name){

		$db = &JFactory::getDBO();



		$query = 'SELECT m.* FROM #__k2_categories m WHERE published=1 AND trash = 0 ORDER BY parent, ordering';

		$db->setQuery( $query );

		$mitems = $db->loadObjectList();

		$children = array();

		if ($mitems){

			foreach ( $mitems as $v ){

				$pt 	= $v->parent;

				$list = @$children[$pt] ? $children[$pt] : array();

				array_push( $list, $v );

				$children[$pt] = $list;

			}

		}

		$list = JHTML::_('menu.treerecurse', 0, '', array(), $children, 9999, 0, 0 );

		$mitems = array();



		foreach ( $list as $item ) {

			$mitems[] = JHTML::_('select.option',  $item->id, '&nbsp;&nbsp;&nbsp;'.$item->treename );

		}



		$doc = & JFactory::getDocument();

		$output= JHTML::_('select.genericlist',  $mitems, ''.$control_name.'['.$name.'][]', 'class="inputbox" style="width:90%;" multiple="multiple" size="10"', 'value', 'text', $value );

		return $output;

	}

}

