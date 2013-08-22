<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Router
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die('Restricted access' );

abstract class Rb_Router
{
	/** 
	 * @var Rb_Extension
	 */
    protected $_component = '';
    protected $_menus = null;
    
    public function __construct()
    {
    	// setup extension naming convention
		$this->_component = Rb_Extension::getInstance($this->_component);
    }
    
	function getName()
	{
		$name = $this->_name;
		if (empty( $name ))
		{
			$r = null;
			Rb_Error::assert(preg_match('/Router(.*)/i', get_class($this), $r) , 'RB_ROUTER : Not able to parse class name :', get_class($this), Rb_Error::ERROR);

			$name = strtolower( $r[1] );
		}
		return $name;
	}
    
    public static function getInstance($name)
    {
        return Rb_Factory::getInstance($name, '', $this->_component->getPrefixClass());
    }
    
    // Load component menu records
    public function _getMenus()
    {
        if($this->_menus ===null){
			$this->_menus 	= JSite::getMenu()->getItems('component_id',JComponentHelper::getComponent($this->_option)->id);
		}

		return $this->_menus;
    }

    
    // find maximum matching menu to the given query
    protected  function _findMatchCount($menu, $query)
    {
            $count = 0;
            foreach($menu as $var=>$value)
            {
            		if(empty($menu[$var])){
            			continue;
            		}
                    //variable not requested OR
                    //variable exist but do not match
                    if(!isset($query[$var]) || $menu[$var] !== $query[$var]){
                            /* 
                            * return 0, because if some variables are in conflict
                            * then variable appended in query will be desolved during parsing 
                            * e.g.
                            * 
                            * index.php?option=com_payplans&view=plan
                            * index.php/subscribe
                            * 
                            * index.php?option=com_payplans&view=plan&task=subscribe&plan_id=1
                            * index.php/subscribe1
                            * 
                            * index.php?option=com_payplans&view=plan&task=subscribe&plan_id=2
                            * index.php/subscribe1?plan_id=2   <== *** WRONG ***
                            * index.php/subscribe?task=subscribe&plan_id=2   <== *** RIGHT ***
                            */ 
                            return 0;
                    }

                    $count++;
            }
            return $count;
    }

    /**
      *Find the itemId for the given query, and set it into query variable
     * @param Array $query
     * @param Array $menus
     * @return integer : ItemId or null
     */
    public function getSelectedMenu(&$query, $menus)
    {        
        //If item id is not set then we need to extract those
        $selMenu = null;
        
        //IMP : Itemid can be sent of current page itself, rather then , which should not be used        
        if($menus){
            $count      = 0;

			$lang_tag = JFactory::getLanguage()->getTag();
            foreach($menus as $menu){
            	$matching = $this->_findMatchCount($menu->query,$query);
            	
             	// if language is set on menu
                if(isset($menu->language)){
                    $menu->language = trim($menu->language);

	                if ($matching > 0 && $menu->language == $lang_tag) {
            	    	//count matching
            	   		$matching++;
            	    }
            	}
                
                //current menu matches more
                if($matching > $count){
                    $count		= $matching;
                    $selMenu 	= $menu;
                }
            }
        }
        
        //assig ItemID of selected menu if any
        if($selMenu !== null){
            $query['Itemid'] = $selMenu->id;
        }
        
        //finally selected menu is
        if($selMenu === null){
            $selMenu = new stdClass();
            $selMenu->query = array();
            unset($query['Itemid']);
        }

        return $selMenu;
    }
    

    protected function _slugify($query, $var)
    {
       return $query[$var];
    }
    
    protected function _deSlugify($var, $value, $parts)
    {
       return $value;
    }
    
    public function build( &$query )
    {
            $segments = array();
            
            $temp_added_vars = array();
            // if itemId is the first key, then these are menu links, only then consider it.
            // else the itemID might be of current page, not for the link
            if(isset($query['Itemid']) && (array_shift(array_keys($query)) === 'Itemid') ){
                // if item-id exists, then pick the var from menu and put into query, if not exist already
     			$item = JSite::getMenu()->getItem($query['Itemid']);
            	foreach($item->query as $var=>$value){
            		if(!isset($query[$var])){
						$query[$var]= $value;
						$temp_added_vars[]=$var;
					}
            	}
            }
            
            //find the selected menu
            $selMenu = $this->getSelectedMenu($query, $this->_getMenus());
            
            // clean the added variables
    		foreach($temp_added_vars as $var){
				unset($query[$var]);
            }
            
            //can we process the route further
            $key = @$query['view'] .'/'. @$query['task'];
            $route=$this->_routes($key);
            
            $route = array_merge(array('view', 'task'), $route);
            //remove not-required variables, which can be calculated from URL itself
            foreach($route as $var){
                
                //variable not requested
                if(!isset($query[$var])){
                    continue;
                }

                //variable not exist in menu
                if(!isset($selMenu->query[$var])){
                    
                    // var exist in request
                    if(isset($query[$var])){
                        $slug=$this->_slugify($query, $var);
                        unset($query[$var]);
                        $segments[] = $slug;
                    }
                    
                    continue;
                }

                //exist & match
                if($selMenu->query[$var] === $query[$var]){
                    unset($query[$var]);
                }else{
                	$slug=$this->_slugify($query, $var);
                    unset($query[$var]);
                	$segments[] = $slug;
                }
            }

            return $segments;
    }

    /**
    * @param	array	A named array
    * @param	array
    *
    * Formats:
    */
    public function parse( &$segments )
    {
        // initialize
        $parts = array();           
        
        // find if any menu selected
        $item = JFactory::getApplication()->getMenu()->getActive();
        
        // find view
        if(isset($item->query['view'])){
        	$view = $item->query['view'];
        }else{
        	$view = array_shift($segments); 
        }
        
        // find task
    	if(isset($item->query['task'])){
        	$task = $item->query['task'];
        }else{
        	$task = array_shift($segments); 
        }
        
        $key = $view.'/'.$task;
        $route=$this->_routes($key);

        //remove not-required variables, which can be calculated from URL itself
        
        $parts = array('view'=>$view , 'task' => $task);
        foreach($route as $var){
            $value = array_shift($segments);
            $value = $this->_deSlugify($var, $value, $parts);
            $parts[$var]=$value;
        }
        
       return $parts;
    }
}
