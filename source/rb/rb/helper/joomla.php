<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die('Restricted access' );

class Rb_AbstractHelperJoomla extends Rb_AdaptHelperJoomla
{
	public static function changePluginState($element, $folder = 'system', $state=parent::ENABLE)
	{
		$db		= Rb_Factory::getDBO();
		$query	= 'UPDATE '. $db->quoteName( '#__extensions' )
				. ' SET   '. $db->quoteName('enabled').'='.$db->Quote($state)
				. ' WHERE '. $db->quoteName('element').'='.$db->Quote($element)
				. ' AND ' . $db->quoteName('folder').'='.$db->Quote($folder) 
				. " AND `type`='plugin' ";
		
		$db->setQuery($query);
		return $db->query();
	}
	
	public static function getPluginPath($plugin)
	{
		return  JPATH_PLUGINS.'/'.$plugin->get('_type').'/'.$plugin->get('_name').'/'.$plugin->get('_name');
	}
	
	public static function isMenuExist($link, $cid, $published=null, $alias=null)
	{
		$strQuery	= "SELECT `alias` FROM `#__menu` "
					  ." WHERE `link` LIKE '$link' AND "
					  ." `component_id`={$cid}"
					  . ( ($published !==null) ? " AND `published`= $published " : " ")
					  . ( ($alias !==null) ? " AND `alias`= '$alias' " : " ") 
					  ;

		$db = Rb_Factory::getDBO();
		$db->setQuery($strQuery);
		return $db->loadResult() ? true : false;
	}
	
	public static function getExistingMenu($link, $cid, $published=null, $alias=null)
	{
		$strQuery	= "SELECT * FROM `#__menu` "
					  ." WHERE `link` LIKE '$link' AND "
					  ." `component_id`={$cid}"
					  . ( ($published !==null) ? " AND `published`= $published " : " ")
					  . ( ($alias !==null) ? " AND `alias`= '$alias' " : " ") 
					  ;

		$db = Rb_Factory::getDBO();
		$db->setQuery($strQuery);
		
		return $db->loadObjectList('id');
	}
	
	public static function addMenu($title, $alias, $link, $menu, $cid)
	{
		if(self::isMenuExist($link, $cid, null, $alias)){
			return true;
		}
		
		//if alias is empty then set title
		if(empty($alias)){
			$alias = $title;
		}
		
		$alias = JApplication::stringURLSafe($alias);
		if (trim(str_replace('-', '', $alias)) == ''){
			$alias = Rb_Factory::getDate()->format('Y-m-d-H-i-s');
		}
			
		jimport('joomla.application.application');
		$defaultMenuType	= JApplication::getInstance('site')->getMenu()->getDefault('workaround_joomla_bug')->menutype;
	
		$db = Rb_Factory::getDBO();
	
		// Update the existing menu items.
		$row		= JTable::getInstance ( 'menu', 'JTable' );
		
		$row->id = null; 
		$row->menutype 		= $defaultMenuType;
		$row->title 		= $title;
		$row->alias 		= $alias;
		$row->link 			= $link;
		$row->type 			= 'component';
        $row->language   	= '*';
		$row->published 	= '1';
		$row->component_id 	= $cid;
//		$row->ordering 		= $order;
		$row->parent_id		= 1;
		
				
		if(!$row->check() || !$row->store()){
			return false;
		}

		//update parent id
		$query =   ' UPDATE '. $db->quoteName( '#__menu' ) 
				 . ' SET `parent_id` = '.$db->quote(1).', `level` = ' . $db->quote(1) 
				 . ' WHERE `id` = '.$db->quote($row->id) ;
		$db->setQuery( $query );
		return $db->query();
	} 
	
	public static function getUsertype()
	{
		$db= & JFactory::getDBO();
		$sql = ' SELECT `title`, `id` FROM '.$db->quoteName('#__usergroups')
				.' WHERE '.$db->quoteName('title').' NOT LIKE "%Public%"';
		$db->setQuery($sql);
		return $db->loadColumn();
	}
	
	public static function isAdmin($userId)
	{
		if(!$userId || !Rb_Factory::getUser($userId)->authorise('core.admin')){
			return false;
		}
		
		return true;
	}
	
	public static function getJoomlaGroups()
	{
		$db  = JFactory::getDBO();

		$sql = 'SELECT a.id AS value, a.title AS name, COUNT(DISTINCT b.id) AS level' .
			' FROM #__usergroups AS a' .
			' LEFT JOIN `#__usergroups` AS b ON a.lft > b.lft AND a.rgt < b.rgt' .
			' GROUP BY a.id' .
			' ORDER BY a.lft ASC';
		$db->setQuery($sql);
		$groups =  $db->loadObjectList('value');
		
		
		return $groups;
	}
	
	public static function addUserToGroup($userId, $group)
	{
		jimport('joomla.user.helper');
		return JUserHelper::addUserToGroup($userId, $group);
	}
	
