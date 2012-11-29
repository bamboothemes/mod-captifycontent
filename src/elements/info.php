<?php
/**
* @id $Id$
* @author  mod_slideshow3.php
* @package  JB Slideshow3
* @copyright Copyright (C) 2006 - 2010 Joomla Bamboo. http://www.joomlabamboo.com  All rights reserved.
* @license  GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
*/
// no direct access
defined('_JEXEC') or die('Restricted access');
class JElementInfo extends JElement {
	var	$_name = 'info';
	function fetchElement($name, $value, &$node, $control_name){
		// Output

		return '
		<div style="font-size:12px;line-height:18px;color:#333;padding:10px;margin:10px 0;background: #FAF2B6">
			'.JText::_($value).'
		</div>
		';
	}
	function fetchTooltip($label, $description, &$node, $control_name, $name){
		// Output
		return '&nbsp;';
	}
}
