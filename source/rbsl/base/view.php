<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		PayPlans
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

abstract class Rb_View extends Rb_AbstractView
{
	public function getDynamicJavaScript()
	{
		// get valid actions for validation submission
		$validActions = $this->getJSValidActions();
		if(!is_array($validActions)){
			$validActions = (array)$validActions;
		}
		
		//common js code to trigger
		ob_start(); ?>

		// current view
		var view = '<?php echo $this->getName();?>' ;
        var validActions = '<?php echo json_encode($validActions);?>' ;

		<?php if(PAYPLANS_JVERSION_15): ?>
		function submitbutton(action) {
		<?php else : ?> 
		Joomla.submitbutton = function(action) {
		<?php endif; ?>
			payplansAdmin.submit(view, action, validActions);
		}

		<?php
		$js = ob_get_contents();
		ob_end_clean();

		return $this->_getDynamicJavaScript().$js;
	}

    public function getJSValidActions()
    {
    	return array('apply', 'save', 'edit', 'delete', 'savenew');
    }

	public function _getDynamicJavaScript()
	{
		return '';
	}

	//Available Task for views, these should only
	//we will later override this
	function display($tpl=null)
	{
		//IMP : If load records is already done before rendering the page
		// then it will not add pagination into it
		// so always clean the query for displaying it on grid views
		$model = $this->getModel();
		$model->clearQuery();

		// IMP : this is required for the pagination issue
		// we should load records after pagination is set, so that it can work well
		$model->getPagination();
		
		$records = $model->loadRecords(array(), array());

		// if total of records is more than 0
		if($model->getTotal() > 0)
			return $this->_displayGrid($records);

		return $this->_displayBlank();
	}

	function _displayBlank()
	{
		$model = $this->getModel();
		$heading = "COM_PAYPLANS_ADMIN_BLANK_".JString::strtoupper($this->getName());
		$msg = "COM_PAYPLANS_ADMIN_BLANK_".JString::strtoupper($this->getName())."_MSG";
		
		$this->assign('heading', Rb_Text::_($heading));
		$this->assign('msg', Rb_Text::_($msg));
		$this->assign('filters', $model->getState(Rb_HelperContext::getObjectContext($model)));
		
		$this->setTpl('blank');
		
		return true;
	}

	function _displayGrid($records)
	{
		$this->setTpl('grid');

		//do processing for default display page
		$model = $this->getModel();
		$recordKey =  $model->getTable()->getKeyName();
		$this->assign('records', $records);
		$this->assign('record_key', $recordKey);
		$this->assign('pagination', $model->getPagination());
		$this->assign('filter_order', $model->getState('filter_order'));
		$this->assign('filter_order_Dir', $model->getState('filter_order_Dir'));
		$this->assign('limitstart', $model->getState('limitstart'));
		$this->assign('filters', $model->getState(Rb_HelperContext::getObjectContext($model)));
		return true;
	}



	function view($tpl=null)
	{
		//do processing for default disply page
	}

	function edit($tpl=null)
	{
		$this->setTpl('edit');
		return true;
	}

	public function _renderModules($position, $attribs = array())
    {
    	jimport( 'joomla.application.module.helper' );

		$modules 	= JModuleHelper::getModules( $position );
		$modulehtml = array();

		// If style attributes are not given or set,
		// we enforce it to use the xhtml style
		// so the title will display correctly.
		if(!isset($attribs['style']))
			$attribs['style']	= 'xhtml';

		foreach($modules as $module){
				// disable title
				//RBFW_TODO : only if required
				//$module->showtitle = 0;
				$modulehtml[$module->title]=JModuleHelper::renderModule($module, $attribs);
		}
		
		// Also add data from apps output
		$pluginresult = $this->get('plugin_result');
		if($pluginresult){
			 if(array_key_exists($position, $pluginresult))
			    array_push($modulehtml,$pluginresult[$position]);
		 }

		return $modulehtml;
    }

    //this will set popup window title
    function _setAjaxWinTitle($title){
    	Rb_Factory::getAjaxResponse()->addScriptCall('xi.ui.dialog.title',$title);
    }

    //this will set action/submit button on bottom of popup window
	function _addAjaxWinAction($text, $onButtonClick=null){
		static $actions = array();

		if($onButtonClick !== null){
			$obejct 		= new stdClass();
			$object->click 	= $onButtonClick;
			$object->text 	= $text;
			$actions[]=$object;
		}
    	return $actions;
    }

	function _setAjaxWinAction(){
    	$actions = $this->_addAjaxWinAction('',null);

    	if(count($actions)===0){
    		return false;
    	}

    	Rb_Factory::getAjaxResponse()->addScriptCall('xi.ui.dialog.button',$actions);
    	return true;
    }

    function _setAjaxWinHeight($height){
    	Rb_Factory::getAjaxResponse()->addScriptCall('xi.ui.dialog.height',$height);
    }
    
	function _setAjaxWinWidth($width){
    	Rb_Factory::getAjaxResponse()->addScriptCall('xi.ui.dialog.width',$width);
    }
    
    function _setAjaxWinAutoclose($time){
    	Rb_Factory::getAjaxResponse()->addScriptCall('xi.ui.dialog.autoclose',$time);
    }
}