	public static function setUserGroups($userId, $group)
	{
		jimport('joomla.user.helper');
		
		if(!is_array($group)){
			$group = (array)$group;
		}
		
		// if user has any core.admin user group
		// then core.admin groups also be set, remove others
		$usergroups = JUserHelper::getUserGroups($userId);
		foreach($usergroups as $usergroup){
			// if its admin group
			if(JAccess::getAssetRules(1)->allow('core.admin', $usergroup)){
				$group[]= $usergroup;
			}
		}
		
		return JUserHelper::setUserGroups($userId, $group);
	} 
	
	public static function removeUserFromGroup($userId, $group)
	{
		jimport('joomla.user.helper');
		return JUserHelper::removeUserFromGroup($userId, $group);
	}
	
	public static function getArticleElementHtml($control_name, $name, $value)
	{
		// Load the modal behavior script.
		Rb_Html::_('behavior.modal', 'a.modal');

		// Build the script.
		$script = array();
		$script[] = '	function jSelectArticle_'.$control_name.'_'.$name.'(id, title, catid, object) {';
		$script[] = '		document.id("'.$control_name.'_'.$name.'_id").value = id;';
		$script[] = '		document.id("'.$control_name.'_'.$name.'_name").value = title;';
		$script[] = '		SqueezeBox.close();';
		$script[] = '	}';

		// Add the script to the document head.
		Rb_Factory::getDocument()->addScriptDeclaration(implode("\n", $script));


		// Setup variables for display.
		$html	= array();
		$link	= 'index.php?option=com_content&amp;view=articles&amp;layout=modal&amp;tmpl=component&amp;function=jSelectArticle_'.$control_name.'_'.$name;

		$db	= Rb_Factory::getDBO();
		$db->setQuery(
			'SELECT title' .
			' FROM #__content' .
			' WHERE id = '.(int) $value
		);
		$title = $db->loadResult();

		if ($error = $db->getErrorMsg()) {
			Rb_Error::assert(false, $error, Rb_Error::ERROR);
		}

		if (empty($title)) {
			$title = Rb_Text::_('PLG_SYSTEM_RBSL_APP_CONTENT_JOOMLA_SELECT_ARTICLE');
		}
		$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

		// The current user display field.
		$html[] = '<div class="fltlft">';
		$html[] = '  <input type="text" id="'.$control_name.'_'.$name.'_name" value="'.$title.'" disabled="disabled" size="35" />';
		$html[] = '</div>';

		// The user select button.
		$html[] = '<div class="button2-left">';
		$html[] = '  <div class="blank">';
		$html[] = '	<a class="modal" title="'.Rb_Text::_('PLG_SYSTEM_RBSL_APP_CONTENT_JOOMLA_SELECT_ARTICLE').'"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 800, y: 450}}">'.JText::_('PLG_SYSTEM_RBSL_APP_CONTENT_JOOMLA_SELECT_ARTICLE_BUTTON').'</a>';
		$html[] = '  </div>';
		$html[] = '</div>';

		// The active article id field.
		if (0 == (int)$value) {
			$value = '';
		} else {
			$value = (int)$value;
		}

		// class='required' for client side validation
		$class = '';

		$html[] = '<input type="hidden" id="'.$control_name.'_'.$name.'_id"'.$class.' name="'.$control_name.'['.$name.']" value="'.$value.'" />';

		return implode("\n", $html);
	}
	
	public static function getUserTimeZone($config = null, $user = null)
	{
		//$user and $config is for testing purpose only
		$config 	= ($config==null) ? Rb_Factory::getConfig() : $config;
		$user		= ($user==null)   ? Rb_Factory::getUser() 	: $user;
		
		//timezone calculation
		$timezone 	= $config->get('offset');
		$userTz 	= $user->getParam('timezone');
		
        if($userTz) {
            $timeZone = $userTz;
        }
        
		return new DateTimeZone($timezone);
	}
	
	public static function getJoomlaUserGroups($userid)
	{
	  jimport('joomla.user.helper');
	  $usergroups = JUserHelper::getUserGroups($userid);
	  if(PAYPLANS_JVERSION_25)
	  {
	  	$db      = Rb_Factory::getDBO();
	  	$groups  = implode(',', $usergroups);
		$db->setQuery( 'SELECT `title`'
				. ' FROM #__usergroups'
				. ' WHERE `id` IN (' . $groups . ')');
		return $db->loadColumn();	
	  }

	  $joomlagroups = array_keys($usergroups);
	  return $joomlagroups;
	}

	public static function getUserEditLink($user)
	{
		return Rb_Route::_("index.php?option=com_users&task=user.edit&id=".$user->getId(), false);
	}
	
	// in j1.7+ doesn't have sections
	public static function getJoomlaSections()
	{
		return false;
	}
	
	public static function getUsersToSendSystemEmail()
	{
		$rules 	= JAccess::getAssetRules(1);
		$groups = $rules->getData();
		$adminGroup = array_keys($groups['core.admin']->getData());
				
		$db = Rb_Factory::getDBO();
		//get all super administrator
		$query = "SELECT *
				FROM #__users
				WHERE block = 0
				AND sendEmail = 1
				AND id IN(
						SELECT user_id FROM #__user_usergroup_map WHERE group_id IN (".implode(",", $adminGroup).")
				)";
		
		$db->setQuery( $query );
		return $db->loadObjectList();
	}
}

