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

class SuperBlogger {

	// Load Includes

	function loadHeadIncludes($headIncludes){

		global $loadPluginIncludes;
		$document = & JFactory::getDocument();

		if(!$loadPluginIncludes){
			$loadPluginIncludes=1;
			$document->addCustomTag($headIncludes);
		}
	}

	// Load Module Position
	function loadModulePosition( $position, $style='' ){

		$document	= &JFactory::getDocument();
		$renderer	= $document->loadRenderer('module');
		$params		= array('style'=>$style);

		$contents = '';

		foreach (JModuleHelper::getModules($position) as $mod)  {
			$contents .= $renderer->render($mod, $params);
		}
		return $contents;
	}


	// Word Limiter
	function wordLimiter($str,$limit=100,$end_char='&#8230;') {

		if (trim($str) == '') return $str;
		preg_match('/\s*(?:\S*\s*){'. (int) $limit .'}/', $str, $matches);
		if (strlen($matches[0]) == strlen($str)) $end_char = '';
		return rtrim($matches[0]).$end_char;
	}


	// Text Cleanup
	function cleanupText($string,$allowed_tags){

		//e.g. $allowed_tags = "span,a,b,p,br,img,hr,h1,h2,h3,h4";
		$allowed_tags_array = explode(",",$allowed_tags);
		$allowed_htmltags = array();

		foreach($allowed_tags_array as $tag){
			$allowed_htmltags[] .= "<".$tag.">";
		}

		$allowed_htmltags = implode("",$allowed_htmltags);
		$string = strip_tags($string, $allowed_htmltags);
		return $string;
	}

	// Clean HTML Tag Attributes

	function cleanupAttributes($string,$tag_array,$attr_array) {
		// e.g. cleanupAttributes($string,"img,hr,h1,h2,h3,h4","style,width,height,hspace,vspace,border,class,id");
		$tag_array = explode(",",$tag_array);
		$attr_array =  explode(",",$attr_array);
		$attr = implode("|",$attr_array);

		foreach($tag_array as $tag) {

			preg_match_all("#<($tag) .+?>#", $string, $matches, PREG_PATTERN_ORDER);

			foreach($matches[0] as $match){

				preg_match_all('/('.$attr.')=([\\"\\\']).+?([\\"\\\'])/', $match, $matchesAttr, PREG_PATTERN_ORDER);

				foreach($matchesAttr[0] as $attrToClean){
					$string = str_replace($attrToClean,'',$string);
					$string = preg_replace('|  +|', ' ', $string);
					$string = str_replace(' >','>',$string);
				}
			}
		}

		return $string;
	}


	// Grab the first image in a string
	function getFirstImage($string,$minDimension=80,$maxDimension=140){

		$img = array();

		// find images
		$regex = "#<img.+?>#s";

		if (preg_match_all($regex, $string, $matches, PREG_PATTERN_ORDER) > 0) {

			$img['tag'] = $matches[0][0];
			$srcPattern = '/<img[^>]+src[\\s=\'"]';
			$srcPattern.= '+([^"\'>\\s]+)/is';

			// grab the src of the first image

			if(preg_match($srcPattern,$matches[0][0],$match)){

				$img['src'] = $match[1];
				list($img['width'], $img['height'], $img['type'], $img['attr']) = getimagesize($match[1]);
				/*
				$alt = '';
				if($imgWidth>$maxDimension){
					$img = '<img class="processed" src="'.$src.'" alt="'.$alt.'" />';
				} elseif($imgWidth>$minDimension && $imgWidth<$maxDimension) {
					$img = '<img src="'.$src.'" alt="'.$alt.'" />';
				} else {
					$img = '';
				}
				*/
				return $img;
			}
		}
	}

	// Grab local or remote image and resize/resample it

	function generateResizedImage($url,$riWidth,$riQuality,$riPrefix,$cacheTime,$cacheFolder){

		/* legend:
		si = source image
		ri = resized image
		*/


		// TO DO: add GD check here

		jimport('joomla.filesystem.file');
		global $app;

		$site_absolutepath = JPATH_SITE;
		$site_httppath = JURI::base();

		// Define the directory separator
		$ds = (strtoupper(substr(PHP_OS,0,3)=='WIN')) ? '\\' : '/';

		// Cache
		$cacheTime = $cacheTime*60;
		$cacheFolderPath = $site_absolutepath.$ds.str_replace('/',$ds,$cacheFolder);
		if(file_exists($cacheFolderPath) && is_dir($cacheFolderPath)){
			// all OK
		} else {
			mkdir($cacheFolderPath);
		}

		// Get the remote filename

		$grabUrl = parse_url($url);
		$grabUrlPath = explode("/",$grabUrl['path']);
		$grabUrlPath = array_reverse($grabUrlPath);

		// Define source and target images
		$siFilename = 'temp_sb_'.$grabUrlPath[0];
		$siPath = $cacheFolderPath.$ds.$siFilename;

		//$riPrefix = 'cache_sb_';
		$riFilename = $riPrefix.substr(md5($siFilename),0,10).'.jpg';
		$riPath = $cacheFolderPath.$ds.$riFilename;
		$riHttpPath = $site_httppath.$cacheFolder.$ds.$riFilename;

		// Check if thumb image exists otherwise create it
		if(file_exists($riPath) && is_readable($riPath) && (filemtime($riPath)+$cacheTime) > time()){
			// do nothing
		} else {
			// Grab the local or remote image
			//$siTemp = imagecreatefromstring(file_get_contents($url));
			$siTemp = imagecreatefromstring(JFile::read($url));

			if ($siTemp !== false) {

				// create source image locally
				imagejpeg($siTemp,$siPath);

				// grab local source image details
				list($siWidth, $siHeight, $siType) = getimagesize($siPath);

				// create an image resource for the original
				$source = imagecreatefromjpeg($siPath);

				// create an image resource for the resized image

				if($riWidth>=$siWidth){
					$riWidth = $siWidth;
					$riHeight = $siHeight;
				} else {
					$riHeight = $riWidth*$siHeight/$siWidth;
				}

				$resized = imagecreatetruecolor($riWidth,$riHeight);

				// create the resized copy
				imagecopyresampled($resized, $siTemp, 0, 0, 0, 0, $riWidth, $riHeight, $siWidth, $siHeight);

				// save the resized copy
				imagejpeg($resized,$riPath,$riQuality);

				// delete temp source
				unlink($siPath);

				// cleanup resources
				imagedestroy($source);
				imagedestroy($resized);
			}
		}

		// output

		return $riHttpPath;
	}

