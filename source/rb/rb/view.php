<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die('Restricted access' );



abstract class Rb_AbstractView extends Rb_AdaptView
{
	
	protected $_model 			= null;  // Will be set by controller
	protected $_modelform		= null;  // A model to get all forms required
	/** 
	 * @var Rb_Extension
	 */
	public    $_component		= '';
	protected $_tpl 			= null;
	
	public 	  $_renderOptions 	= array();
	protected $_templatePaths	= null;

	public function __construct($config = array())
	{
		parent::__construct($config);
		
		// setup extension naming convention
		$this->_component = Rb_Extension::getInstance($this->_component);
		$this->_getTemplatePath();
	}
	/*
	 * We need to override joomla behaviour as they differ in
	 * Model and Controller Naming
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
		Rb_Error::assert(preg_match('/(.*)View/i', get_class($this), $r), JText::sprintf('PLG_SYSTEM_RBSL_ERROR_XIVIEW_GETPREFIX_CANT_GET_OR_PARSE_CLASSNAME', get_class($this)), Rb_Error::ERROR);


		$this->_prefix  =  strtolower($r[1]);
		return $this->_prefix;
	}

	/**
	 * @return Rb_Model
	 */
	function getModel($name = null)
	{
		return $this->_model;
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
		$this->_isAdmin    = Rb_Factory::getApplication()->isAdmin();
		$this->setTask($task);
		$this->setTpl($tpl);

		// Execute the task
		if(!(bool)$this->$task()) {
			return false;
		}
		
		//Task executed successfully
		$this->_basicFormSetup($task);

		// Trigger event before we load templates
		// Different apps will send on respective positions
		$args	= array(&$this, &$task, $this->getName());		
		$pluginResult = Rb_HelperJoomla::triggerPlugin('on'.$this->_component->getPrefixClass().'ViewBeforeRender',$args, '', $this);
		$pluginResult = $this->_filterPluginResult($pluginResult);
		
		// now get html from different plugins and views
		$olddata = $this->get('plugin_result');
		$pluginResult = $this->_mergePluginsData($pluginResult, $olddata);
		
		$this->assign('plugin_result', $pluginResult);

		$output = $this->_showTask();

		//post template rendering load trigger
		$args	= array(&$this, &$task, $this->getName(), &$output);
		$result =  Rb_HelperJoomla::triggerPlugin('on'.$this->_component->getPrefixClass().'ViewAfterRender', $args, '', $this);
	
		//render output
		return $this->render($output, $this->_renderOptions);
	}
	
