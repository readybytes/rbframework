<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();



abstract class Rb_AbstractView extends Rb_AdaptView
{
	
	protected $_model 			= null; // Will be set by controller
	public    $_component		= '';
	protected $_tpl 			= null;
	public 	  $options 			= array('domObject'=>'xiWindowContent','domProperty'=>'innerHTML');
	protected $_templatePaths	= null;

	/*
	 * We need to override joomla behaviour as they differ in
	 * Model and Controller Naming
	 * In Joomla 	-> JModelProducts, JProductsController
	 * In PayPlans	 -> PayplansModelProducts, PayplansControllerProducts
	 */
	function getName()
	{
		if (empty( $this->_name ))
		{
			$r = null;
			if (!preg_match('/View(.*)/i', get_class($this), $r)) {
				JError::raiseError (500, "Rb_View::getName() : Can't get or parse class name.");
			}
			
			$this->_name= strtolower( $r[1] );
		}

		return $this->_name;
	}

	/*
	 * Collect prefix auto-magically
	 */
	public function getPrefix()
	{
		if(isset($this->_prefix) && empty($this->_prefix)===false)
			return $this->_prefix;

		$r = null;
		Rb_Error::assert(preg_match('/(.*)View/i', get_class($this), $r), Rb_Text::sprintf('PLG_SYSTEM_RBSL_ERROR_XIVIEW_GETPREFIX_CANT_GET_OR_PARSE_CLASSNAME', get_class($this)), Rb_Error::ERROR);


		$this->_prefix  =  JString::strtolower($r[1]);
		return $this->_prefix;
	}

	function getModel()
	{
		return $this->_model;
	}

	function setModel($model)
	{
		 $this->_model = $model;
		 return $this;
	}

	function setTpl($tpl=null)
	{
		 $this->_tpl = $tpl;
		 return $this;
	}

	function getTpl($tpl=null)
	{
		 return $this->_tpl;
	}
	
	function setTask($task=null)
	{
		 $this->_task = $task;
		 return $this;
	}

	function getTask($task=null)
	{
		 return $this->_task;
	}
	/*
	 * This function will be called from Controller
	 * after doTask, so it will be called after
	 * controller task have been completed
	 *
	 * So that there is one point contact for
	 * handling anything in view
	 */
	function showTask($task='',$tpl=null)
	{
 		//generate the data in view for template
		if(empty($task) || $task=='default' || method_exists($this, $task)==false ){
			$task='display';
		}

		//set are you in admin or site
		$app = Rb_Factory::getApplication();
		$this->_isAdmin    = $app->isAdmin();
		$this->setTask($task);
		$this->setTpl($tpl);

		// collect task specific data,
		// if some error, do not display the page and simply return
		// RBFW_TODO : controller will handle the error
		if(false === $this->$task()){
			return false;
		}
		//set the model state in view variable
		$this->_basicFormSetup();

		// Trigger event before we load templates
		$args	= array(&$this, &$task);
		// get data from diffreent apps on respective positions
		$pluginResult = Rb_HelperPlugin::trigger('onRbViewBeforeRender',$args, '', $this);
		$pluginResult = $this->_filterPluginResult($pluginResult);
		
		// now get html from different plugins and views
		$olddata = $this->get('plugin_result');
		$pluginResult = $this->_mergePluginsData($pluginResult, $olddata);
		
		$this->assign('plugin_result', $pluginResult);

		//load the template file
		$output = $this->loadTemplate($this->getTpl());
		if (Rb_Error::isError($output)) {
			return $output;
		}

		//post template rendering load trigger
		$args	= array(&$this, &$task, &$output);
		$result =  Rb_HelperPlugin::trigger('onRbViewAfterRender', $args, '', $this);

		$this->_prepareDocument();
		
		//render output
		return $this->render($output);
	}
	
	public function _mergePluginsData($pluginResult, $olddata)
	{
		if(!isset($olddata)){
			return $pluginResult;
		}
			
		if(count($pluginResult) > 1){
			foreach($pluginResult as $key=>$html){
				if(isset($olddata[$key])){
				    $pluginResult[$key] = $olddata[$key].$pluginResult[$key];
					//IMP:- unset values of $olddata after assigning values into $pluginResults
				    unset($olddata[$key]);
				}
			}
			//IMP:- if any data exists on any $key of $oldata 
			// but not on $pluginResult then add those values in $pluginResult
			$pluginResult = array_merge($pluginResult, $olddata);
		}
		else{
			foreach ($olddata as $key=>$html){
				$pluginResult[$key] = $olddata[$key];
			}
		}
		
		return $pluginResult;
	}

