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

if (substr(JVERSION, 0, 3) >= '1.6') {

	/*********************************************************************************************************************
	 *
	 *  Helper for Joomla 1.7 +
	 *
	**********************************************************************************************************************/
		
	jimport('joomla.application.component.model');

	$com_path = JPATH_SITE.DS.'components'.DS.'com_content'.DS;
	require_once $com_path.'router.php';
	require_once $com_path.'helpers'.DS.'route.php';

	jimport('joomla.application.component.model');

	JModel::addIncludePath($com_path.DS.'models', 'ContentModel');

	abstract class modCaptifycontentHelper
	{
		public static function getList(&$params)
		{
			$type = $params->get('type', 'content');
			$app = JFactory::getApplication();
			$appParams = $app->getParams();

			if ($type == "category") {
				//echo $params->get('c_catid','0');
				$catids = $params->get('c_catid','0');
				$access = !JComponentHelper::getParams('com_content')->get('show_noauth');
				$authorised = JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id'));
				$categories = JModel::getInstance('Categories', 'ContentModel', array('ignore_request' => true));
				$catCount = $params->get('count', 5);
				$levels = $params->get('c_levels', 1) ? $params->get('c_levels', 1) : 9999;			
				$categories->setState('filter.published', '1');
				$categories->setState('filter.access', $access);
				if ($catids && $params->get('c_show_child_category_articles', 0) && (int) $params->get('c_levels', 0) > 0) {
					$additional_catids = array();
					foreach($catids as $catid)
					{
						$categories->setState('filter.parentId', $catid);
						$recursive = true;
						$items = $categories->getItems($recursive);

						if ($items)
						{
							foreach($items as $category)
							{
								$condition = (($category->level - $categories->getParent()->level) <= $levels);
								if ($condition) {
									$additional_catids[] = $category->id;
								}

							}
						}
					}

					$catids = array_unique(array_merge($catids, $additional_catids));
				}

				$items = array();
				$jcategory = JCategories::getInstance('Content');

				if (is_array($catids)) foreach ( $catids as $catid )
				{
					$catitem = $jcategory->get($catid);
					if(!($catitem->published)) continue;
					$catitem->slug = $catitem->id.':'.$catitem->alias;
					$catitem->catslug = $catitem->id ? $catitem->id .':'.$catitem->alias : $catitem->id;

					if ($access || in_array($catitem->access, $authorised)) {

						$catitem->link = JRoute::_(ContentHelperRoute::getCategoryRoute($catitem->id).'&layout=blog');
					}
					 else {
						// Angie Fixed Routing
						$app	= JFactory::getApplication();
						$menu	= $app->getMenu();
						$menuitems	= $menu->getItems('link', 'index.php?option=com_users&view=login');
					if(isset($menuitems[0])) {
							$Itemid = $menuitems[0]->id;
						} else if (JRequest::getInt('Itemid') > 0) { //use Itemid from requesting page only if there is no existing menu
							$Itemid = JRequest::getInt('Itemid');
						}

						$catitem->link = JRoute::_('index.php?option=com_users&view=login&Itemid='.$Itemid);
						}

					$items[] = $catitem;

				}
				return $items;	



		} else if($type == "content") {
		
				$catids = $params->get('catid');
				$articles = JModel::getInstance('Articles', 'ContentModel', array('ignore_request' => true));
				$articles->setState('params', $appParams);
				$artids		= $params->get('artid');
				$show_front	= $params->get('show_front', 1);
				// Set the filters based on the module params
				$articles->setState('list.start', 0);
				$articles->setState('list.limit', (int) $params->get('count', 5));
				$articles->setState('filter.published', 1);

				// Access filter
				$access = !JComponentHelper::getParams('com_content')->get('show_noauth');
				$authorised = JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id'));
				$articles->setState('filter.access', $access);

				$catids = $params->get('catid');
				$articles->setState('filter.category_id.include', (bool) $params->get('category_filtering_type', 1));

				// Category filter
				if ($catids) {
					if ($params->get('show_child_category_articles', 0) && (int) $params->get('levels', 0) > 0) {
						// Get an instance of the generic categories model
						$categories = JModel::getInstance('Categories', 'ContentModel', array('ignore_request' => true));
						$categories->setState('params', $appParams);
						$levels = $params->get('levels', 1) ? $params->get('levels', 1) : 9999;
						$categories->setState('filter.get_children', $levels);
						$categories->setState('filter.published', 1);
						$categories->setState('filter.access', $access);
						$additional_catids = array();

						foreach($catids as $catid)
						{
							$categories->setState('filter.parentId', $catid);
							$recursive = true;
							$items = $categories->getItems($recursive);

							if ($items) {
								foreach($items as $category) {
									$condition = (($category->level - $categories->getParent()->level) <= $levels);
									if ($condition) {
										$additional_catids[] = $category->id;
									}
								}
							}
						}

						$catids = array_unique(array_merge($catids, $additional_catids));
					}
					$articles->setState('filter.category_id', $catids);
				}
				
				if($artids) {
					$articles->setState('filter.article_id', $artids);
					$articles->setState('filter.article_id.include', $params->get('article_filtering_type', 1));
				}
				// Ordering
				$articles->setState('list.ordering', $params->get('ordering', 'a.ordering'));
				$articles->setState('list.direction', $params->get('ordering_direction', 'ASC'));

				// New Parameters
				$articles->setState('filter.featured', $params->get('show_front', 'show'));
				$articles->setState('filter.author_id', $params->get('created_by', ""));
				$articles->setState('filter.author_id.include', $params->get('author_filtering_type', 1));
				$articles->setState('filter.author_alias', $params->get('created_by_alias', ""));
				$articles->setState('filter.author_alias.include', $params->get('author_alias_filtering_type', 1));
				$excluded_articles = $params->get('excluded_articles', '');

				$articles->setState('filter.language',$app->getLanguageFilter());

				$items = $articles->getItems();
				
				foreach ( $items as $item ) {
				
					$item->slug = $item->id.':'.$item->alias;
					$item->catslug = $item->catid ? $item->catid .':'.$item->category_alias : $item->catid;

					if ($access || in_array($item->access, $authorised)) {
						$item->link = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catslug));
						$item->catlink = JRoute::_(ContentHelperRoute::getCategoryRoute($item->catslug).'&layout=blog');
					} else {
						// Angie Fixed Routing
						$app	= JFactory::getApplication();
						$menu	= $app->getMenu();
						$menuitems	= $menu->getItems('link', 'index.php?option=com_users&view=login');
						
						if(isset($menuitems[0])) {
							$Itemid = $menuitems[0]->id;
						} else if (JRequest::getInt('Itemid') > 0) { 
							//use Itemid from requesting page only if there is no existing menu
							$Itemid = JRequest::getInt('Itemid');
						}

						$item->link = JRoute::_('index.php?option=com_users&view=login&Itemid='.$Itemid);
					}
			
					$item->text = $item->introtext;
				}			
				return $items;
			}
		}
	}

} else {

	/*********************************************************************************************************************
	 *
	 *  Helper for Joomla 1.5 +
	 * 
	**********************************************************************************************************************/
	
	require_once (JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');

	class modCaptifycontentHelper
	{
		function getList(&$params) {
		
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

				if ($sectionid) {
					if (is_array($sectionid)) {				
						JArrayHelper::toInteger( $sectionid );
						$secCondition = ' AND (a.id=' . implode( ' OR a.id=', $sectionid ) . ')';
					} else {				
						$ids = explode( ',', $sectionid );
						JArrayHelper::toInteger( $ids );
						$secCondition = ' AND (a.id=' . implode( ' OR ca.id=', $ids ) . ')';					
					}			
				}

				switch ($ordering) {

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
			
			} else if ($type == "category") {

				if ($catid) {
					if (is_array($catid)) {
						JArrayHelper::toInteger( $catid );
						$catCondition = ' AND (a.id=' . implode( ' OR a.id=', $catid ) . ')';
					} else {
						$ids = explode( ',', $catid );
						JArrayHelper::toInteger( $ids );
						$catCondition = ' AND (a.id=' . implode( ' OR a.id=', $ids ) . ')';
					}
				}

				switch ($ordering) {

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

			} else if ($type == "content") {

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

				$where	= 'a.state = 1'
					. ' AND ( a.publish_up = '.$db->Quote($nullDate).' OR a.publish_up <= '.$db->Quote($now).' )'
					. ' AND ( a.publish_down = '.$db->Quote($nullDate).' OR a.publish_down >= '.$db->Quote($now).' )'
					;

				// User Filter
				switch ($params->get( 'user_id' )) {

					case 'by_me':
						$where .= ' AND (created_by = ' . (int) $userId . ' OR modified_by = ' . (int) $userId . ')';
						break;

					case 'not_me':
						$where .= ' AND (created_by <> ' . (int) $userId . ' AND modified_by <> ' . (int) $userId . ')';
						break;
				}

				// Ordering
				switch ($params->get( 'ordering' ))	{

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

				if ($artid) {
					if( is_array( $artid ) ) {	
						$artCondition = ' AND (a.id IN ( ' . implode( ',', $artid ) . ') )';	
					} else {	
						$artCondition = ' AND (a.id = '.$artid.')';	
					}
				}

				if ($catid) {
					if (is_array($catid)) {
						JArrayHelper::toInteger( $catid );
						$catCondition = ' AND (cc.id=' . implode( ' OR cc.id=', $catid ) . ')';
						
						if($artid){
							$catCondition = ' OR (cc.id=' . implode( ' OR cc.id=', $catid ) . ')';
						}
					} else {
						$ids = explode( ',', $catid );
						JArrayHelper::toInteger( $ids );
						$catCondition = ' AND (cc.id=' . implode( ' OR cc.id=', $ids ) . ')';
						
						if($artid){
							$catCondition = ' OR (cc.id=' . implode( ' OR cc.id=', $ids ) . ')';
						}
					}
				}

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

				foreach ( $rows as $row ) {
					if($row->access <= $aid) {
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
}


$k2file = JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'models'.DS.'comments.php';

if (file_exists($k2file)){

	/*********************************************************************************************************************
	 *
	 *  Helper for K2 v2.4.x
	 *
	**********************************************************************************************************************/

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

				if(!is_array($itemid)) {
					$itemid		= preg_split("/[\s,]+/", $itemid);
				}
				JArrayHelper::toInteger( $itemid );
				$query .= ' AND i.id IN (' . implode( ',', $itemid ) . ')';

				if ($params->get('itemFilter') == 'feat')
					$query .= " AND i.featured = 1";				
				else if ($params->get('itemFilter') == 'hide')
					$query .= " AND i.featured = 0";

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
						if($k2ImageSource == "k2item") {

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
							
							if(isset($item->$image)) {
								$item->firstimage = $item->$image;
							} else {
								$item->firstimage = "";
							}

						} elseif ($k2ImageSource == "k2content"){
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

				if ($params->get('itemFilter') == 'feat')
					$query .= " AND i.featured = 1";				
				else if ($params->get('itemFilter') == 'hide')
					$query .= " AND i.featured = 0";

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
						if($k2ImageSource == "k2item") {

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
							
							if(isset($item->$image)) {
								$item->firstimage = $item->$image;
							} else {
								$item->firstimage = "";
							}

						} elseif ($k2ImageSource == "k2content"){
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
		}
	}
} else {
	
	/*********************************************************************************************************************
	 * 
	 *  Helper for K2 v2.5.x
	 * 
	**********************************************************************************************************************/

		
	class modCCK2ContentHelper {

		function getList(&$params) {

			require_once(JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'helpers'.DS.'route.php');
			require_once(JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'helpers'.DS.'utilities.php');
	
			$mainframe = &JFactory::getApplication();

			jimport('joomla.filesystem.file');
			$contentSource = $params->get('type','k2');

			if($contentSource == "k2"){
				
				$limit = $params->get('count', 5);
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

				$query .= " WHERE i.published = 1 AND i.trash = 0 AND c.published = 1 AND c.trash = 0";
				
				if(K2_JVERSION=='16'){
					$query .= " AND i.access IN(".implode(',', $user->authorisedLevels()).") ";
				}
				else {
					$query .=" AND i.access<={$aid} ";
				}
				
				if(K2_JVERSION=='16'){
					$query .= " AND c.access IN(".implode(',', $user->authorisedLevels()).") ";
				}
				else {
					$query .=" AND c.access<={$aid} ";
				}

				$query .= " AND ( i.publish_up = ".$db->Quote($nullDate)." OR i.publish_up <= ".$db->Quote($now)." )";

				$query .= " AND ( i.publish_down = ".$db->Quote($nullDate)." OR i.publish_down >= ".$db->Quote($now)." )";
				
				
				// If content source is categories
				if($params->get('k2contentSource') != 'item'){
					if (!is_null($cid)) {
						if (is_array($cid)) {
							if ($params->get('getChildren')) {
								require_once (JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'models'.DS.'itemlist.php');
								$categories = K2ModelItemlist::getCategoryTree($cid);
								$sql = @implode(',', $categories);
								$query .= " AND i.catid IN ({$sql})";

							} else {
								JArrayHelper::toInteger($cid);
								$query .= " AND i.catid IN(".implode(',', $cid).")";
							}

						} else {
							if ($params->get('getChildren')) {
								require_once (JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'models'.DS.'itemlist.php');
								$categories = K2ModelItemlist::getCategoryTree($cid);
								$sql = @implode(',', $categories);
								$query .= " AND i.catid IN ({$sql})";
							} else {
								$query .= " AND i.catid=".(int)$cid;
							}

						}
					}
				}
				
				// If content source is just items
				if($params->get('k2contentSource') == 'item'){
					JArrayHelper::toInteger( $itemid );
					$query .= ' AND (i.id=' . implode( ' OR i.id=', $itemid ) . ')';
				}


				if ($params->get('FeaturedItems') == '0')
				$query .= " AND i.featured != 1";

				if ($params->get('FeaturedItems') == '2')
				$query .= " AND i.featured = 1";

				if ($params->get('videosOnly'))
				$query .= " AND (i.video IS NOT NULL AND i.video!='')";

				if ($ordering == 'comments')
				$query .= " AND comments.published = 1";

				if(K2_JVERSION=='16'){
					if($mainframe->getLanguageFilter()) {
						$languageTag = JFactory::getLanguage()->getTag();
						$query .= " AND c.language IN (".$db->Quote($languageTag).", ".$db->Quote('*').") AND i.language IN (".$db->Quote($languageTag).", ".$db->Quote('*').")";
					}
				}

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
						if($k2ImageSource == "k2item") {

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
					
							if(isset($item->$image)) {
								$item->firstimage = $item->$image;
							} else {
								$item->firstimage = "";
							}

						} elseif ($k2ImageSource == "k2content"){
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
				
				$limit = $params->get('count', 5);
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
			             $categories = K2ModelItemlist::getCategoryTree($id);
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
			           $categories = K2ModelItemlist::getCategoryTree($cid);
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
	}