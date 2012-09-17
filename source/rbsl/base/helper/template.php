<?php
/**
* @copyright	Copyright (C) 2009 - 2009 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		PayPlans
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

class XiHelperTemplate
{
	static $_setupScriptsEnv = false;
	public static function loadSetupEnv()
	{
		if(self::$_setupScriptsEnv === true){
			return true;
		}

		$vars  = new stdClass();
		
		$vars->url = new stdClass();
		$vars->url->base = JURI::base();
		$vars->url->root = JURI::root();
		$vars->url->base_without_scheme = JURI::base(true);
		$vars->request = new stdClass();
		$vars->request->option 	= JRequest::getVar('option','');
		$vars->request->view 	= JRequest::getVar('view','');
		$vars->request->task 	= JRequest::getVar('task','');
		
		$vars->time = new stdClass();
		$vars->time->timzone 	= XiHelperJoomla::getUserTimeZone();
		$vars->time->offset 	= XiHelperJoomla::getUserTimeZone()* 60;
		
		
		ob_start();
		?>
		var xi_vars = <?php echo json_encode($vars); ?>;
		
		if(typeof(Joomla) === "undefined") {
	      var Joomla = {};
		}

		//XITODO : deprecated variables will be removed in 2.1	
		var xi_url_base = xi_vars['url']['base'];
		var xi_url_base_without_scheme = xi_vars['url']['base_without_scheme'];
		var xi_view = xi_vars['request']['view'];
		var xi_time_offset_minutes = xi_vars['time']['offset'];
		<?php
		$script = ob_get_contents();
		ob_end_clean();
		XiFactory::getDocument()->addScriptDeclaration($script);
		return self::$_setupScriptsEnv = true;;
	}
	
	static $_setupScriptsLoaded = false;
	public static function loadSetupScripts($jquery=true, $jquery_ui=true)
	{
		if(self::$_setupScriptsLoaded === true){
			return true;
		}
		
		$isAdmin = XiFactory::getApplication()->isAdmin();
		// Load Mootools first : It is done automatically by script function
		// NoConflict already added in jQuery file,
		if($jquery || $isAdmin){
			XiHtml::script('jquery.js');
		}

		//load jQuery if required
		if($jquery_ui || $isAdmin){
			XiHtml::stylesheet('jquery-ui.css');
			XiHtml::script('jquery-ui.js');
			//XITODO : if IE then load jquery-ui-ie.css also
		}
		//load payplans customized jquery-ui.css
		XiHtml::stylesheet('xi-ui.css');
		
		XiHtml::stylesheet('xi.css');
		// Load XI Script (Maintain Order) then other scripts
		XiHtml::script('xi.core.js');
		XiHtml::script('xi.lib.js');

		return self::$_setupScriptsLoaded = true;
	}
	
	
	public static function mediaURI($path, $append=true)
	{
		$path	= XiHelperUtils::str_ireplace(XiHelperJoomla::getRootPath().DS, '', $path);
		
		// replace all DS to URL-slash
		$path	= JPath::clean($path, '/');
		
		// no need to add root url
		if(strpos($path, 'http') === 0) {
			return $path;
		}
		
		// prepend URL-root, and append slash
		return JURI::root().$path.($append ? '/' : '');
	}
	
	public static function partial($layout='default', $args=array())
	{
		$app 		= XiFactory::getApplication();
    	$pTemplate	= 'default'; 			// XITODO : the template being used
    	$pDefaultTemplate = 'default'; 		// default template
		$jTemplate 	= $app->getTemplate(); 	// joomla template

        // get the template and default paths for the layout
        static $paths = null;

        if($paths === null)
        {
        	$paths = array();
        
        	$joomlaTemplatePath = JPATH_THEMES.DS.$jTemplate.DS.'html'.DS.PAYPLANS_COMPONENT_NAME;
			if($app->isAdmin()){
				$payplanTemplatePath = PAYPLANS_PATH_TEMPLATE_ADMIN;
			}
			else{
				$payplanTemplatePath =  PAYPLANS_PATH_TEMPLATE;
			}

			// joomla template override
        	$paths[] = $joomlaTemplatePath.DS.'_partials';
			$paths[] = $payplanTemplatePath.DS.$pTemplate.DS.'_partials';
			$paths[] = $payplanTemplatePath.DS.$pDefaultTemplate.DS.'_partials';
			// default to frontend partials
			$paths[] = PAYPLANS_PATH_TEMPLATE.DS.$pDefaultTemplate.DS.'_partials';
        }
        
        //find the path and return
        jimport('joomla.filesystem.path');
        $template = JPath::find($paths, $layout.'.php');
        
		if ($template == false) {
			return JError::raiseError( 500, "Layout $file [$template] not found");
		}
		
        // setup args and render 
        extract((array)$args,  EXTR_OVERWRITE);
		
		ob_start();
		include $template;
		$output = ob_get_contents();
		ob_end_clean();

		return $output;
        
	}
}