<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

class Rb_HelperLoader
{
	//here we will try to register all MC and libs and helpers
	static function addAutoLoadFolder($folder, $type, $prefix='Rb_')
	{
		//echo "<br /> Loading folder $folder <br />";
		
		if(empty($folder))
			return;
			
		if (!is_dir($folder))
			return;
		
		$filetree = Rb_FileTree::getFileTree($folder);
		if(is_array($filetree)){
			$files		=	isset($filetree['files'])?$filetree['files']:false;
			$folders	=	isset($filetree['folders'])?$filetree['folders']:false;
		}else{
			$files		=	JFolder::files($folder,".php$");
			$folders	=	JFolder::folders($folder);
		}
		
			
		if(is_array($files) && count($files)>0){
			foreach($files as $file ){
				// 	folder name starts with underscore, false means no underscore 
				if('_' == substr($file, 0, 1)){
					continue;
				}
				
				//e.g. Rb_Controller + Product
				// Rb_Performance : As Class names are not case sensitive so no need to use JString::ucfirst 
				$className 	= $prefix
							. $type
							. JFile::stripExt($file);
				//echo " <br /> Loading $className ".$folder.'/'.$file;
				JLoader::register($className, $folder.'/'.$file);
			}
		}
		
		if(is_array($folders) && count($folders)> 0){
			foreach($folders as $subfolder ){
				// folder name starts with underscore, false means no underscore 
				if('_' == substr($subfolder, 0, 1)){
					continue;
				}
				$subtype		= 	$type.$subfolder;
				$subfolderpath	=	$folder.'/'.$subfolder;
				self::addAutoLoadFolder($subfolderpath , $subtype , $prefix);
			}
		}
	}
	

	static function addAutoLoadFile($fileName, $className)
	{
		JLoader::register($className, $fileName);
	}

	/* View are stored very differently */
	static function addAutoLoadViews($baseFolder, $format=RB_REQUEST_DOCUMENT_FORMAT, $prefix='Rb_')
	{
		$filetree = Rb_FileTree::getFileTree($baseFolder);
		if(is_array($filetree)){
			$files		=	isset($filetree['files'])?$filetree['files']:false;
			$folders	=	isset($filetree['folders'])?$filetree['folders']:false;
		}else{
			$folders	=	JFolder::folders($baseFolder);
		}

		foreach($folders as $folder )
		{
			//$className 	= $prefix.'View'.$folder;
			//JLoader::register($className, $baseFolder.'/'.$folder.'/view.php');
			
			$className 	= $prefix.'View'.$folder;
			$fileName	= "view.$format.php";
			JLoader::register($className, $baseFolder.'/'.$folder.'/'.$fileName);
		}
	}

	static function includeFolder($folder, $filter=null)
	{
		foreach( JFolder::files($folder) as $file )
		{
			if($filter && in_array($file,$filter))
				continue;
			require_once  $folder.'/'.$file;
		}
	}
}