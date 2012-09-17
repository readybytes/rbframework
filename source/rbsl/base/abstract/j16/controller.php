<?php
/**
* @copyright	Copyright (C) 2009 - 2009 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		PayPlans
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

class XiAbstractJ16Controller extends XiAbstractControllerBase
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

class XiAbstractController extends XiAbstractJ16Controller
{}
