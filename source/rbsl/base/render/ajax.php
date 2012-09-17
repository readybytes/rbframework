<?php
/**
* @copyright	Copyright (C) 2009 - 2009 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		PayPlans
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();


class XiRenderAjax extends XiRender
{
	public $headerFooters = true;
	protected function _render(XiView $view, $html, $options = array('domObject'=>'xiWindowContent','domProperty'=>'innerHTML'))
	{
		$domObject	=	JRequest::getVar('domObject',$options['domObject']);
		$domProperty = JRequest::getVar('domProperty',$options['domProperty']);

		$response	= XiFactory::getAjaxResponse();
		$response->addAssign( $domObject , $domProperty , $html );

		//XITODO : remove this send response, let the start file do it
		return $response->sendResponse();
	}

}
