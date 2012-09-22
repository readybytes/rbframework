<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

/**
 * This files is modified version of JDatabaseQuery Class from Joomla 1.6
 * @version		$Id: databasequery.php 14571 2010-02-04 07:07:47Z eddieajau $
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 */


/**
 * Query Element Class.
 */
class Rb_QueryElement
{
	protected $_name = null;
	protected $_elements = null;
	protected $_glue = null;

	/**
	 * Constructor.
	 *
	 * @param	string	The name of the element.
	 * @param	mixed	String or array.
	 * @param	string	The glue for elements.
	 */
	public function __construct($name, $elements, $glue=',')
	{
		$this->_elements	= array();
		$this->_name		= $name;
		$this->_glue		= $glue;

		$this->append($elements);
	}

	public function __toString()
	{
		return PHP_EOL.$this->_name.' '.implode($this->_glue, $this->_elements);
	}

	/**
	 * Appends element parts to the internal list.
	 * @param	mixed	String or array.
	 */
	public function append($elements)
	{
		if (is_array($elements)) {
			$this->_elements = array_unique(array_merge($this->_elements, $elements));
		} else {
			$this->_elements = array_unique(array_merge($this->_elements, array($elements)));
		}
	}
}

/**
 * Query Building Class
 */
class Rb_Query
{
	protected $_type = '';
	protected $_select = null;
	protected $_delete = null;
	protected $_update = null;
	protected $_insert = null;
	protected $_truncate = null;
	protected $_drop = null;
	protected $_from = null;
	protected $_join = null;
	protected $_set = null;
	protected $_where = null;
	protected $_group = null;
	protected $_having = null;
	protected $_order = null;
	
	/** @var object The where element */
	protected $_limit = 0;
	protected $_offset = 0;

	/**
	 * getters and setters
	 * @param $prop
	 * @return Rb_QueryElement
	 */
	public function getProp($prop)
	{
		return $this->$prop;
	}

	/**
	 *
	 * @param $prop
	 * @param $value
	 * @return Rb_Query
	 */
	public function setProp($prop, $value)
	{
		$this->$prop = $value;
		return $this;
	}

	/**
	 * @return JDatabase
	 */
	function dbLoadQuery($queryPrefix="", $querySuffix="")
	{
		//RBFW_TODO : Add limit and limitstart support in query class
		$db = Rb_Factory::getDBO();
		$db->setQuery($queryPrefix.(string)$this.$querySuffix, $this->_offset,$this->_limit);
		return $db;
	}

	/**
	 * Clear data from the query or a specific clause of the query.
	 * @param	string	Optionally, the name of the clause to clear,
	 * 				    or nothing to clear the whole query.
	 * @return Rb_Query
	 */
	public function clear($clause = null)
	{
		switch ($clause) {
			case 'select':
				$this->_select = null;
				$this->_type = null;
				break;
			case 'delete':
				$this->_delete = null;
				$this->_type = null;
				break;
			case 'update':
				$this->_update = null;
				$this->_type = null;
				break;
			case 'insert':
				$this->_insert = null;
				$this->_type = null;
				break;
			case 'from':
				$this->_from = null;
				break;
			case 'join':
				$this->_join = null;
				break;
			case 'set':
				$this->_set = null;
				break;
			case 'where':
				$this->_where = null;
				break;
			case 'group':
				$this->_group = null;
				break;
			case 'having':
				$this->_having = null;
				break;
			case 'order':
				$this->_order = null;
				break;
			case 'limit':
 				// reset oddset also whle reseting limit
				$this->_limit = null;
				$this->_offset = null;
				break;
			default:
				$this->_type = null;
				$this->_select = null;
				$this->_truncate = null;
				$this->_drop = null;
				$this->_delete = null;
				$this->_udpate = null;
				$this->_insert = null;
				$this->_from = null;
				$this->_join = null;
				$this->_set = null;
				$this->_where = null;
				$this->_group = null;
				$this->_having = null;
				$this->_order = null;
				$this->_limit = null;
				$this->_offset = null;
				break;
		}

		return $this;
	}


	/**
	 * @param	mixed	A string or an array of field names
	 * @return Rb_Query
	 */
	public function select($columns)
	{
		$this->_type = 'select';
		if (is_null($this->_select)) {
			$this->_select = new Rb_QueryElement('SELECT', $columns);
		} else {
			$this->_select->append($columns);
		}

		return $this;
	}

	/**
	 * @return Rb_Query
	 */
	public function delete()
	{
		$this->_type = 'delete';
		$this->_delete = new Rb_QueryElement('DELETE', array(), '');
		return $this;
	}
	
	public function truncate($table)
	{
		$this->_type 	 = 'truncate';
		$this->_truncate = new Rb_QueryElement('TRUNCATE TABLE ', array($table), '');
		return $this;
	}
	
	public function drop($table)
	{
		$this->_type 	 = 'drop';
		$this->_drop = new Rb_QueryElement('DROP TABLE IF EXISTS ', $table, '');
		return $this;
	}

	/**
	 * @param	mixed	A string or array of table names
	 * @return Rb_Query
	 */
	public function insert($tables)
	{
		$this->_type = 'insert';
		$this->_insert = new Rb_QueryElement('INSERT INTO', $tables);
		return $this;
	}

