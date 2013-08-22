<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die('Restricted access' );


class Rb_ViewAjax extends Rb_View
{
	public 	$_renderOptions = array('domObject'=>'rbWindowContent','domProperty'=>'innerHTML');
	public 	$headerFooters 	= true;
	
	protected function render($html, $options = array('domObject'=>'rbWindowContent','domProperty'=>'innerHTML'))
	{
		$domObject	 = $this->input->get('domObject',$options['domObject']);
		$domProperty = $this->input->get('domProperty',$options['domProperty']);

		$response	= Rb_Factory::getAjaxResponse();
		$response->addAssign( $domObject , $domProperty , $html );

		//RBFW_TODO : remove this send response, let the start file do it
		return $response->sendResponse();
	}

	//this will set popup window title
    function _setAjaxWinTitle($title)
    {
    	Rb_Factory::getAjaxResponse()->addScriptCall('rb.ui.dialog.title', $title);
    }

	//this will set popup window body
    function _setAjaxWinBody($body)
    {
    	Rb_Factory::getAjaxResponse()->addScriptCall('rb.ui.dialog.body', $body);
    }
    
    //this will set action/submit button on bottom of popup window
	function _addAjaxWinAction($text, $onButtonClick=null, $class="btn", $attr = '')
	{
		static $actions = array();

		if($onButtonClick !== null){
			$obejct 		= new stdClass();
			$object->click 	= $onButtonClick;
			$object->text 	= $text;
			$object->classes= $class;
			$object->attr 	= $attr;
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

    	Rb_Factory::getAjaxResponse()->addScriptCall('rb.ui.dialog.button',$actions);
    	return true;
    }

    function _setAjaxWinHeight($height)
    {
    	Rb_Factory::getAjaxResponse()->addScriptCall('rb.ui.dialog.height',$height);
    }
    
	function _setAjaxWinWidth($width)
	{
    	Rb_Factory::getAjaxResponse()->addScriptCall('rb.ui.dialog.width',$width);
    }
    
    function _setAjaxWinAutoclose($time)
    {
    	Rb_Factory::getAjaxResponse()->addScriptCall('rb.ui.dialog.autoclose',$time);
    }
}