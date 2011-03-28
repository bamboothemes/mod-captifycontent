<?php

/**
* @version		$Id: helper.php 10616 2008-08-06 11:06:39Z hackwar $
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/



/// no direct access

defined('_JEXEC') or die('Restricted access');



require_once (JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');



class modCaptifycontentHelper

{

	function getList(&$params)

	{

		global $mainframe;

	

		$type = $params->get('type', 'content');

			

		if ($type == "section") {

			

			$sectionid 		= $params->get( 'sectionid', '' );

			$ordering		= $params->get( 'secOrdering' , 'order' );

			$db		=& JFactory::getDBO();

			$user	=& JFactory::getUser();

			$count	= intval($params->get('countcc', 20));

			$contentConfig 	= &JComponentHelper::getParams( 'com_content' );

			$access	= !$contentConfig->get('shownoauth');

			$gid 		= $user->get('aid', 0);

			$now		= date('Y-m-d H:i:s', time() + $mainframe->getCfg('offset') * 60 * 60);

			$nullDate	= $db->getNullDate();

	

		}

		

		if ($type == "category") {

		

		

		$db		=& JFactory::getDBO();

		$user		=& JFactory::getUser();

		$ordering		= $params->get( 'ordering' , 'order' );

		$count		= intval($params->get('countcc', 5)); 

		$contentConfig 	= &JComponentHelper::getParams( 'com_content' );

		$catid		= $params->get('catid');

		$access		= !$contentConfig->get('shownoauth');

		$gid 		= $user->get('aid', 0);

		$now		= date('Y-m-d H:i:s', time() + $mainframe->getCfg('offset') * 60 * 60);

		$nullDate	= $db->getNullDate();



		

		

		}

		

if ($type == "section") {

		

		if ($sectionid)

		{

			if (is_array($sectionid))

			{

				JArrayHelper::toInteger( $sectionid );

				$secCondition = ' AND (a.id=' . implode( ' OR a.id=', $sectionid ) . ')';

			}

			else

			{

				$ids = explode( ',', $sectionid );

				JArrayHelper::toInteger( $ids );

				$secCondition = ' AND (a.id=' . implode( ' OR ca.id=', $ids ) . ')';

			}			

		}

		

		switch ($ordering)

		{

			case 'random':

				$ordering = 'RAND()';

				break;

			case 'alpha':

				$ordering = 'a.title';

				break;

			case 'ralpha':

				$ordering = 'a.title DESC';

				break;

			case 'order':

			default:

				$ordering = 'a.ordering';

				break;

		}

		

		$query = 'SELECT a.id AS id, a.title AS title,a.description AS description,a.image AS image, COUNT(b.id) as cnt' .

			' FROM #__sections as a' .

			' LEFT JOIN #__content as b ON a.id = b.sectionid' .

			($access ? ' AND b.access <= '.(int) $gid : '') .

			' AND ( b.publish_up = '.$db->Quote($nullDate).' OR b.publish_up <= '.$db->Quote($now).' )' .

			' AND ( b.publish_down = '.$db->Quote($nullDate).' OR b.publish_down >= '.$db->Quote($now).' )' .

			' WHERE a.scope = "content"' .

			' AND a.published = 1' .

			($access ? ' AND a.access <= '.(int) $gid : '') .

			($sectionid ? $secCondition : '').

			' GROUP BY a.id '.

			' HAVING COUNT( b.id ) > 0' .

			' ORDER BY '. $ordering;

		$db->setQuery($query, 0, $count);

		$rows = $db->loadObjectList();



		return $rows;

		

}



else if



($type == "category") {

	

			if ($catid)

			{

				if (is_array($catid))

				{

					JArrayHelper::toInteger( $catid );

					$catCondition = ' AND (a.id=' . implode( ' OR a.id=', $catid ) . ')';

				}

				else

				{

					$ids = explode( ',', $catid );

					JArrayHelper::toInteger( $ids );

					$catCondition = ' AND (a.id=' . implode( ' OR a.id=', $ids ) . ')';

				}			

			}

			

			switch ($ordering)

			{

				case 'random':

					$ordering = 'RAND()';

					break;

				case 'alpha':

					$ordering = 'a.title';

					break;

				case 'ralpha':

					$ordering = 'a.title DESC';

					break;

				case 'order':

				default:

					$ordering = 'a.ordering';

					break;

			}

			

			

			// Query Sections table for all Categories that match Section ID

		$query = 'SELECT a.id AS id, a.title AS title,a.image AS image, COUNT(b.id) as cnt' .

			' FROM #__categories as a' .

			' LEFT JOIN #__content as b ON b.catid = a.id' .

			($access ? ' AND b.access <= '.(int) $gid : '') .

			' AND ( b.publish_up = "'.$nullDate.'" OR b.publish_up <= "'.$now.'" )' .

			' AND ( b.publish_down = "'.$nullDate.'" OR b.publish_down >= "'.$now.'" )' .

			' WHERE a.published = 1' .

			($catid ? $catCondition : '').

			($access ? ' AND a.access <= '.$gid : '') .

			' GROUP BY a.id '.

			' ORDER BY '. $ordering;

						

		$db->setQuery($query, 0, $count);

		$rows = $db->loadObjectList();

		

		foreach($rows as $row){

			$row->link = JRoute::_(ContentHelperRoute::getCategoryRoute($row->id,'').'&layout=blog');

		}



		return $rows;

	}





else if

($type == "content") {

	

		$db			=& JFactory::getDBO();

		$user		=& JFactory::getUser();

		$userId		= (int) $user->get('id');

		$count		= (int) $params->get('countcc', 5);

		$catid		= $params->get('catid');

		$artid		= $params->get('artid');

		$show_front	= $params->get('show_front', 1);

		$aid		= $user->get('aid', 0);

		$contentConfig = &JComponentHelper::getParams( 'com_content' );

		$access		= !$contentConfig->get('show_noauth');



		$nullDate	= $db->getNullDate();



		$date =& JFactory::getDate();

		$now = $date->toMySQL();



		$where		= 'a.state = 1'

			. ' AND ( a.publish_up = '.$db->Quote($nullDate).' OR a.publish_up <= '.$db->Quote($now).' )'

			. ' AND ( a.publish_down = '.$db->Quote($nullDate).' OR a.publish_down >= '.$db->Quote($now).' )'

			;



		// User Filter

		switch ($params->get( 'user_id' ))

		{

			case 'by_me':

				$where .= ' AND (created_by = ' . (int) $userId . ' OR modified_by = ' . (int) $userId . ')';

				break;

			case 'not_me':

				$where .= ' AND (created_by <> ' . (int) $userId . ' AND modified_by <> ' . (int) $userId . ')';

				break;

		}



		// Ordering

		switch ($params->get( 'ordering' ))

		{

			case 'random':

				$ordering = 'RAND()';

				break;

			case 'date':

				$ordering = 'a.created';

				break;

			case 'rdate':

				$ordering = 'a.created DESC';

				break;

			case 'alpha':

				$ordering = 'a.title';

				break;

			case 'ralpha':

				$ordering = 'a.title DESC';

				break;

			case 'hits':

				$ordering = 'a.hits DESC';

				break;

			case 'rhits':

				$ordering = 'a.hits ASC';

				break;

			case 'order':

			default:

				$ordering = 'a.ordering';

				break;

		}

		

		if ($artid)
	
		{ 
	
			if( is_array( $artid ) ) {
	
				$artCondition = ' AND (a.id IN ( ' . implode( ',', $artid ) . ') )';
	
			} else {
	
				$artCondition = ' AND (a.id = '.$artid.')';
	
			}	
	
		}

		

		if ($catid)

		{

			if (is_array($catid))

			{

				JArrayHelper::toInteger( $catid );

				$catCondition = ' AND (cc.id=' . implode( ' OR cc.id=', $catid ) . ')';

				if($artid){

					$catCondition = ' OR (cc.id=' . implode( ' OR cc.id=', $catid ) . ')';

				}

			}

			else

			{

				$ids = explode( ',', $catid );

				JArrayHelper::toInteger( $ids );

				$catCondition = ' AND (cc.id=' . implode( ' OR cc.id=', $ids ) . ')';

				if($artid){

					$catCondition = ' OR (cc.id=' . implode( ' OR cc.id=', $ids ) . ')';

				}

			}			

		}

		

		//if($articleSource == 'cat'){

		//	$artCondition = '';

		//}elseif ($articleSource == 'art'){

		//	$catCondition = '';

		//}

		

		// Content Items only

		$query = 'SELECT a.*, ' .

			' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(":", a.id, a.alias) ELSE a.id END as slug,'.

			' CASE WHEN CHAR_LENGTH(cc.alias) THEN CONCAT_WS(":", cc.id, cc.alias) ELSE cc.id END as catslug'.

			' FROM #__content AS a' .

			($show_front == '0' ? ' LEFT JOIN #__content_frontpage AS f ON f.content_id = a.id' : '') .

			' INNER JOIN #__categories AS cc ON cc.id = a.catid' .

			' INNER JOIN #__sections AS s ON s.id = a.sectionid' .

			' WHERE '. $where .' AND s.id > 0' .

			($access ? ' AND a.access <= ' .(int) $aid. ' AND cc.access <= ' .(int) $aid. ' AND s.access <= ' .(int) $aid : '').

			($artid ? $artCondition : '').

			($catid ? $catCondition : '').

			($show_front == '0' ? ' AND f.content_id IS NULL ' : '').

			' AND s.published = 1' .

			' AND cc.published = 1' .

			' ORDER BY '. $ordering;

		$db->setQuery($query, 0, $count);

		$rows = $db->loadObjectList();





		$i		= 0;

		$lists	= array();

		foreach ( $rows as $row )

		{

			if($row->access <= $aid)

			{

				$lists[$i]->link = JRoute::_(ContentHelperRoute::getArticleRoute($row->slug, $row->catslug, $row->sectionid));

			} else {

				$lists[$i]->link = JRoute::_('index.php?option=com_user&view=login');

			}

			

			$lists[$i]->title = $row->title;

			$lists[$i]->text = $row->introtext;

			$i++;

		}



		return $lists;

	}

}

	

	

}



class modCCK2ContentHelper {



  function getList(&$params) {

  	global $mainframe;

    jimport('joomla.filesystem.file');

	$contentSource = $params->get('type','k2');

	if($contentSource == "k2"){

    	$limit = $params->get('countcc', 5);

	    $cid = $params->get('k2catid', NULL);

	    $ordering = $params->get('orderingK2');

	    $limitstart = JRequest::getInt('limitstart');

	    $user = &JFactory::getUser();

	    $aid = $user->get('aid');

	    $db = &JFactory::getDBO();

	    $jnow = &JFactory::getDate();

	    $now = $jnow->toMySQL();

	    $nullDate = $db->getNullDate();

		$itemid = $params->get('itemid','');

    

	    $query = "SELECT i.*, c.name AS categoryname,c.id AS categoryid, c.alias AS categoryalias, c.params AS categoryparams";

	    if ($ordering == 'best')

	      $query .= ", (r.rating_sum/r.rating_count) AS rating";

      

	    $query .= " FROM #__k2_items as i LEFT JOIN #__k2_categories c ON c.id = i.catid";

    

	    if ($ordering == 'best')

	      $query .= " LEFT JOIN #__k2_rating r ON r.itemID = i.id";

      

	    $query .= " WHERE i.published = 1 AND i.access <= {$aid} AND i.trash = 0 AND c.published = 1 AND c.access <= {$aid} AND c.trash = 0";

    

	    $query .= " AND ( i.publish_up = ".$db->Quote($nullDate)." OR i.publish_up <= ".$db->Quote($now)." )";

	    $query .= " AND ( i.publish_down = ".$db->Quote($nullDate)." OR i.publish_down >= ".$db->Quote($now)." )";

    

		if($params->get('itemFilter') != 'item'){

			if (!is_null($cid)) {

		        if (is_array($cid)) {

		         if ($params->get('getChildren')) {



		           require_once (JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'models'.DS.'itemlist.php');

		           $allChildren = array();

		           foreach ($cid as $id) {

		             $categories = K2ModelItemlist::getCategoryChilds($id);

		             $categories[] = $id;

		             $categories = @array_unique($categories);

		             $allChildren = @array_merge($allChildren, $categories);

		           }



		           $allChildren = @array_unique($allChildren);

		           $sql = @implode(',', $allChildren);

		           $query .= " AND i.catid IN ({$sql})";



		         } else {

		           $query .= " AND i.catid IN(".implode(',', $cid).")";

		         }



		       } else {

		         if ($params->get('getChildren')) {

		           require_once (JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'models'.DS.'itemlist.php');

		           $categories = K2ModelItemlist::getCategoryChilds($cid);

		           $categories[] = $cid;

		           $categories = @array_unique($categories);

		           $sql = @implode(',', $categories);

		           $query .= " AND i.catid IN ({$sql})";

		         } else {

		           $query .= " AND i.catid={$cid}";

		         }



		       }

		     }

		}

	

	    if($params->get('itemFilter') == 'item'){

			$itemid		= preg_split("/[\s,]+/", $itemid);

			JArrayHelper::toInteger( $itemid );

			$query .= ' AND (i.id=' . implode( ' OR i.id=', $itemid ) . ')';

		}

	

		if($params->get('itemFilter') == 'catitem'){

			$itemid		= preg_split("/[\s,]+/", $itemid);

			JArrayHelper::toInteger( $itemid );

			$query .= ' OR (i.id=' . implode( ' OR i.id=', $itemid ) . ')';

		}

 

	    if ($params->get('itemFilter') == 'feat')

	      $query .= " AND i.featured = 1";

      

	    switch ($ordering) {

    

	      case 'date':

	        $orderby = 'i.created ASC';

	        break;

        

	      case 'rdate':

	        $orderby = 'i.created DESC';

	        break;

        

	      case 'alpha':

	        $orderby = 'i.title';

	        break;

        

	      case 'ralpha':

	        $orderby = 'i.title DESC';

	        break;

        

	      case 'order':

	        if ($params->get('itemFilter') == 'feat')

	          $orderby = 'i.featured_ordering';

	        else

	          $orderby = 'i.ordering';

	        break;

        

	      case 'hits':

	        $orderby = 'i.hits DESC';

	        break;

        

	      case 'rand':

	        $orderby = 'RAND()';

	        break;

        

	      case 'best':

	        $orderby = 'rating DESC';

	        break;

        

	      default:

	        $orderby = 'i.id DESC';

	        break;

	    }

    

	    $query .= " ORDER BY ".$orderby;

	    $db->setQuery($query, 0, $limit);

	    $items = $db->loadObjectList();



		require_once (JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'models'.DS.'item.php');

		$model = new K2ModelItem;

	

		require_once (JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'helpers'.DS.'route.php');

	

    

	    if (count($items)) {

    

	      	$k2ImageSource = $params->get('displayImages','k2item');

			foreach ($items as $item) {

      

	        //Images

			if($k2ImageSource == "k2item")

			{

				if (JFile::exists(JPATH_SITE.DS.'media'.DS.'k2'.DS.'items'.DS.'src'.DS.md5("Image".$item->id).'.jpg'))

	            $item->imageOriginal = 'media/k2/items/src/'.md5("Image".$item->id).'.jpg';



			if (JFile::exists(JPATH_SITE.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$item->id).'_XS.jpg'))

	            $item->imageXSmall = 'media/k2/items/cache/'.md5("Image".$item->id).'_XS.jpg';



                      if (JFile::exists(JPATH_SITE.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$item->id).'_S.jpg'))

                        $item->imageSmall = 'media/k2/items/cache/'.md5("Image".$item->id).'_S.jpg';



                      if (JFile::exists(JPATH_SITE.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$item->id).'_M.jpg'))

                        $item->imageMedium = 'media/k2/items/cache/'.md5("Image".$item->id).'_M.jpg';



                      if (JFile::exists(JPATH_SITE.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$item->id).'_L.jpg'))

                        $item->imageLarge = 'media/k2/items/cache/'.md5("Image".$item->id).'_L.jpg';



                      if (JFile::exists(JPATH_SITE.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$item->id).'_XL.jpg'))

                        $item->imageXLarge = 'media/k2/items/cache/'.md5("Image".$item->id).'_XL.jpg';



                      if (JFile::exists(JPATH_SITE.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$item->id).'_Generic.jpg'))

                        $item->imageGeneric = 'media/k2/items/cache/'.md5("Image".$item->id).'_Generic.jpg';



                    $image = 'image'.$params->get('itemImageSize');



                        $item->firstimage = $item->$image;



                        }elseif ($k2ImageSource == "k2content"){



                            $item->firstimage = "";

                        }

		



			

	        $item->numOfComments = $model->countItemComments($item->id);

        

        

	        //Read more link

	        $item->link = urldecode(JRoute::_(K2HelperRoute::getItemRoute($item->id.':'.urlencode($item->alias), $item->catid.':'.urlencode($item->categoryalias))));

       

        

        

	        // Item text

			$item->text = $item->introtext;

		

		

        

	        $rows[] = $item;

	      }

      

	      return $rows;

      

	    }



	}

	if($contentSource == "k2category"){

		$limit = $params->get('countcc', 5);

	    $cid = $params->get('k2catid', NULL);

	    $ordering = $params->get('orderingK2');

	    $limitstart = JRequest::getInt('limitstart');

	    $user = &JFactory::getUser();

	    $aid = $user->get('aid');

	    $db = &JFactory::getDBO();

	    $jnow = &JFactory::getDate();

	    $now = $jnow->toMySQL();

	    $nullDate = $db->getNullDate();

    

	    $query = "SELECT c.*";

	    $query .= " FROM #__k2_categories as c";      

	    $query .= " WHERE c.published = 1 AND c.access <= {$aid} AND c.trash = 0";

    

		if (!is_null($cid)) {

	        if (is_array($cid)) {

	         if ($params->get('getChildren')) {



	           require_once (JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'models'.DS.'itemlist.php');

	           $allChildren = array();

	           foreach ($cid as $id) {

	             $categories = K2ModelItemlist::getCategoryChilds($id);

	             $categories[] = $id;

	             $categories = @array_unique($categories);

	             $allChildren = @array_merge($allChildren, $categories);

	           }



	           $allChildren = @array_unique($allChildren);

	           $sql = @implode(',', $allChildren);

	           $query .= " AND c.id IN ({$sql})";



	         } else {

	           $query .= " AND c.id IN(".implode(',', $cid).")";

	         }



	       } else {

	         if ($params->get('getChildren')) {

	           require_once (JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'models'.DS.'itemlist.php');

	           $categories = K2ModelItemlist::getCategoryChilds($cid);

	           $categories[] = $cid;

	           $categories = @array_unique($categories);

	           $sql = @implode(',', $categories);

	           $query .= " AND c.id IN ({$sql})";

	         } else {

	           $query .= " AND c.id={$cid}";

	         }



	       }

	     }

	

		switch ($ordering) {



	      

	      case 'alpha':

	        $orderby = 'c.name';

	        break;



	      case 'ralpha':

	        $orderby = 'c.name DESC';

	        break;



	      case 'rand':

	        $orderby = 'RAND()';

	        break;



	      case 'order':

			$orderby = 'c.ordering';

			break;

			

		  default:

			$orderby = 'c.ordering';

	        break;

	    }

      

	    $query .= " ORDER BY ".$orderby;

	    $db->setQuery($query, 0, $limit);

	    $items = $db->loadObjectList();



		require_once (JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'models'.DS.'item.php');

		$model = new K2ModelItem;

	

		require_once (JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'helpers'.DS.'route.php');

	

    

	    if (count($items)) {

    

	      	$k2ImageSource = $params->get('displayImages','k2item');

			foreach ($items as $item) {

      

	        //Images

	

			

			if (JFile::exists(JPATH_SITE.DS.'media'.DS.'k2'.DS.'categories'.DS.$item->image))

	            $item->image = 'media/k2/categories/'.$item->image;

			

        

	        //Read more link

	        $item->link = urldecode(JRoute::_(K2HelperRoute::getCategoryRoute($item->id.':'.urlencode($item->alias))));

       

        

        

	        // Item text

			$item->title = $item->name;

		

		

        

	        $rows[] = $item;

	      }

      

	      return $rows;

      

	    }

		

	}

    

  }

  

}

