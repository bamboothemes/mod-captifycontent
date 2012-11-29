<?php
/**
* @id $Id$
* @author  mod_slideshow3.php
* @package  JB Slideshow3
* @copyright Copyright (C) 2006 - 2011 Joomla Bamboo. http://www.joomlabamboo.com  All rights reserved.
* @license  GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
*/
// no direct access
defined('_JEXEC') or die('Restricted access');

class JFormFieldHeader extends JFormField {
	protected $type = 'header';
	protected function getInput(){
		// Output
		return '
		<div style="font-weight:bold;padding:10px 0;margin:0;background:#f6f6f6;height:10px">
			'.JText::_($this->value).'
		</div>
		';
	}
	public function getLabel() {
		return '<span style="display:none">' . parent::getLabel() . '</span>';
	}
}
