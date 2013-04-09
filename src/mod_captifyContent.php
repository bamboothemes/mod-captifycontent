<?php
/**
 * @package		##package##
 * @subpackage	##subpackage##
 * @author		##author##
 * @copyright 	##copyright##
 * @license		##license##
 * @version		##version##
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Import the file / foldersystem
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

// Test to see if the default template is a zgf v2 template
$app = JFactory::getApplication();

// Sets variables so we can check if framework or library is present
$jblibrary = JPATH_SITE . '/media/plg_jblibrary/helpers/image.php';
$framework2 = JPATH_ROOT . '/media/zengridframework/helpers/image.php';
$framework1 = JPATH_ROOT . '/templates/' . $app->getTemplate() . '/includes/config.php';

if (file_exists($framework2)) // Checks to see if framework is installed
{
	require_once $framework2;
	$zgf = 1;
	$library = JURI::base(true) . '/media/zengridframework/';
}
else if (file_exists($framework1)) // Checks to see if framework is installed
{
	require_once $framework1;
	$zgf = 1;
	$library = JURI::base(true) . '/media/zengridframework/';
}
else if (file_exists($jblibrary)) // Checks to see if JB Library is installed
{
	require_once $jblibrary;
	$zgf = 0;
	$library = JURI::base(true) . '/media/plg_jblibrary/';
}
else // Else throw an error to let the user know
{
	echo '<div style="font-size:12px;font-family: helvetica neue, arial, sans serif;width:600px;margin:0 auto;background: #f9f9f9;border:1px solid #ddd ;margin-top:100px;padding:40px"><h3>Ooops. It looks like JbLibrary plugin or the Zen Grid Framework plugin is not installed!</h3> <br />Please install it and ensure that you have enabled the plugin by navigating to extensions > plugin manager. <br /><br />JB Library is a free Joomla extension that you can download directly from the <a href="http://www.joomlabamboo.com/joomla-extensions/jb-library-plugin-a-free-joomla-jquery-plugin">Joomla Bamboo website</a>.</div>';
	die();
}

// Include the syndicate functions only once
require_once dirname(__FILE__) . '/helper.php';

$document = JFactory::getDocument();

$modbase = JURI::base(true) . '/modules/mod_captifyContent/';
$module_id = $module->id;
$url = JURI::base();
$count = intval($params->get('count', 5));
$type = $params->get('type', 'content');

// Load CSS & JS
$scripts = $params->get('scripts', 1);

// Image Size and container
$imageDimensions = (int) $params->get('imageDimensions', '1');
$option = $params->get('option', 'crop');
$image_width = str_replace('px', '', $params->get('image_width', '234'));
$image_height = str_replace('px', '', $params->get('image_height', '100'));
$rightMargin = str_replace('px', '', $params->get('rightMargin', '0'));
$bottomMargin = str_replace('px', '', $params->get('bottomMargin', '0'));
$imagesPerRow = (int) $params->get('imagesPerRow', '4');

if ($imagesPerRow < 1)
{
	$imagesPerRow = 1;
}

// $colour = $params->get('colour', 'white');
$background = $params->get('background', 'light-background');

// Fade Effects
$fadeEffect = $params->get('fadeEffect', '1');

// Captify Parameters
$useCaptify = $params->get('useCaptify', '0');
$speed = $params->get('speed', '800');
$speedOut = $params->get('speedOut', '800');
$transition = $params->get('transition', 'fade');
$opacity = $params->get('opacity', '0.8');
$position = $params->get('position', 'bottom');
$displayImages = $params->get('displayImages', 'k2item');
$titleBelow = $params->get('titleBelow', '0');
$contentSource = $params->get('type', 'content');

// Test to see if cache is enabled
$cache = $app->getCfg('caching') ? 1 : 0;

// Load css into the head
if ($scripts)
{
	if (!$cache)
	{
		$document->addStyleSheet($modbase . 'css/captifyContent.css');
		if ($useCaptify == '2')
		{
			$document->addScript($modbase . "js/captify.tiny.min.js");
		}
	}
}

if (($contentSource === "k2") || ($contentSource === "k2category"))
{
	if (isK2Installed())
	{
		$list = ModCCK2ContentHelper::getList($params);
	}
	else
	{
		echo 'K2 is not installed!<br />';

		return;
	}

}
elseif (($contentSource === "content") || ($contentSource === "category") || ($contentSource === "section"))
{
	$list = ModCaptifycontentHelper::getList($params);
}

if (!isset($list) || !count($list))
{
	echo 'Error! Unable to retrieve any Images!';

	return;
}

require JModuleHelper::getLayoutPath('mod_captifyContent');
