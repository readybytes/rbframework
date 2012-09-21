<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		PayPlans
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

class Rb_AbstractJ15HelperJoomla extends Rb_AbstractHelperJoomlaBase
{
	public static function changePluginState($element, $folder = 'system', $state=parent::ENABLE)
	{
		$db		= JFactory::getDBO();

		$query	= 'UPDATE ' . $db->nameQuote('#__plugins' )
				. ' SET '   . $db->nameQuote('published').'='.$db->Quote($state)
		        . ' WHERE ' . $db->nameQuote('element').'='.$db->Quote($element)
		        . ' AND ' . $db->nameQuote('folder').'='.$db->Quote($folder)
		        ;

		$db->setQuery($query);
		return $db->query();
	}
	
	public static function getPluginPath($plugin)
	{
		$path  = JPATH_PLUGINS.'/'.$plugin->get('_type').'/'.$plugin->get('_name');
		return $path;
	}
	
	public static function isMenuExist($link, $cid, $published=null, $alias=null)
	{
		$strQuery	= "SELECT `alias` FROM `#__menu` "
					  ." WHERE `link` LIKE '$link' AND "
					  ."`componentid`={$cid}"
					  . ( ($published !==null) ? " AND `published`= $published " : " ")
					  . ( ($alias !==null) ? " AND `alias`= '$alias' " : " ") 
					  ;
		$db = Rb_Factory::getDBO();
		$db->setQuery($strQuery);
		return $db->loadResult() ? true : false;
	}
	
	public static function addMenu($title, $alias, $link, $menu='mainmenu', $cid)
	{
		if(self::isMenuExist($link, $cid)){
			return true;
		}
		
		$db = Rb_Factory::getDBO();	
		$strQuery	= "INSERT IGNORE INTO `#__menu` (`menutype`, `name`, `alias`, `link`, `type`, `published`, `parent`, `componentid`, `sublevel`, `ordering`, `checked_out`, `checked_out_time`, `pollid`, `browserNav`, `access`, `utaccess`, `params`, `lft`, `rgt`, `home`) "
					  ."VALUES ('$menu', '$title', '$alias', '$link', 'component', 1, 0, $cid, 0, 500, 0, '0000-00-00 00:00:00', 0, 0, 0, 0, '', 0, 0, 0)"
					  ;
		
		$db->setQuery($strQuery);
		return $db->query();
	} 
	
	public static function getUsertype()
	{
		$db= & JFactory::getDBO();
		$sql = ' SELECT `name` FROM '.$db->nameQuote('#__core_acl_aro_groups')
				.' WHERE '.$db->nameQuote('name').' NOT LIKE "%USERS%"'
				.' AND '.$db->nameQuote('name').' NOT LIKE  "%ROOT%"'
				.' AND '.$db->nameQuote('name').' NOT LIKE  "%Public%"';
		$db->setQuery($sql);
		return $db->loadResultArray();
	}
	
	public static  function isAdmin($userId)
	{
		$userType = Rb_Factory::getUser($userId)->usertype;
		if($userType == 'Super Administrator'){
			return true;
		}
		return false;
	}
	
	public static function getJoomlaGroups()
	{
		$db  = JFactory::getDBO();
		
		$sql = ' SELECT * FROM '.$db->nameQuote('#__core_acl_aro_groups') 
			.' WHERE '.$db->nameQuote('name').' NOT LIKE "%USERS%"' 
			.' AND '.$db->nameQuote('name').' NOT LIKE  "%ROOT%"'
			.' AND '.$db->nameQuote('name').' NOT LIKE  "%Public%"' 
			//.' AND '.$db->nameQuote('name').' NOT LIKE  "%Administrator%"'
			.' AND '.$db->nameQuote('name').' NOT LIKE  "%Super Administrator%"' ;
			
		$db->setQuery($sql);
		return $db->loadObjectList('value');	
	}
	
	public static function addUserToGroup($userId, $group)
	{
		$user = Rb_Factory::getUser($userId);
		
		if(empty($group)){
			return true;
		}
		// when subscriber is super administrator do not change its usertype and gid
		if(Rb_HelperJoomla::isAdmin($userId) ==false){
			$authorize	= Rb_Factory::getACL();
			$user->set('usertype', $group);
			$user->set('gid', $authorize->get_group_id( '', $group, 'ARO' ));
		}

		return $user->save();
	}
	
