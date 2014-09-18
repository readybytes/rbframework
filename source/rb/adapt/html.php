<?php
/**
* @copyright	Copyright (C) 2009 - 2014 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		support+rb@readybytes.in
*/
if(defined('_JEXEC')===false) die('Restricted access' );

if(RB_CMS_ADAPTER==='j16'){
	class Rb_AdaptHtml extends Rb_AdaptJ16Html
	{}
}

if(RB_CMS_ADAPTER==='j35'){
	class Rb_AdaptHtml extends Rb_AdaptJ35Html
	{}
}
