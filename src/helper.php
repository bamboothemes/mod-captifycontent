<?php

/**
 * @version    $Id: helper.php 10616 2008-08-06 11:06:39Z hackwar $
 * @package    Joomla
 * @copyright  Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license    GNU/GPL, see LICENSE.php
 *
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

$app = JFactory::getApplication();

if (version_compare(JVERSION, '3.0', '<'))
{
	class ZenJModel extends JModel {};
}
else
{
	class ZenJModel extends JModelLegacy {};
}

$isK2GE260 = isK2GE260();


if (substr(JVERSION, 0, 3) >= '1.6')
{
	/*********************************************************************************************************************
	 *
	* Helper for Joomla 1.7 +
	*
	**********************************************************************************************************************/

	jimport('joomla.application.component.model');

	$com_path = JPATH_SITE . '/components/com_content';
	require_once $com_path . '/router.php';
	require_once $com_path . '/helpers/route.php';

	jimport('joomla.application.component.model');

	ZenJModel::addIncludePath($com_path . '/models', 'ContentModel');

	/**
	 * Content Helper Class
	 *
	 * @package  Mod_CaptifyContent
	 * @since    1.0
	*/
	abstract class ModCaptifycontentHelper
	{
		public static function getList(&$params)
		{
			$type = $params->get('type', 'content');
			$app = JFactory::getApplication();
			$appParams = $app->getParams();

			if ($type === "category")
			{
				$catids = (array)$params->get('c_catid', '0');
				$access = !JComponentHelper::getParams('com_content')->get('show_noauth');
				$authorised = JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id'));
				$categories = ZenJModel::getInstance('Categories', 'ContentModel', array('ignore_request' => true));
				$catCount = $params->get('count', 5);
				$levels = (int)($params->get('c_levels', 1) ? $params->get('c_levels', 1) : 9999);
				$show_child_category_articles = (bool)$params->get('c_show_child_category_articles', 0);
				$categories->setState('filter.published', '1');
				$categories->setState('filter.access', $access);


				if ($catids && $show_child_category_articles && $levels > 0)
				{
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
								if ($condition)
								{
									$additional_catids[] = $category->id;
								}

							}
						}
					}

					$catids = array_unique(array_merge($catids, $additional_catids));
				}

				$items = array();
				$jcategory = JCategories::getInstance('Content');

				if (is_array($catids))
				{
					foreach ($catids as $catid)
					{
						$catitem = $jcategory->get($catid);

						if (!($catitem->published))
						{
							continue;
						}

						$catitem->slug = $catitem->id . ':' . $catitem->alias;
						$catitem->catslug = $catitem->id ? $catitem->id . ':' . $catitem->alias : $catitem->id;

						if ($access || in_array($catitem->access, $authorised))
						{

							$catitem->link = JRoute::_(ContentHelperRoute::getCategoryRoute($catitem->id) . '&layout=blog');
						}
						else
						{
							// Angie Fixed Routing
							$app	= JFactory::getApplication();
							$menu	= $app->getMenu();
							$menuitems	= $menu->getItems('link', 'index.php?option=com_users&view=login');

							if (isset($menuitems[0]))
							{
								$Itemid = $menuitems[0]->id;
							}
							else if (JRequest::getInt('Itemid') > 0)
							{ // use Itemid from requesting page only if there is no existing menu
								$Itemid = JRequest::getInt('Itemid');
							}

							$catitem->link = JRoute::_('index.php?option=com_users&view=login&Itemid=' . $Itemid);
						}

						$catitem->image = "";

						$items[] = $catitem;
					}
				}

				return $items;
			}
			else if ($type == "content")
			{
				$catids = $params->get('catid');

				$articles = ZenJModel::getInstance('Articles', 'ContentModel', array('ignore_request' => true));
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
				if ($catids)
				{
					if ($params->get('show_child_category_articles', 0) && (int) $params->get('levels', 0) > 0)
					{
						// Get an instance of the generic categories model
						$categories = ZenJModel::getInstance('Categories', 'ContentModel', array('ignore_request' => true));
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

							if ($items)
							{
								foreach($items as $category)
								{
									$condition = (($category->level - $categories->getParent()->level) <= $levels);
									if ($condition)
									{
										$additional_catids[] = $category->id;
									}
								}
							}
						}

						$catids = array_unique(array_merge($catids, $additional_catids));
					}
					$articles->setState('filter.category_id', $catids);
				}

				if ($artids)
				{
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

				foreach ($items as $item)
				{

					$item->slug = $item->id. ':' . $item->alias;
					$item->catslug = $item->catid ? $item->catid . ':' . $item->category_alias : $item->catid;

					if ($access || in_array($item->access, $authorised))
					{
						$item->link = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catslug));
						$item->catlink = JRoute::_(ContentHelperRoute::getCategoryRoute($item->catslug) . '&layout=blog');
					}
					else
					{
						// Angie Fixed Routing
						$app	= JFactory::getApplication();
						$menu	= $app->getMenu();
						$menuitems	= $menu->getItems('link', 'index.php?option=com_users&view=login');

						if (isset($menuitems[0]))
						{
							$Itemid = $menuitems[0]->id;
						} else if (JRequest::getInt('Itemid') > 0)
						{
							// use Itemid from requesting page only if there is no existing menu
							$Itemid = JRequest::getInt('Itemid');
						}

						$item->link = JRoute::_('index.php?option=com_users&view=login&Itemid=' . $Itemid);
					}

					$item->text = $item->introtext;
				}
				return $items;
			}
		}
	}

}
else
{

	/*********************************************************************************************************************
	 *
	* Helper for Joomla 1.5 +
	*
	**********************************************************************************************************************/

	require_once JPATH_SITE . '/components/com_content/helpers/route.php';

	Class ModCaptifycontentHelper
	{
		public static function getList(&$params)
		{

			global $app;

			$type = $params->get('type', 'content');

			if ($type == "section")
			{

				$sectionid     = $params->get('sectionid', '');
				$ordering      = $params->get('secOrdering' , 'order');

				$db            = JFactory::getDBO();
				$user          = JFactory::getUser();

				$count         = intval($params->get('count', 20));
				$contentConfig = JComponentHelper::getParams('com_content');

				$access        = !$contentConfig->get('shownoauth');
				$gid           = $user->get('aid', 0);
				$now           = date('Y-m-d H:i:s', time() + $app->getCfg('offset') * 60 * 60);
				$nullDate      = $db->getNullDate();
			}

			if ($type == "category")
			{

				$db            = JFactory::getDBO();
				$user          = JFactory::getUser();

				$ordering      = $params->get('ordering' , 'order');
				$count         = intval($params->get('count', 5));

				$contentConfig = JComponentHelper::getParams('com_content');
				$catid         = $params->get('catid');
				$access        = !$contentConfig->get('shownoauth');
				$gid           = $user->get('aid', 0);
				$now           = date('Y-m-d H:i:s', time() + $app->getCfg('offset') * 60 * 60);
				$nullDate      = $db->getNullDate();
			}

			// Get K2 version
			if (isK2GE260())
			{
				// public = 1
				$gid += 1;
			}

			if ($type == "section")
			{

				if ($sectionid)
				{
					if (is_array($sectionid))
					{
						JArrayHelper::toInteger($sectionid);
						$secCondition = ' AND (a.id=' . implode(' OR a.id=', $sectionid) . ')';
					} else
					{
						$ids = explode(', ', $sectionid);
						JArrayHelper::toInteger($ids);
						$secCondition = ' AND (a.id=' . implode(' OR ca.id=', $ids) . ')';
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
						($access ? ' AND b.access <= ' . (int) $gid : '') .
						' AND (b.publish_up = ' . $db->Quote($nullDate) . ' OR b.publish_up <= ' . $db->Quote($now) . ')' .
						' AND (b.publish_down = ' . $db->Quote($nullDate) . ' OR b.publish_down >= ' . $db->Quote($now) . ')' .
						' WHERE a.scope = "content"' .
						' AND a.published = 1' .
						($access ? ' AND a.access <= ' . (int) $gid : '') .
						($sectionid ? $secCondition : '') .
						' GROUP BY a.id ' .
						' HAVING COUNT(b.id) > 0' .
						' ORDER BY ' . $ordering;

				$db->setQuery($query, 0, $count);
				$rows = $db->loadObjectList();

				return $rows;

			}
			else if ($type == "category")
			{
				if ($catid)
				{
					if (is_array($catid))
					{
						JArrayHelper::toInteger($catid);
						$catCondition = ' AND (a.id=' . implode(' OR a.id=', $catid) . ')';
					}
					else
					{
						$ids = explode(', ', $catid);
						JArrayHelper::toInteger($ids);
						$catCondition = ' AND (a.id=' . implode(' OR a.id=', $ids) . ')';
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
						($access ? ' AND b.access <= ' . (int) $gid : '') .
						' AND (b.publish_up = "' . $nullDate. '" OR b.publish_up <= "' . $now. '")' .
						' AND (b.publish_down = "' . $nullDate. '" OR b.publish_down >= "' . $now. '")' .
						' WHERE a.published = 1' .
						($catid ? $catCondition : '') .
						($access ? ' AND a.access <= ' . $gid : '') .
						' GROUP BY a.id ' .
						' ORDER BY ' . $ordering;

				$db->setQuery($query, 0, $count);

				$rows = $db->loadObjectList();

				foreach ($rows as $row)
				{
					$row->link = JRoute::_(ContentHelperRoute::getCategoryRoute($row->id, '') . '&layout=blog');
				}

				return $rows;

			}
			else if ($type == "content")
			{
				$db            = JFactory::getDBO();
				$user          = JFactory::getUser();
				$userId        = (int) $user->get('id');
				$count         = (int) $params->get('count', 5);
				$catid         = $params->get('catid');
				$artid         = $params->get('artid');
				$show_front    = $params->get('show_front', 1);
				$aid           = $user->get('aid', 0);

				$contentConfig = JComponentHelper::getParams('com_content');
				$access        = !$contentConfig->get('show_noauth');

				$nullDate      = $db->getNullDate();
				$date          = JFactory::getDate();

				if (version_compare(JVERSION, '3.0', '<'))
				{
					$now = $date->toMySQL();
				}
				else
				{
					$now = $date->toSql();
				}

				// Get K2 version
				if (isK2GE260())
				{
					// public = 1
					$aid += 1;
				}

				$where	= 'a.state = 1'
						. ' AND (a.publish_up = ' . $db->Quote($nullDate) . ' OR a.publish_up <= ' . $db->Quote($now) . ')'
								. ' AND (a.publish_down = ' . $db->Quote($nullDate) . ' OR a.publish_down >= ' . $db->Quote($now) . ')'
										;

				// User Filter
				switch ($params->get('user_id'))
				{

					case 'by_me':
						$where .= ' AND (created_by = ' . (int) $userId . ' OR modified_by = ' . (int) $userId . ')';
						break;

					case 'not_me':
						$where .= ' AND (created_by <> ' . (int) $userId . ' AND modified_by <> ' . (int) $userId . ')';
						break;
				}

				// Ordering
				switch ($params->get('ordering'))
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
					if (is_array($artid))
					{
						$artCondition = ' AND (a.id IN (' . implode(', ', $artid) . '))';
					} else
					{
						$artCondition = ' AND (a.id = ' . $artid. ')';
					}
				}

				if ($catid)
				{
					if (is_array($catid))
					{
						JArrayHelper::toInteger($catid);
						$catCondition = ' AND (cc.id=' . implode(' OR cc.id=', $catid) . ')';

						if ($artid){
							$catCondition = ' OR (cc.id=' . implode(' OR cc.id=', $catid) . ')';
						}
					}
					else
					{
						$ids = explode(', ', $catid);
						JArrayHelper::toInteger($ids);
						$catCondition = ' AND (cc.id=' . implode(' OR cc.id=', $ids) . ')';

						if ($artid){
							$catCondition = ' OR (cc.id=' . implode(' OR cc.id=', $ids) . ')';
						}
					}
				}

				// Content Items only
				$query = 'SELECT a.*, ' .
						' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(":", a.id, a.alias) ELSE a.id END as slug, ' .
						' CASE WHEN CHAR_LENGTH(cc.alias) THEN CONCAT_WS(":", cc.id, cc.alias) ELSE cc.id END as catslug' .
						' FROM #__content AS a' .
						($show_front == '0' ? ' LEFT JOIN #__content_frontpage AS f ON f.content_id = a.id' : '') .
						' INNER JOIN #__categories AS cc ON cc.id = a.catid' .
						' INNER JOIN #__sections AS s ON s.id = a.sectionid' .
						' WHERE ' . $where . ' AND s.id > 0' .
						($access ? ' AND a.access <= ' . (int) $aid. ' AND cc.access <= ' . (int) $aid. ' AND s.access <= ' . (int) $aid : '') .
						($artid ? $artCondition : '') .
						($catid ? $catCondition : '') .
						($show_front == '0' ? ' AND f.content_id IS NULL ' : '') .
						' AND s.published = 1' .
						' AND cc.published = 1' .
						' ORDER BY ' . $ordering;

				$db->setQuery($query, 0, $count);
				$rows = $db->loadObjectList();

				$i		= 0;
				$lists	= array();

				foreach ($rows as $row)
				{
					if ($row->access <= $aid)
					{
						$lists[$i]->link = JRoute::_(ContentHelperRoute::getArticleRoute($row->slug, $row->catslug, $row->sectionid));
					}
					else
					{
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



/*********************************************************************************************************************
 *
* Helper for K2 v2.5.x
*
**********************************************************************************************************************/


class ModCCK2ContentHelper
{
	public static function getList(&$params)
	{
		require_once JPATH_SITE . '/components/com_k2/helpers/route.php';
		require_once JPATH_SITE . '/components/com_k2/helpers/utilities.php';

		$app = JFactory::getApplication();

		jimport('joomla.filesystem.file');
		$contentSource = $params->get('type', 'k2');

		if ($contentSource == "k2")
		{

			$limit = $params->get('count', 2);
			$cid = $params->get('k2catid', NULL);
			$ordering = $params->get('orderingK2');
			$limitstart = JRequest::getInt('limitstart');
			$user = JFactory::getUser();
			$aid = $user->get('aid');
			$db = JFactory::getDBO();
			$jnow = JFactory::getDate();
			$nullDate = $db->getNullDate();
			$itemid = $params->get('itemid', '');

			if (version_compare(JVERSION, '3.0', '<'))
			{
				$now = $jnow->toMySQL();
			}
			else
			{
				$now = $jnow->toSql();
			}

			// Get K2 version
			if (isK2GE260())
			{
				// public = 1
				$aid += 1;
			}

			$query = "SELECT i.*, c.name AS categoryname,c.id AS categoryid, c.alias AS categoryalias, c.params AS categoryparams";

			if ($ordering == 'best')
			{
				$query .= ", (r.rating_sum/r.rating_count) AS rating";
			}

			$query .= " FROM #__k2_items as i LEFT JOIN #__k2_categories c ON c.id = i.catid";

			if ($ordering == 'best')
			{
				$query .= " LEFT JOIN #__k2_rating r ON r.itemID = i.id";
			}

			$query .= " WHERE i.published = 1 AND i.trash = 0 AND c.published = 1 AND c.trash = 0";

			if (version_compare(JVERSION, '1.6', '>='))
			{
				$query .= " AND i.access IN(" .implode(', ', $user->authorisedLevels()) .") ";
			}
			else
			{
				$query .= " AND i.access<={$aid} ";
			}

			if (version_compare(JVERSION, '1.6', '>='))
			{
				$query .= " AND c.access IN(" .implode(', ', $user->authorisedLevels()) .") ";
			}
			else
			{
				$query .= " AND c.access<={$aid} ";
			}

			$query .= " AND (i.publish_up = " . $db->Quote($nullDate) ." OR i.publish_up <= " . $db->Quote($now) .")";

			$query .= " AND (i.publish_down = " . $db->Quote($nullDate) ." OR i.publish_down >= " . $db->Quote($now) .")";


			// If content source is categories
			if ($params->get('k2contentSource') != 'item')
			{
				if (!is_null($cid))
				{
					if (is_array($cid))
					{
						if ($params->get('getChildren'))
						{
							require_once JPATH_SITE . '/components/com_k2/models/itemlist.php';
							$categories = K2ModelItemlist::getCategoryTree($cid);
							$sql = @implode(', ', $categories);
							$query .= " AND i.catid IN ({$sql})";

						}
						else
						{
							JArrayHelper::toInteger($cid);
							$query .= " AND i.catid IN(" .implode(', ', $cid) .")";
						}

					}
					else
					{
						if ($params->get('getChildren'))
						{
							require_once JPATH_SITE . '/components/com_k2/models/itemlist.php';
							$categories = K2ModelItemlist::getCategoryTree($cid);
							$sql = @implode(', ', $categories);
							$query .= " AND i.catid IN ({$sql})";
						}
						else
						{
							$query .= " AND i.catid= " . (int)$cid;
						}

					}
				}
			}

			// If content source is just items
			if ($params->get('k2contentSource') == 'item')
			{
				if (!empty($itemid))
				{
					if (is_array($itemid))
					{
						JArrayHelper::toInteger($itemid);
						$query .= ' AND (i.id=' . implode(' OR i.id=', $itemid) . ')';
					}
					else
					{
						$query .= ' AND (i.id=' . $itemid . ')';
					}
				}
			}


			if ($params->get('itemFilter') == 'hide')
			{
				$query .= " AND i.featured != 1";
			}

			if ($params->get('itemFilter') == 'feat')
			{
				$query .= " AND i.featured = 1";
			}

			if ($params->get('videosOnly'))
			{
				$query .= " AND (i.video IS NOT NULL AND i.video!='')";
			}

			if ($ordering == 'comments')
			{
				$query .= " AND comments.published = 1";
			}

			if (version_compare(JVERSION, '1.6', '>='))
			{
				if ($app->getLanguageFilter())
				{
					$languageTag = JFactory::getLanguage()->getTag();
					$query .= " AND c.language IN (" . $db->Quote($languageTag) .", " . $db->Quote('*') .") AND i.language IN (" . $db->Quote($languageTag) .", " . $db->Quote('*') .")";
				}
			}

			switch ($ordering)
			{
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
					{
						$orderby = 'i.featured_ordering';
					}
					else
					{
						$orderby = 'i.ordering';
					}
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

			$query .= " ORDER BY " . $orderby;
			$db->setQuery($query, 0, $limit);
			$items = $db->loadObjectList();

			require_once JPATH_SITE . '/components/com_k2/models/item.php';
			$model = new K2ModelItem;

			require_once JPATH_SITE . '/components/com_k2/helpers/route.php';


			if (count($items))
			{
				$k2ImageSource = $params->get('displayImages', 'k2item');

				foreach ($items as $item)
				{
					// Images
					if ($k2ImageSource == "k2item")
					{
						if (JFile::exists(JPATH_SITE . '/media/k2/items/src/' . md5("Image" . $item->id) . ' .jpg'))
							$item->imageOriginal = 'media/k2/items/src/' .md5("Image" . $item->id) . ' .jpg';

						if (JFile::exists(JPATH_SITE . '/media/k2/items/cache/' . md5("Image" . $item->id) . '_XS.jpg'))
							$item->imageXSmall = 'media/k2/items/cache/' .md5("Image" . $item->id) . '_XS.jpg';


						if (JFile::exists(JPATH_SITE . '/media/k2/items/cache/' . md5("Image" . $item->id) . '_S.jpg'))
							$item->imageSmall = 'media/k2/items/cache/' .md5("Image" . $item->id) . '_S.jpg';


						if (JFile::exists(JPATH_SITE . '/media/k2/items/cache/' . md5("Image" . $item->id) . '_M.jpg'))
							$item->imageMedium = 'media/k2/items/cache/' .md5("Image" . $item->id) . '_M.jpg';


						if (JFile::exists(JPATH_SITE . '/media/k2/items/cache/' . md5("Image" . $item->id) . '_L.jpg'))
							$item->imageLarge = 'media/k2/items/cache/' .md5("Image" . $item->id) . '_L.jpg';


						if (JFile::exists(JPATH_SITE . '/media/k2/items/cache/' . md5("Image" . $item->id) . '_XL.jpg'))
							$item->imageXLarge = 'media/k2/items/cache/' .md5("Image" . $item->id) . '_XL.jpg';


						if (JFile::exists(JPATH_SITE . '/media/k2/items/cache/' . md5("Image" . $item->id) . '_Generic.jpg'))
							$item->imageGeneric = 'media/k2/items/cache/' .md5("Image" . $item->id) . '_Generic.jpg';


						$image = 'image' . $params->get('itemImageSize');

						if (isset($item->$image))
						{
							$item->firstimage = $item->$image;
						}
						else
						{
							$item->firstimage = "";
						}

					}
					elseif ($k2ImageSource == "k2content")
					{
						$item->firstimage = "";
					}

					$item->numOfComments = $model->countItemComments($item->id);

					// Read more link

					$item->link = urldecode(JRoute::_(K2HelperRoute::getItemRoute($item->id. ':' . urlencode($item->alias), $item->catid . ':' . urlencode($item->categoryalias))));

					// Item text
					$item->text = $item->introtext;
					$rows[] = $item;
				}

				return $rows;
			}
		}
		else if ($contentSource == "k2category")
		{
			$limit = $params->get('count', 5);
			$cid = $params->get('k2catid', NULL);
			$ordering = $params->get('orderingK2');
			$limitstart = JRequest::getInt('limitstart');
			$user = JFactory::getUser();
			$db = JFactory::getDBO();
			$jnow = JFactory::getDate();
			$nullDate = $db->getNullDate();

			if (version_compare(JVERSION, '3.0', '<'))
			{
				$now = $jnow->toMySQL();
			}
			else
			{
				$now = $jnow->toSql();
			}

			// Fix some differences between J1.5 and J1.7
			$version = new JVersion();
			$version = (float)$version->RELEASE;
			if ($version >= 1.7)
			{
				// Get aid from ACL
				$aid = max($user->getAuthorisedViewLevels());

				if (is_array($cid) && count($cid) == 1)
				{
					$cid = $cid[0];
				}
			}
			else
			{
				$aid = $user->get('aid');
			}

			// Get K2 version
			if (isK2GE260())
			{
				// public = 1
				$aid += 1;
			}

			$query = "SELECT c.*";
			$query .= " FROM #__k2_categories as c";
			$query .= " WHERE c.published = 1 AND c.access <= {$aid} AND c.trash = 0";

			if (!is_null($cid))
			{
				if (is_array($cid))
				{
					if ($params->get('getChildren'))
					{
						require_once JPATH_SITE . '/components/com_k2/models/itemlist.php';
						$allChildren = array();

						foreach ($cid as $id)
						{
							$categories = K2ModelItemlist::getCategoryTree($id);
							$categories[] = $id;
							$categories = @array_unique($categories);
							$allChildren = @array_merge($allChildren, $categories);
						}
						$allChildren = @array_unique($allChildren);
						$sql = @implode(', ', $allChildren);
						$query .= " AND c.id IN ({$sql})";
					}
					else
					{
						$query .= " AND c.id IN(" .implode(', ', $cid) .")";
					}
				}
				else
				{
					if ($params->get('getChildren'))
					{
						require_once JPATH_SITE . '/components/com_k2/models/itemlist.php';
						$categories = K2ModelItemlist::getCategoryTree($cid);
						$categories[] = $cid;
						$categories = @array_unique($categories);
						$sql = @implode(', ', $categories);
						$query .= " AND c.id IN ({$sql})";
					}
					else
					{
						$query .= " AND c.id={$cid}";
					}
				}
			}

			switch ($ordering)
			{
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

			$query .= " ORDER BY " . $orderby;

			$db->setQuery($query, 0, $limit);
			$items = $db->loadObjectList();

			require_once JPATH_SITE . '/components/com_k2/models/item.php';
			$model = new K2ModelItem;

			require_once JPATH_SITE . '/components/com_k2/helpers/route.php';

			if (count($items))
			{
				$k2ImageSource = $params->get('displayImages', 'k2item');
				foreach ($items as $item)
				{
					// Images
					if (JFile::exists(JPATH_SITE . '/media/k2/categories/' . $item->image))
					{
						$item->image = 'media/k2/categories/' . $item->image;
					}
					else
					{
						$item->image = "";
					}

					// Read more link
					$item->link = urldecode(JRoute::_(K2HelperRoute::getCategoryRoute($item->id . ':' . urlencode($item->alias))));

					// Item text
					$item->title = $item->name;
					$item->description = $item->description;

					$rows[] = $item;
				}

				return $rows;
			}
		}
	}
}

function isK2Installed()
{
	jimport( 'joomla.filesystem.file' );

	if (defined('K2_JVERSION'))
	{
		return true;
	}

	if (version_compare(JVERSION, '3.0', '>='))
	{
		jimport('joomla.filesystem.files');
	}

	return JFile::exists(JPATH_ADMINISTRATOR . '/components/com_k2/admin.k2.php')
		|| JFile::exists(JPATH_ADMINISTRATOR . '/components/com_k2/k2.php');
}

// Check if K2 is >= 2.6.0
function isK2GE260()
{
	static $result = null;

	if ($result === null)
	{
		$k2Path = JPATH_ADMINISTRATOR . '/components/com_k2/';
		$result = false;

		if (version_compare(JVERSION, '1.6', '>='))
		{
			jimport('joomla.filesystem.file');

			// Check if K2 manifest file exists
			// K2 versions, older than 2.6.1 have missed manifest file
			if (JFile::exists($k2Path . 'k2.xml'))
			{
				$installer = new JInstaller;
				$installer->setPath('source', $k2Path);
				$manifest = $installer->getManifest();

				$result = version_compare($manifest->version, '2.6.0', '>=');
			}
		}
	}

	return $result;
}
