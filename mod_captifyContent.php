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

// Import the file / foldersystem
jimport( 'joomla.filesystem.file' );

// Sets variables so we can check if framework or library is present
$jblibrary = JPATH_SITE.DS.'media'.DS.'plg_jblibrary'.DS.'helpers'.DS.'image.php';
$framework = JPATH_SITE.DS.'media'.DS.'zengridframework'.DS.'helpers'.DS.'image.php';

// Checks to see if framework is installed
if (file_exists($framework)){ 
	require_once($framework); 
	$zgf = 1;
	$library = JURI::base(true).'/media/zengridframework/';
} 
	
// Checks to see if JB Library is installed
elseif (file_exists($jblibrary)){ 
	require_once($jblibrary);
	$zgf = 0;
	$library = JURI::base(true).'/media/plg_jblibrary/';
}

// Else throw an error to let the user know
else {
	echo '<div style="font-size:12px;font-family: helvetica neue, arial, sans serif;width:600px;margin:0 auto;background: #f9f9f9;border:1px solid #ddd ;margin-top:100px;padding:40px"><h3>Ooops. It looks like JbLibrary plugin or the Zen Grid Framework plugin is not installed!</h3> <br />Please install it and ensure that you have enabled the plugin by navigating to extensions > plugin manager. <br /><br />JB Library is a free Joomla extension that you can download directly from the <a href="http://www.joomlabamboo.com/joomla-extensions/jb-library-plugin-a-free-joomla-jquery-plugin">Joomla Bamboo website</a>.</div>';
}

// Include the syndicate functions only once
require_once (dirname(__FILE__).DS.'helper.php');

$document =& JFactory::getDocument();
$modbase = JURI::base(true).'/modules/mod_captifyContent/';
$module_id = $module->id;
$url = JURI::base();
$count = intval($params->get('countcc', 5));
$type = $params->get( 'type','category');

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
$scripts = $params->get('scripts', 1);
$list = array();

if (substr(JVERSION, 0, 3) >= '1.6') {	
	// Test to see if cache is enabled
	if ($app->getCfg('caching')) { 
		$cache = 1;
	}
	else {
		$cache = 0;
	}
} else {
	// Test to see if cache is enabled
	if ($mainframe->getCfg('caching')) { 
		$cache = 1;
	}
	else {
		$cache = 0;
	}
}

// Load css into the head
if($scripts) {
	if(!$zgf) {
		if(!$cache) {
			$document->addStyleSheet($modbase.'css/captifyContent.css');	
			if ($useCaptify == '2') { $document->addScript($modbase . "js/captify.tiny.js");}
		}
	}
}

$k2 = JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_k2'.DS.'admin.k2.php';


if((($contentSource == "k2")||($contentSource == "k2category"))&&(file_exists($k2))) {
	$list = modCCK2ContentHelper::getList($params);
} elseif(($contentSource == "content")||($contentSource == "category")||($contentSource == "section")) {
	$list = modCaptifycontentHelper::getList($params);
} else {
	echo 'K2 is not installed!<br />';
}

if (!count($list)) {
	echo 'Error! Unable to retrieve any Images!';
	return;
}

require(JModuleHelper::getLayoutPath('mod_captifyContent'));