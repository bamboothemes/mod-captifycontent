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
			$catids = $params->get('catid');
			$access = !JComponentHelper::getParams('com_content')->get('show_noauth');
			$authorised = JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id'));
			$categories = JModel::getInstance('Categories', 'ContentModel', array('ignore_request' => true));
			$categories->setState('params', $appParams);
			$levels = $params->get('levels', 1) ? $params->get('levels', 1) : 9999;
			$categories->setState('list.start', 0);
			$categories->setState('list.limit', (int) $params->get('count', 5));
			$categories->setState('filter.get_children', $levels);
			$categories->setState('filter.published', 1);
			$categories->setState('filter.access', $access);
			$categories->setState('filter.parentId', $catids);
			$categories->setState('category.id',$catids);
			//$items = $categories->getItems(true);
			
			foreach ( $items as $item )
			{
				//$item->slug = $item->id.':'.$item->alias;
				//$item->catslug = $item->catid ? $item->catid .':'.$item->category_alias : $item->catid;

				if ($access || in_array($item->access, $authorised)) {

					//$item->link = JRoute::_(ContentHelperRoute::getCategoryRoute($item->id).'&layout=blog');
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

					$item->link = JRoute::_('index.php?option=com_users&view=login&Itemid='.$Itemid);
					}
		
				
			}
			//print_r($items);
			return $items;	
			
			
			
	}
		else if
		($type == "content") {
	
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
				foreach ( $items as $item )
				{
					$item->slug = $item->id.':'.$item->alias;
					$item->catslug = $item->catid ? $item->catid .':'.$item->category_alias : $item->catid;

					if ($access || in_array($item->access, $authorised)) {

						$item->link = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catslug));
						$item->catlink = JRoute::_(ContentHelperRoute::getCategoryRoute($item->catslug).'&layout=blog');
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

						$item->link = JRoute::_('index.php?option=com_users&view=login&Itemid='.$Itemid);
						}
			
					$item->text = $item->introtext;
				}

				return $items;
			}
		}

	

	

}