	protected function _prepareDocument()
	{	
		if(Rb_Factory::getApplication()->isAdmin()){
			return true;
		}
		
		$app		= Rb_Factory::getApplication();
		$params 	= $app->getParams();
		$document 	= Rb_Factory::getDocument();		
		$menus		= $app->getMenu();
		$title		= null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();
		if($menu){
			$params->def('page_heading', $params->def('page_title', $menu->title));
		}
		
		$title = $params->get('page_title', '');
		if (empty($title)) {
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1) {
			$title = Rb_Text::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
			$title = Rb_Text::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}
		
		$document->setTitle($title);

		if ($params->get('menu-meta_description'))
		{
			$document->setDescription($params->get('menu-meta_description'));
		}

		if ($params->get('menu-meta_keywords'))
		{
			$document->setMetadata('keywords', $params->get('menu-meta_keywords'));
		}

		if ($params->get('robots'))
		{
			$document->setMetadata('robots', $params->get('robots'));
		}
	}
	
	protected function _filterPluginResult($pluginResult)
	{
		$result = array('default' => '');
		foreach($pluginResult as $pluginHtml){
						
			// ignore empty, true and false
			if(is_bool($pluginHtml) || isset($pluginHtml)==false || empty($pluginHtml)==true){
				continue;
			}

			$position = 'default';
			$html	  = '';
			// want to set on position
			if(is_array($pluginHtml)){
				foreach ($pluginHtml as $position => $html){			
					// if string, then need to display on certain position
					if(is_string($position)){		
						if(isset($result[$position])==false){
							$result[$position] = '';
						}
						$result[$position] .= $html;
					}
					
					// if nothing specified then echo on default position
					if(is_numeric($position)){
						$result['default'] .= $html ; 
					}
				}
			}else{
				// no position mentioned, display it on default
				$result['default'] .= $pluginHtml;
			}
		}
		
		return $result;
	}

	//Calls actual rendered object, to render output
	protected function _render($output)
	{

	}
	
	protected function _showHeader()
	{
		// add admin toolbar
		if($this->_isAdmin){
			$this->_adminToolbar();
			$this->_adminSubmenu();
		}
		return '';
	}

	protected function _showFooter()
	{
		// Do no apply on ajax request
		if(RB_REQUEST_DOCUMENT_FORMAT != 'html'){
			return '';
		}
		
		if(JRequest::getVar('tmpl')=='component'){
			return '';
		}
		
		//always shown in admin
		if(Rb_Factory::getApplication()->isAdmin()==true){
			return $this->_showAdminFooter();
		}

		return '';

	}

	protected function _showAdminFooter()
	{
		return '';
	}
	
	protected function _adminToolbar()
	{
		$this->_adminToolbarTitle();

		if($this->getTask() == 'edit' || $this->getTask() == 'new')
			$this->_adminEditToolbar();
		else
			$this->_adminGridToolbar();
	}

	protected function _adminToolbarTitle()
	{
		// Set the titlebar text
		//Rb_HelperToolbar::title(Rb_Text::_('PLG_SYSTEM_RBSL_SM_'.JString::strtoupper($this->getName())), "xi-".$this->getName().".png");
	}

	protected function _adminGridToolbar()
	{
		Rb_HelperToolbar::addNewX('new');
		Rb_HelperToolbar::editListX();
		Rb_HelperToolbar::customX( 'copy', 'copy.png', 'copy_f2.png', 'PLG_SYSTEM_RBSL_TOOLBAR_COPY', true );
		Rb_HelperToolbar::divider();
		Rb_HelperToolbar::publish();
		Rb_HelperToolbar::unpublish();
		Rb_HelperToolbar::divider();
		Rb_HelperToolbar::delete();
		Rb_HelperToolbar::divider();

	}

	protected function _adminEditToolbar()
	{   
	    $model = $this->getModel();
		Rb_HelperToolbar::apply();
		Rb_HelperToolbar::save();
		Rb_HelperToolbar::savenew();
		Rb_HelperToolbar::cancel();
		Rb_HelperToolbar::divider();
	  	//don't display delete button when creating new instance of object 
	    if($model->getId() != null){
		   Rb_HelperToolbar::deleteRecord();
		 }
		
	}

	
	static $_submenus = array('dashboard', 'config', 'plan','app', 'subscription',
							  'invoice', 'transaction','user', 'log');
	static function addSubmenus($menu=null)
	{
		if($menu !== null){
			self::$_submenus[] = $menu;
		}	
		return self::$_submenus;
	}

	protected static $_subMenuRenderingDone = false;
	public function _adminSubmenu($selMenu = 'dashboard')
	{
		$selMenu	= JString::strtolower(JRequest::getVar('view',$selMenu));

		// add menu for group if config option is enable
		if(!in_array('group', self::$_submenus)
			&& isset(Rb_Factory::getConfig()->useGroupsForPlan) 
			&& Rb_Factory::getConfig()->useGroupsForPlan){
			array_splice(self::$_submenus, 2, 0, "group");
		}
	
		foreach(self::$_submenus as $menu){
			Rb_HelperToolbar::addSubMenu($menu,$selMenu);
		}
		return $this;
	}


