<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die('Restricted access' );

jimport( 'joomla.application.component.controller' );

class Rb_AdaptJ35Controller extends JControllerLegacy
{
	protected	$_name		= null;
		
	public function getMessage()
	{
		return $this->message;
	}
	
	public function getRedirect()
	{
		return $this->redirect;
	}
	
	
	public function getdoTask()
	{
		return $this->doTask;
	}
	
	public function setdoTask($doTask)
	{
		$this->doTask = $doTask;
		return $this;		
	}
	
	public function getTask()
	{
		return $this->task;
	}
	
	public function setTask($task)
	{
		$this->task = $task;
		return $this;		
	}
}

class Rb_AdaptController extends Rb_AdaptJ35Controller
{}
