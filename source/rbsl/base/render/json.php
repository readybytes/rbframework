<?php
/**
* @copyright	Copyright (C) 2009 - 2009 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		PayPlans
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();


class XiRenderJson extends XiRender
{
	public $headerFooters = false;
	protected function _render(XiView $view, $html, $options = array('domObject'=>'xiWindowContent','domProperty'=>'innerHTML'))
	{
		echo json_encode($view->get('json'));
		exit;
	}

}