	protected function _basicFormSetup()
	{
		//setup the action URL
		$url 	= 'index.php?option=com_payplans&view='.$this->getName();
		$task	= JRequest::getVar('task');
		if($task){
			$url .= '&task='.$task;
		}
		
		$this->assign('uri', Rb_Route::_($url));

		//setup state
		$this->assign( 'state', $this->getModel()->getState());

		//setup record id, if any
		$this->assign( 'record_id', $this->getModel()->getId());

		//also pass model if required
		$this->assign( 'model', $this->getModel());
	}
	
	public function get($tpl=null)
	{
		// for templates data is assigned to tplVars
		if(isset($this->_tplVars[$tpl])){
			return $this->_tplVars[$tpl];
		}
		
		return parent::get($tpl);
	}
	
	public function assign($key, $value)
	{
		$this->_tplVars[$key] = $value;
	}
	
	function loadTemplate( $tpl = null, $args = null, $layout=null)
	{
		if($args === null){
			$args= $this->_tplVars;
		}
		
		//create the template file name based on the layout
		if($layout===null){
			$layout = $this->_layout;
		}
		
		$file = isset($tpl) ? $layout.'_'.$tpl : $layout;
		$file = preg_replace('/[^A-Z0-9_\.-]/i', '', $file);

		// load the template script
		$paths = $this->_getTemplatePath($file);
		//find the path
    	jimport('joomla.filesystem.path');
    	$template = JPath::find($paths, $file.'.php');

		if ($template == false) {
			return JError::raiseError( 500, "Layout $file [$template] not found");
		}
		
		// unset so as not to introduce into template scope
		if (isset($args['this'])) {
			unset($args['this']);
		}
		
		unset($tpl);
		unset($file);
		

		// Support tmpl vars
        unset($args['this']);
        unset($args['_tplVars']);
        extract((array)$args,  EXTR_OVERWRITE);
		
		ob_start();
		include $template;
		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}
	
	protected function _getTemplatePath($layout = 'default')
    {
    	
    	$app 		= Rb_Factory::getApplication();
    	$view 		= strtolower($this->getName());
    	
    	$extTemplate		= 'default'; 	// RBFW_TODO : the template being used
    	$extDefaultTemplate = 'default'; 	// default template
		$jTemplate 	= $app->getTemplate(); 	// joomla template

        // get the template and default paths for the layout
        if($this->_templatePaths === null)
        {
        	$paths = array();
        
        	$joomlaTemplatePath 	= JPATH_THEMES.'/'.$jTemplate.'/html/com_'.$this->_component;
        	$extTemplatePath 		= constant(strtoupper($this->_component).'_PATH_SITE_TEMPLATE');
        	$extSiteTemplatePath 	= $extTemplatePath;
			if($app->isAdmin()){
				$extTemplatePath = constant(strtoupper($this->_component).'_PATH_ADMIN_TEMPLATE');
			}

			// joomla template override
        	$paths[] = $joomlaTemplatePath.'/'.$view;
            $paths[] = $joomlaTemplatePath;
        	$paths[] = $joomlaTemplatePath.'/_partials';
        	
			// selected template path
			$paths[] = $extTemplatePath.'/'.$extTemplate.'/'.$view;
			$paths[] = $extTemplatePath.'/'.$extTemplate;
			$paths[] = $extTemplatePath.'/'.$extTemplate.'/_partials';

			// default template path			
			$paths[] = $extTemplatePath.'/'.$extDefaultTemplate.'/'.$view;
			$paths[] = $extTemplatePath.'/'.$extDefaultTemplate;
			$paths[] = $extTemplatePath.'/'.$extDefaultTemplate.'/_partials';

			// finally default partials
			$paths[] = $extSiteTemplatePath.'/'.$extDefaultTemplate.'/_partials';
			$this->_templatePaths = $paths;
        }
        //JError::raiseError( 500, var_export($this->_templatePaths, true)); 
        return $this->_templatePaths;
    }

    function addPathToView($templatePaths)
    {
		foreach($templatePaths as $tmpl){
			$this->addTemplatePath($tmpl);
		}
    }
}


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
		$heading = "PLG_SYSTEM_RBSL_ADMIN_BLANK_".JString::strtoupper($this->getName());
		$msg = "PLG_SYSTEM_RBSL_ADMIN_BLANK_".JString::strtoupper($this->getName())."_MSG";
		
		$this->assign('heading', Rb_Text::_($heading));
		$this->assign('msg', Rb_Text::_($msg));
		$this->assign('filters', $model->getState($model->getContext()));
		
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
		$this->assign('filters', $model->getState($model->getContext()));
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