	public static function setUserGroups($userId, $group)
	{
		return self::addUserToGroup($userId, $group);
	} 
	
	public static function getArticleElementHtml($control_name, $name, $value)
	{
		global $mainframe;

		$db			=& JFactory::getDBO();
		$doc 		=& JFactory::getDocument();
		$template 	= $mainframe->getTemplate();
		$fieldName	= $control_name.'['.$name.']';
		$article =& JTable::getInstance('content');
		if ($value) {
			$article->load($value);
		} else {
			$article->title = JText::_('COM_PAYPLANS_APP_CONTENT_JOOMLA_SELECT_ARTICLE');
		}

		$js = "
		function jSelectArticle(id, title, object) {
			document.getElementById(object + '_id').value = id;
			document.getElementById(object + '_name').value = title;
			document.getElementById('sbox-window').close();
		}";
		$doc->addScriptDeclaration($js);

		$link = 'index.php?option=com_content&amp;task=element&amp;tmpl=component&amp;object='.$name;

		JHTML::_('behavior.modal', 'a.modal');
		$html = "\n".'<div style="float: left;"><input style="background: #ffffff;" type="text" id="'.$name.'_name" value="'.htmlspecialchars($article->title, ENT_QUOTES, 'UTF-8').'" disabled="disabled" /></div>';
//		$html .= "\n &nbsp; <input class=\"inputbox modal-button\" type=\"button\" value=\"".JText::_('COM_PAYPLANS_APP_CONTENT_JOOMLA_SELECT_ARTICLE_BUTTON')."\" />";
		$html .= '<div class="button2-left"><div class="blank"><a class="modal" title="'.JText::_('COM_PAYPLANS_APP_CONTENT_JOOMLA_SELECT_ARTICLE').'"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 650, y: 375}}">'.JText::_('COM_PAYPLANS_APP_CONTENT_JOOMLA_SELECT_ARTICLE_BUTTON').'</a></div></div>'."\n";
		$html .= "\n".'<input type="hidden" id="'.$name.'_id" name="'.$fieldName.'" value="'.(int)$value.'" />';

		return $html;
	}
	
	public static function getUserTimeZone($config = null, $user = null)
	{
		//$user and $config is for testing purpose only
		$config = ($config==null) ? Rb_Factory::getConfig() 	: $config;
		$my		= ($user==null)   ? Rb_Factory::getUser() 	: $user;
		
		//default offset
		$offset = $config->offset;
		
		//if user is logged in, then do it as per him
		if($my->id){
			$offset = $my->getParam('timezone', $offset);
		}
		
		return $offset;
	}
	public static function getJoomlaUserGroups($userid)
	{
		$db = Rb_Factory::getDBO();
		$db->setQuery( 'SELECT `usertype`'
			. ' FROM #__users'
			. ' WHERE `id` = \'' . $userid . '\'');
	   return  $db->loadResultArray();	
	}

	public static function getUserEditLink($user)
	{
		if(Rb_Factory::getApplication()->isAdmin()){
			return Rb_Route::_("index.php?option=com_users&view=user&task=edit&cid=".$user->getId(), false);
		}
		return Rb_Route::_("index.php?option=com_user&view=user&task=edit&cid=".$user->getId(), false);
	}
	
	// get Joomla section
	public static function getJoomlaSections()
	{
		$db 	= PayplansFactory::getDBO();
		
		$query = 'SELECT  `id`  as section_id, title'
			 	. ' FROM #__sections'
			 	;
	 	$db->setQuery( $query );
	 	return $db->loadObjectList('section_id');
	}
	
	public static function getUsersToSendSystemEmail()
	{
		$db = Rb_Factory::getDBO();
		//get all super administrator
		$query = "SELECT *
				FROM #__users
				WHERE block = 0
				AND sendEmail = 1
				AND usertype = 'Super Administrator'" ;
		
		$db->setQuery( $query );
		return $db->loadObjectList();
	}
}

class Rb_AbstractHelperJoomla extends Rb_AbstractJ15HelperJoomla{}