	// Twitter updates
	function getTwitterUpdates($twitterUsername,$siteTweetsLimit,$cacheTime,$pluginName)
	{
		jimport('joomla.filesystem.file');
		global $app;

		$site_absolutepath = JPATH_SITE;

		// Assign some names

		$ds = (strtoupper(substr(PHP_OS,0,3)=='WIN')) ? '\\' : '/';
		$cacheFolderPath = $site_absolutepath.$ds.'cache'.$ds.$pluginName;
		$twitterXMLFile = $cacheFolderPath.$ds.'cache_sb_'.$twitterUsername.'.xml';

		// Check cache folder

		$cacheTime = $cacheTime*60;

		if(file_exists($cacheFolderPath) && is_dir($cacheFolderPath)){
			// all OK
		} else {
			mkdir($cacheFolderPath);
		}

		if(file_exists($twitterXMLFile) && is_readable($twitterXMLFile) && (filemtime($twitterXMLFile)+($cacheTime)) > time()){
			// XML file is cached
		} else {
			//$fetchXML = file_get_contents('http://twitter.com/statuses/user_timeline.xml?screen_name='.$twitterUsername);
			$fetchXML = JFile::read('http://twitter.com/statuses/user_timeline.xml?screen_name='.$twitterUsername);
			//file_put_contents($twitterXMLFile,$fetchXML);
			JFile::write($twitterXMLFile,$fetchXML);
		}

		//$twitterXML = new SimpleXMLElement(file_get_contents($twitterXMLFile));
		//$twitterXML = new SimpleXMLElement(JFile::read($twitterXMLFile));
		$twitterXML = new JSimpleXML;
		$twitterXML->loadFile($twitterXMLFile);

		foreach($twitterXML->document->status as $status){
			$tweets['date'][] = $status->created_at[0]->data();
			$tweets['id'][] = $status->id[0]->data();
			$tweets['text'][] = $status->text[0]->data();
		}

		$twitterName = $twitterXML->document->getElementByPath('status/user/name');
		$siteTweets['name'] = $twitterName->data();

		/*
		foreach($twitterXML as $status){
			foreach($status->created_at as $statusCreated) $tweets['date'][] = $statusCreated;
			foreach($status->id as $statusId) $tweets['id'][] = $statusId;
			foreach($status->text as $statusText) $tweets['text'][] = $statusText;
		}


		$twitterName = $twitterXML->xpath('/statuses/status/user/name');
		$siteTweets['name'] = $twitterName[0];
		*/

		for ($j = 0; $j < $siteTweetsLimit; $j++){
			$siteTweetsObj[$j] = new JObject;
			$siteTweetsObj[$j]->date = $tweets['date'][$j];
			$siteTweetsObj[$j]->id = $tweets['id'][$j];
			$siteTweetsObj[$j]->url = 'http://twitter.com/'.$twitterUsername.'/status/'.$siteTweetsObj[$j]->id;
			$siteTweetsObj[$j]->text = htmlentities($tweets['text'][$j], ENT_QUOTES, 'utf-8');
			// Convert URLs in text to links

			$pattern = "@\b(https?://)?(([0-9a-zA-Z_!~*'().&=+$%-]+:)?[0-9a-zA-Z_!~*'().&=+$%-]+\@)?(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-zA-Z_!~*'()-]+\.)*([0-9a-zA-Z][0-9a-zA-Z-]{0,61})?[0-9a-zA-Z]\.[a-zA-Z]{2,6})(:[0-9]{1,4})?((/[0-9a-zA-Z_!~*'().;?:\@&=+$,%#-]+)*/?)@";

			$siteTweetsObj[$j]->text = preg_replace($pattern, '<a target="_blank" href="\0">\0</a>', $siteTweetsObj[$j]->text);
		}

		$siteTweets['tweets'] = $siteTweetsObj;

		// Output
		return $siteTweets;
	}

	// Path overrides
	function getTemplatePath($pluginName,$file)
	{
		global $app;
		$p = new JObject;

		if (file_exists(JPATH_SITE . '/templates/' . $app->getTemplate() . '/html/' . $pluginName . '/' . $file))
		{
			$p->file = JPATH_SITE . '/templates/' . $app->getTemplate() . '/html/' . $pluginName . '/' . $file;
			$p->http = JURI::base() . "templates/" . $app->getTemplate() . "/html/{$pluginName}/{$file}";
		}
		else
		{
			$p->file = JPATH_SITE . '/plugins/content/' . $pluginName . '/tmpl/' . $file;
			$p->http = JURI::base() . "plugins/content/{$pluginName}/tmpl/{$file}";
		}
		return $p;
	}
} // end class
