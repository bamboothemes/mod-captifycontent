<?php
/**
* @version		$Id: mod_sections.php 10381 2008-06-01 03:35:53Z pasamio $
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

// Include the syndicate functions only once
require_once (dirname(__FILE__).DS.'helper.php');

$document =& JFactory::getDocument();
$library = JURI::base(true).'/media/plg_jblibrary/';
$modbase = JURI::base(true).'/modules/mod_captifyContent/';
$module_id = $module->id;
$url = JURI::base();
$count = intval($params->get('countcc', 5));
$type = $params->get( 'type','category');
// Load CSS & JS
$loadCSS = $params->get('loadCSS','head');
$loadJS = $params->get('loadJS','head');
// Image Size and container
$imageDimensions = (int)$params->get( 'imageDimensions', '1');
$option = $params->get( 'option', 'crop');
$image_width = str_replace('px', '', $params->get('image_width','234'));
$image_height = str_replace('px', '', $params->get('image_height','100'));
$rightMargin = str_replace('px', '', $params->get('rightMargin','0'));
$bottomMargin = str_replace('px', '', $params->get('bottomMargin','0'));
$imagesPerRow = (int)$params->get('imagesPerRow', '4');
if ($imagesPerRow < 1) $imagesPerRow = 1;
//$colour = $params->get('colour', 'white');
$background = $params->get('background', 'light-background');
// Fade Effects
$fadeEffect = $params->get('fadeEffect','1');
// Captify Parameters
$useCaptify = $params->get( 'useCaptify','0');
$speed = $params->get( 'speed', '800');
$speedOut = $params->get( 'speedOut', '800');
$transition = $params->get( 'transition', 'fade');
$opacity = $params->get( 'opacity', '0.8');
$position = $params->get( 'position', 'bottom');
$displayImages = $params->get('displayImages','k2item');
$titleBelow = $params->get('titleBelow','0');
$contentSource = $params->get('type','article');

// Load css into the head
if($loadCSS== 'head') $document->addStyleSheet($modbase.'css/captifyContent.css');

if (($useCaptify == '2')&&($loadJS == 'head')) { $document->addScript($modbase . "js/captify.tiny.js");}

if($contentSource == "k2" or $contentSource == "k2category")
{
	$list = modCCK2ContentHelper::getList($params);
}
else
{
	$list = modCaptifycontentHelper::getList($params);
}

if (!count($list)) {
	echo 'Error! Unable to retrieve any Images!';
	return;
}

require(JModuleHelper::getLayoutPath('mod_captifyContent'));
