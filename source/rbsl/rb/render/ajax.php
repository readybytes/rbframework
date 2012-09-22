<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();


class Rb_RenderAjax extends Rb_Render
{
	public $headerFooters = true;
	protected function _render(Rb_View $view, $html, $options = array('domObject'=>'xiWindowContent','domProperty'=>'innerHTML'))
	{
		$domObject	=	JRequest::getVar('domObject',$options['domObject']);
		$domProperty = JRequest::getVar('domProperty',$options['domProperty']);

		$response	= Rb_Factory::getAjaxResponse();
		$response->addAssign( $domObject , $domProperty , $html );

		//RBFW_TODO : remove this send response, let the start file do it
		return $response->sendResponse();
	}

}