class Rb_HelperJoomla extends Rb_AbstractHelperJoomla  
{
	/**
	 *
	 * @param unknown_type $eventName
	 * @param array $data
	 * @return array
	 */
	static function triggerPlugin($eventName,array &$data =array(), $type ='')
	{
		static $dispatcher = null;

		//load dispatcher if required
		if($dispatcher===null){
			$dispatcher = JDispatcher::getInstance();
		}

		//load payplans plugins
		self::loadPlugins($type);
		//$eventName = $prefix.JString::ucfirst($eventName);
		return $dispatcher->trigger($eventName, $data);
	}

	/**
	 * Loads plugin of given type
	 * @param $type
	 */
	static function loadPlugins($type='payplans')
	{
		static $loaded = array();

		//is already loaded
		if(isset($loaded[$type]))
			return true;

		//import plugins
		JPluginHelper::importPlugin($type);

		//set that plugins are already loaded
		$loaded[$type]= true;
		return true;
	}

	public static function getPluginStatus($element, $folder = 'system')
	{
		return JPluginHelper::isEnabled($folder, $element);
	}
	
	public function getPluginInstance($type, $name)
	{
		
		$observers = JDispatcher::getInstance()->get('_observers');
				
		foreach ($observers as $observer){
			if (is_array($observer) && isset($observer['_type']) && $observer['_type'] == $type && $observer['_name'] == $name){
					return $observer;
			}
			elseif (is_object($observer) && ($observer->get('_type') == $type) && ($observer->get('_name') == $name)){
					return $observer;
			}
		}

		return null;	
	}
		
	public function getLogoutLink($routed=true)
	{
		$link = 'index.php?option='.PAYPLANS_COM_USER;
	
		if(RB_CMS_VERSION_FAMILY==15){
			$link .= '&view=user&task=logout';
		}else{
			
			$link .= '&task=user.logout';
			// add token
			$link .= '&'.JUtility::getToken().'=1';
		}
		
		//set return in url to redirect to home page after logout
		$sitename = JURI::root();
		$returnurl = base64_encode($sitename);
		$link.='&return='.$returnurl;
		
		if($routed){
			return Rb_Route::_($link);
		}
		
		return $link;
	}

	public function getLoginLink($routed=true)
	{
		$link = 'index.php?option='.PAYPLANS_COM_USER;
	
		if(RB_CMS_VERSION_FAMILY==15){
			$link .= '&view=user&task=login';
		}else{
			
			$link .= '&task=login';
			// add token
			$link .= '&'.JUtility::getToken().'=1';
		}
		
		//set return in url to redirect to home page after login
		$sitename  = JURI::getInstance()->toString();
		$returnurl = base64_encode($sitename);
		$link.='&return='.$returnurl;
		
		if($routed){
			return Rb_Route::_($link);
		}
		
		return $link;
	}
	
		/**
        *
        * @return currently used langauge code
        * Also language and locale seperated
        */
       public static function getLanguageCode()
       {
               //RBFW_TODO : fixit for Joomfish

               $lang = Rb_Factory::getLanguage();
               if(RB_CMS_VERSION_FAMILY == '15'){
                       $code = $lang->_lang;
               }else{
                       // as if now no way to collect language code
                       //RBFW_TODO : fixit for 1.7
                       $code = $lang->get('tag');
               }
               
               list ($langCode, $localCode)=explode('-', $code, 2);
               return array('code' => $code, 'language' => $langCode, 'local' => $localCode);
       }
       
    public static function isLocalHost()
	{
		$root = JURI::root();
		if(JString::strpos($root, 'localhost/') === false){
			return false;
		}
		
		return true;
	}	
	
	static function getRootPath()
	{
		// in case of multi-site, we need to refer correct files
		return dirname(dirname(dirname(dirname(RB_PATH_FRAMEWORK))));
	}

	public static function getJoomlaUsers($id = false)
	{
		$query = new Rb_Query();
		if(!$id){
		return $query->select(' `id`, `name`, `username` ')
					 ->from('`#__users`')
					 ->dbLoadQuery()
					 ->loadObjectList('id');
		}
		if(is_array($id)==false){
			$id = array($id);
		}
		$ids = implode(',', $id);
		return $query->select(' `id`, `name`, `username` ')
					 ->from('`#__users`')
					 ->where('`id` IN ('.$ids.')')
					 ->dbLoadQuery()
					 ->loadObjectList('id');
	}
	
	// get joomla categories
	public static function getJoomlaCategories()
	{
		$db 	= PayplansFactory::getDBO();
		
		$query = 'SELECT  `id`  as category_id, title'
			 	. ' FROM #__categories'
			 	;
	 	$db->setQuery( $query );
	 	return $db->loadObjectList('category_id');
	}
	
	// get joomla articles
	public static function getJoomlaArticles()
	{
		$query = new Rb_Query();
		return $query->select(' `id`, `title` ')
					 ->from('`#__content`')
					 ->dbLoadQuery()
					 ->loadObjectList('id');
	}
	
}