	/**
	 * @param	mixed	A string or array of table names
	 * @return Rb_Query
	 */
	public function update($tables)
	{
		$this->_type = 'update';
		$this->_update = new Rb_QueryElement('UPDATE', $tables);
		return $this;
	}

	/**
	 * @param	mixed	A string or array of table names
	 * @return Rb_Query
	 */
	public function from($tables)
	{
		if (is_null($this->_from)) {
			$this->_from = new Rb_QueryElement('FROM', $tables);
		} else {
			$this->_from->append($tables);
		}

		return $this;
	}

	/**
	 * @param	string
	 * @param	string
	 * @return Rb_Query
	 */
	public function join($type, $conditions)
	{
		if (is_null($this->_join)) {
			$this->_join = array();
		}
		$this->_join[] = new Rb_QueryElement(strtoupper($type) . ' JOIN', $conditions);

		return $this;
	}

	/**
	 * @param	string
	 * @return Rb_Query
	 */
	public function innerJoin($conditions)
	{
		$this->join('INNER', $conditions);

		return $this;
	}

	/**
	 * @param	string
	 * @return Rb_Query
	 */
	public function outerJoin($conditions)
	{
		$this->join('OUTER', $conditions);

		return $this;
	}

	/**
	 * @param	string
	 * @return Rb_Query
	 */
	public function leftJoin($conditions)
	{
		$this->join('LEFT', $conditions);

		return $this;
	}

	/**
	 * @param	string
	 * @return Rb_Query
	 */
	public function rightJoin($conditions)
	{
		$this->join('RIGHT', $conditions);

		return $this;
	}

	/**
	 * @param	mixed	A string or array of conditions
	 * @param	string
	 * @return Rb_Query
	 */
	public function set($conditions, $glue=',')
	{
		if (is_null($this->_set)) {
			$glue = strtoupper($glue);
			$this->_set = new Rb_QueryElement('SET', $conditions, "\n\t$glue ");
		} else {
			$this->_set->append($conditions);
		}

		return $this;
	}

	/**
	 * @param	mixed	A string or array of where conditions
	 * @param	string
	 * @return Rb_Query
	 */
	public function where($conditions, $glue='AND')
	{
		if (is_null($this->_where)) {
			$glue = strtoupper($glue);
			$this->_where = new Rb_QueryElement('WHERE', $conditions, " $glue ");
		} else {
			$this->_where->append($conditions);
		}

		return $this;
	}

	/**
	 * @param	mixed	A string or array of ordering columns
	 * @return Rb_Query
	 */
	public function group($columns)
	{
		if (is_null($this->_group)) {
			$this->_group = new Rb_QueryElement('GROUP BY', $columns);
		} else {
			$this->_group->append($columns);
		}

		return $this;
	}

	/**
	 * @param	mixed	A string or array of columns
	 * @param	string
	 * @return Rb_Query
	 */
	public function having($conditions, $glue='AND')
	{
		if (is_null($this->_having)) {
			$glue = strtoupper($glue);
			$this->_having = new Rb_QueryElement('HAVING', $conditions, " $glue ");
		} else {
			$this->_having->append($conditions);
		}

		return $this;
	}

	/**
	 * @param	mixed	A string or array of ordering columns
	 * @return Rb_Query
	 */
	public function order($columns)
	{
		if (is_null($this->_order)) {
			$this->_order = new Rb_QueryElement('ORDER BY', $columns);
		} else {
			$this->_order->append($columns);
		}

		return $this;
	}
	
	/**
	 * @param	mixed	limit
	 * @param	mixed	limitstarts
	 * @return Rb_Query
	 */
    public function limit($limit=0, $offset=0)
	{
		//IMP : Do not apply limit if it is Zero
		if($limit !=0 ){
			$this->_limit 	= $limit;
			$this->_offset 	= $offset;
		}
		return $this;
	}
	

	/**
	 * @return	string	The completed query
	 */
	public function __toString()
	{
		$query = '';

		switch ($this->_type) {
			case 'select':
				$query .= (string) $this->_select;
				$query .= (string) $this->_from;
				if ($this->_join) {
					// special case for joins
					foreach ($this->_join as $join) {
						$query .= (string) $join;
					}
				}
				if ($this->_where) {
					$query .= (string) $this->_where;
				}
				if ($this->_group) {
					$query .= (string) $this->_group;
				}
				if ($this->_having) {
					$query .= (string) $this->_having;
				}
				if ($this->_order) {
					$query .= (string) $this->_order;
				}
				break;

			case 'delete':
				$query .= (string) $this->_delete;
				$query .= (string) $this->_from;
				if ($this->_where) {
					$query .= (string) $this->_where;
				}
				break;

			case 'update':
				$query .= (string) $this->_update;
				$query .= (string) $this->_set;
				if ($this->_where) {
					$query .= (string) $this->_where;
				}
				break;

			case 'insert':
				$query .= (string) $this->_insert;
				$query .= (string) $this->_set;
				if ($this->_where) {
					$query .= (string) $this->_where;
				}
				break;
				
			case 'truncate':
				$query .= (string) $this->_truncate;
				break;

			case 'drop':
				$query .= (string) $this->_drop;
				break;
		}

		return $query;
	}
	
	public function getClone()
	{
		return unserialize(serialize($this));
	}
}