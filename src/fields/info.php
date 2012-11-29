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
jimport('joomla.html.html');
jimport('joomla.form.formfield');

class JFormFieldInfo extends JFormField {
	protected $type = 'info';
	protected function getInput(){
		// Output
		return '
		<div style="font-size:12px;line-height:18px;color:#333;padding:10px;margin:10px 0;background: #F4F4F4">
			'.JText::_($this->element['default']).'
		</div>
		';
	}
	public function getLabel() {
		return '<span style="display:none">' . parent::getLabel() . '</span>';
	}
}