	protected function _showTask()
	{
		//load assets file first, then load its tmpls
		$output = $this->loadTemplate('assets');
				
		//load the template file
		$output .= $this->loadTemplate($this->getTpl());
		
		return $output;
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
		
		if(Rb_Factory::getApplication()->input->get('tmpl')=='component'){
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
		//RBFW_TODO : Don't use hard entity. { task => edit and new } 
		//Give flexibility So we can be changed it from anywhere for adding extra task without adding here.
		if($this->getTask() == 'edit' || $this->getTask() == 'new'){
			$this->_adminEditToolbar();
		}
		else{
			$this->_adminGridToolbar();
		}
	}

	/** Set the titlebar text and icon
	 * @param unknown_type $title
	 * @param unknown_type $image
	 */
	protected function _adminToolbarTitle($title=null, $image=null)
	{
		if($title === null){
			$title = JText::_($this->_component->getPrefixText().'_SUBMENU_'.strtoupper($this->getName()));
		}
		
		if($image === null){
			$image = $this->_component->getNameSmall().'-'.$this->getName().'.png';
		}
		
		JToolBarHelper::title($title,	$image);
	}

	protected function _adminGridToolbar()
	{
		// Sample Toolbar icons
		//Rb_HelperToolbar::addNewX('new');
		//Rb_HelperToolbar::editListX();
		//Rb_HelperToolbar::customX( 'copy', 'copy.png', 'copy_f2.png', 'PLG_SYSTEM_RBSL_TOOLBAR_COPY', true );
		//Rb_HelperToolbar::divider();
		//Rb_HelperToolbar::publish();
		//Rb_HelperToolbar::unpublish();
		//Rb_HelperToolbar::divider();
	}

	protected function _adminEditToolbar()
	{   
	    //$model = $this->getModel();
		//Rb_HelperToolbar::apply();
		//Rb_HelperToolbar::save();
		//Rb_HelperToolbar::savenew();
		//Rb_HelperToolbar::cancel();
		//Rb_HelperToolbar::divider();
	  	//don't display delete button when creating new instance of object 
	    //if($model->getId() != null){
		//  Rb_HelperToolbar::deleteRecord();
		//}
	}

	
	static $_submenus = array();
	static function addSubmenus($menu=null)
	{
		if($menu !== null){
			if(is_array($menu)){
				foreach($menu as $m){
					self::$_submenus[] = $m;		
				}
			}else{
				self::$_submenus[] = $menu;
			}
		}	
		
		return self::$_submenus;
	}

	protected static $_subMenuRenderingDone = false;
	public function _adminSubmenu($selMenu = 'dashboard')
	{
		$selMenu	= strtolower(Rb_Factory::getApplication()->input->get('view',$selMenu));

		foreach(self::$_submenus as $menu){
			//IMP :: JToolBarHelper class does not have _addSubMenu function
			Rb_HelperToolbar::addSubMenu($menu, $selMenu, $this->_component->getNameCom());
		}
		return $this;
	}


	protected function _basicFormSetup($task)
	{
		//setup the action URL
		$url 	= 'index.php?option='.$this->_component->getNameCom().'&view='.$this->getName();
		$task	= Rb_Factory::getApplication()->input->get('task', $task);
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
	
	public function get($tpl, $default = null)
	{
		// for templates data is assigned to tplVars
		if(isset($this->_tplVars[$tpl])){
			return $this->_tplVars[$tpl];
		}
		
		return parent::get($tpl);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see libraries/legacy/view/JViewLegacy::assign()
	 * @param $key   Initialized with default value '', to remove strict standard warning
	 * @param $value Initialized with default value '', to remove strict standard warning
	 */
	public function assign($key = '', $value = '')
	{
		$this->_tplVars[$key] = $value;
	}
	
	function loadTemplate( $tpl = null, $args = null, $layout=null)
	{
		if($args === null && isset($this->_tplVars)){
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
			throw new Exception(" Layout $file [$template] not found for class:".get_class($this));
		}
		
		// unset so as not to introduce into template scope
		unset($tpl);
		unset($file);
		if (isset($args['this'])) {
			unset($args['this']);
		}

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
	
	protected function _getTemplatePath()
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
        
        	$joomlaTemplatePath 	= JPATH_THEMES.'/'.$jTemplate.'/html/'.$this->_component->getNameCom();
        	$extTemplatePath 		= constant($this->_component->getNameCaps().'_PATH_SITE_TEMPLATE');
        	$extSiteTemplatePath 	= $extTemplatePath;
        	
			// To load temlates of view, irrespective of instance of app (admin or site)
			// if current class name of current instance has admin word but not site, then load admin template
			// if current class name of current instance has site word but not admin, then load site template
			// in case if class name has bothe the word, throw exception
        	// for admin view
			if(preg_match('^(?!.*site).*admin.*$^i', get_class($this))){
				$extTemplatePath = constant($this->_component->getNameCaps().'_PATH_ADMIN_TEMPLATE');
			}
			// for site view
			elseif(preg_match('^(?!.*admin).*site.*$^i', get_class($this))){
				//do nothing as it is already set
			}
			else{
				throw new Exception("Class name of view must not contain 'admin' and 'site' both");
			}

			// joomla template override
        	$paths[] = $joomlaTemplatePath.'/'.$view;
            $paths[] = $joomlaTemplatePath;
        	
			// selected template path
			$paths[] = $extTemplatePath.'/'.$extTemplate.'/'.$view;
			$paths[] = $extTemplatePath.'/'.$extTemplate;

			// default template path			
			$paths[] = $extTemplatePath.'/'.$extDefaultTemplate.'/'.$view;
			$paths[] = $extTemplatePath.'/'.$extDefaultTemplate;

			$this->_templatePaths = $paths;
        }

        return $this->_templatePaths;
    }

    function addPathToTemplate($templatePaths)
    {
    	if(!is_array($templatePaths)){
    		$templatePaths = array($templatePaths);    		
    	}
    	
    	if($this->_templatePaths == null){
    		$this->_getTemplatePath();
    	}
    	
		foreach($templatePaths as $tmpl){
			$this->_templatePaths[] = $tmpl;
		}
		
		return $this;
    }
}


abstract class Rb_View extends Rb_AbstractView
{
	// XITODO : check task for savenew in J2.5
	protected $_validateActions = array('apply', 'save', 'save2new', 'savenew');
	public function getDynamicJavaScript()
	{
		// no need to load these admin specific strings in frontend.
		if(!Rb_Factory::getApplication()->isAdmin()){
			return '';
		}

		// get valid actions for validation submission
		$validActions = $this->_validateActions;
		Rb_Error::assert(is_array($validActions));
		
		//common js code to trigger
		ob_start(); ?>

		// current view
		var view 			= '<?php echo $this->getName();?>' ;
        var validActions 	= '<?php echo json_encode($validActions);?>' ;


		Joomla.submitbutton = function(action) {
			<?php echo $this->_component->getNameSmall();?>.admin.grid.submit(view, action, validActions);
		}

		<?php
		$js = ob_get_contents();
		ob_end_clean();

		return $js;
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
		if(count($records) > 0) {
			return $this->_displayGrid($records);
		}

		return $this->_displayBlank();
	}

	function _displayBlank()
	{
		$model 		= $this->getModel();
		$textPrefix = $this->_component->getPrefixText();
		
		$heading = $textPrefix.'_ADMIN_BLANK_'.strtoupper($this->getName());
		$message = $heading.'_MSG';
		
		$this->assign('heading', JText::_($heading));
		$this->assign('msg', JText::_($message));
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
}
