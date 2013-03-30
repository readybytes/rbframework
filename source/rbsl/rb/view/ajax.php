<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();


class Rb_ViewAjax extends Rb_View
{
	public 	$_renderOptions = array('domObject'=>'xiWindowContent','domProperty'=>'innerHTML');
	public 	$headerFooters 	= true;
	
	protected function render($html, $options = array('domObject'=>'xiWindowContent','domProperty'=>'innerHTML'))
	{
		$domObject	 = JRequest::getVar('domObject',$options['domObject']);
		$domProperty = JRequest::getVar('domProperty',$options['domProperty']);

		$response	= Rb_Factory::getAjaxResponse();
		$response->addAssign( $domObject , $domProperty , $html );

		//RBFW_TODO : remove this send response, let the start file do it
		return $response->sendResponse();
	}

	//this will set popup window title
    function _setAjaxWinTitle($title)
    {
    	Rb_Factory::getAjaxResponse()->addScriptCall('xi.ui.dialog.title',$title);
    }

    //this will set action/submit button on bottom of popup window
	function _addAjaxWinAction($text, $onButtonClick=null)
	{
		static $actions = array();

		if($onButtonClick !== null){
			$obejct 		= new stdClass();
			$object->click 	= $onButtonClick;
			$object->text 	= $text;
			$actions[]=$object;
		}
    	return $actions;
    }

	function _setAjaxWinAction()
	{
    	$actions = $this->_addAjaxWinAction('',null);

    	if(count($actions)===0){
    		return false;
    	}

    	Rb_Factory::getAjaxResponse()->addScriptCall('xi.ui.dialog.button',$actions);
    	return true;
    }

    function _setAjaxWinHeight($height)
    {
    	Rb_Factory::getAjaxResponse()->addScriptCall('xi.ui.dialog.height',$height);
    }
    
	function _setAjaxWinWidth($width)
	{
    	Rb_Factory::getAjaxResponse()->addScriptCall('xi.ui.dialog.width',$width);
    }
    
    function _setAjaxWinAutoclose($time)
    {
    	Rb_Factory::getAjaxResponse()->addScriptCall('xi.ui.dialog.autoclose',$time);
    }
}