<?php
/**
* @copyright	Copyright (C) 2009 - 2009 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		PayPlans
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

class XiHelperLoader
{
	//here we will try to register all MC and libs and helpers
	static function addAutoLoadFolder($folder, $type, $prefix='Xi')
	{
		if(empty($folder))
			return;
			
		if (!is_dir($folder))
			return;
		
		$filetree = XiFileTree::getFileTree($folder);
		if(is_array($filetree)){
			$files		=	isset($filetree['files'])?$filetree['files']:false;
			$folders	=	isset($filetree['folders'])?$filetree['folders']:false;
		}else{
			$files		=	JFolder::files($folder,".php$");
			$folders	=	JFolder::folders($folder);
		}
		
			
		if(is_array($files)){
			foreach($files as $file ){
				//e.g. XiController + Product
				// XiPerformance : As Class names are not case sensitive so no need to use JString::ucfirst 
				$className 	= $prefix
							. $type
							. JFile::stripExt($file);
				//echo " <br /> Loading $className ".$folder.DS.$file;
				JLoader::register($className, $folder.DS.$file);
			}
		}
		
		if(is_array($folders)){
			foreach($folders as $subfolder ){
				$subtype		= 	$type.$subfolder;
				$subfolderpath	=	$folder.DS.$subfolder;
				self::addAutoLoadFolder($subfolderpath , $subtype , $prefix);
			}
		}
	}
	

	static function addAutoLoadFile($fileName, $className)
	{
		JLoader::register($className, $fileName);
	}

	/* View are stored very differently */
	static function addAutoLoadViews($baseFolders, $format, $prefix='Xi')
	{
		$filetree = XiFileTree::getFileTree($baseFolders);
		if(is_array($filetree)){
			$folders	=	isset($filetree['folders'])?$filetree['folders']:false;
		}else{
			$folders	=	JFolder::folders($baseFolders);
		}

		foreach($folders as $folder )
		{
			//e.g. XiController + Product
			$className 	= $prefix
						. 'View'
						. $folder;

			if($format==='ajax') $format = 'html';
			$fileName	= "view.$format.php";
			JLoader::register($className, $baseFolders.DS.$folder.DS.$fileName);
		}
	}

	static function includeFolder($folder, $filter=null)
	{
		foreach( JFolder::files($folder) as $file )
		{
			if($filter && in_array($file,$filter))
				continue;
			require_once  $folder.DS.$file;
		}
	}
}