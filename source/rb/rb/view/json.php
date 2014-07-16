<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die('Restricted access' );


class Rb_ViewJson extends Rb_View
{	
	protected $json;
	
	public function render($output, $options)
	{
		echo '###'.json_encode($output).'###';
		exit;
	}
	
	function _showTask()
	{
 		return $this->get('json');
	}
}