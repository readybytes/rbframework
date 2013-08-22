<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die('Restricted access' );

class Rb_FileTree
{
	static $tree_class_not_loaded=true;
	
	static public function generateFileTree($dirs)
	{
		$root     =  JPATH_ROOT ;// dirname(dirname(dirname(dirname(__FILE__))));
		
		$gtree = array();
		
		foreach($dirs as $dir){
	    	self::folder_tree('*.php', 0, $dir, -1, 0 , $root, $gtree);	
		}
	        
	    $data = var_export($gtree,true);
	    ob_start();
	    ?>
	    	if(defined('_JEXEC')===false) die('Restricted access' );
	    	
	    	class Rb_FileTreeProvider
	    	{
	    		static $_filetree =  <?php echo $data; ?> ;
		    	static $_fileroot =  '<?php echo $root; ?>' ;
		    	
		    	static public function get($index)
    			{
    				$index = str_replace(self::$_fileroot, '', $index);
    				$index = DS.trim($index, DS).DS;
    				if( isset(self::$_filetree[$index]) ) {
    					return self::$_filetree[$index];
    				}
    
       				return false;
    			}
			}
	    <?php 
	    $filecontent = '<?php '.ob_get_contents();
	    ob_end_clean();
	    
	    $tmpPath = JFactory::getConfig()->getValue('config.tmp_path');
	    $filename = $tmpPath.'/_filetree.php';
	    JFile::write($filename, $filecontent);
	}
	
	static public function getFileTree($index)
	{
		//return false;
		
		if(self::$tree_class_not_loaded)
		{
			$tmpPath = JFactory::getConfig()->get('config.tmp_path');
			$file = $tmpPath.'/_filetree.php';
		    if(JFile::exists($file) == false){
		    	$file = dirname(__FILE__).'/_filetree.php'; 
		    }
		    
			include_once $file;
			self::$tree_class_not_loaded = false;
		}
		
		return Rb_FileTreeProvider::get($index);
	}
	
	static private function folder_tree($pattern = '*', $flags = 0, $path = false, $depth = 0, $level = 0, &$root, &$gtree)
	{     
	        $correct_path = str_replace($root,'',$path);
			$gtree[$correct_path] = array('files'=>array(), 'folders'=> array());
		    $files = glob($path.$pattern, $flags);
		    $fileCount= count($files);
		    for($i=0 ; $i < $fileCount ; $i++){
				$correct_file_path=str_replace($path,'',$files[$i]);
				unset($files[$i]);
				$gtree[$correct_path]['files'][] = $correct_file_path;
	   	    }
	
		    $paths = glob($path.'*', GLOB_ONLYDIR|GLOB_NOSORT);
	
		    $subtree = array();
		    if (!empty($paths) && ($level < $depth || $depth == -1)) {
		    	$level++;
			
		    	foreach ($paths as $sub_path) {
					$correct_folder_path = str_replace($path,'',$sub_path);
					$gtree[$correct_path]['folders'][]= $correct_folder_path;
	    			self::folder_tree($pattern, $flags, $sub_path.DIRECTORY_SEPARATOR, $depth, $level, $root, $gtree);
		    	}	
		    }	     
	}

}