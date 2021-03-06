<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Database result wrapper.
 *
 * @package    Database
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
abstract class Kohana_Database_Result implements Countable, Iterator,SeekableIterator, ArrayAccess {

	// Executed SQL for this result
	protected $_query;

	// Raw result resource
	protected $_result;

	// Total number of rows and current row
	protected $_total_rows  = 0;
	protected $_current_row = 0;

	// Return rows as an object or associative array
	protected $_as_object;

	/**
	 * Sets the total number of rows and stores the result locally.
	 *
	 * @param   mixed   query result
	 * @param   string  SQL query
	 * @return  void
	 */
	public function __construct($result, $sql, $as_object)
	{
		// Store the result locally
		$this->_result = $result;

		// Store the SQL locally
		$this->_query = $sql;

		// Results as objects or associative arrays
		$this->_as_object = $as_object;
	}

	/**
	 * Result destruction cleans up all open result sets.
	 */
	abstract public function __destruct();

	/**
	 * Return all of the rows in the result as an array.
	 *
	 * @param   string  column for an associative keys
	 * @param   string  column for an associative values
	 * @return  array
	 */
	public function as_array($key = NULL, $value = NULL)
	{
		$results = array();

		foreach ($this as $row)
		{
			if ($key)
			{
				$row_key = $this->_as_object ? $row->$key : $row[$key];
			}

			if ($value)
			{
				$row_value = $this->_as_object ? $row->$value : $row[$value];
			}
			else
			{
				$row_value = $row;
			}

			if (isset($row_key))
			{
				$results[$row_key] = $row_value;
			}
			else
			{
				$results[] = $row_value;
			}
		}

		return $results;
	}

	/**
	 * Return the named column from the current row.
	 *
	 * @param   string  column to get
	 * @param   mixed   default value if the column does not exist
	 * @return  mixed
	 */
	public function get($name, $default = NULL)
	{
		$row = $this->current();

		if ($this->_as_object)
		{
			if (isset($row->$name))
				return $row->$name;
		}
		else
		{
			if (isset($row[$name]))
				return $row[$name];
		}

		return $default;
	}

	/**
	 * Countable: count
	 */
	public function count()
	{
		return $this->_total_rows;
	}

	/**
	 * ArrayAccess: offsetExists
	 */
	public function offsetExists($offset)
	{
		return ($offset >= 0 AND $offset < $this->_total_rows);
	}

	/**
	 * ArrayAccess: offsetGet
	 */
	public function offsetGet($offset)
	{
		if ( ! $this->seek($offset))
			return NULL;

		return $this->current();
	}

	/**
	 * ArrayAccess: offsetSet
	 *
	 * @throws  Kohana_Database_Exception
	 */
	final public function offsetSet($offset, $value)
	{
		throw new Kohana_Exception('Database results are read-only');
	}

	/**
	 * ArrayAccess: offsetUnset
	 *
	 * @throws  Kohana_Database_Exception
	 */
	final public function offsetUnset($offset)
	{
		throw new Kohana_Exception('Database results are read-only');
	}

	/**
	 * Iterator: key
	 */
	public function key()
	{
		return $this->_current_row;
	}

	/**
	 * Iterator: next
	 */
	public function next()
	{
		++$this->_current_row;
		return $this;
	}

	/**
	 * Iterator: prev
	 */
	public function prev()
	{
		--$this->_current_row;
		return $this;
	}

	/**
	 * Iterator: rewind
	 */
	public function rewind()
	{
		$this->_current_row = 0;
		return $this;
	}

	/**
	 * Iterator: valid
	 */
	public function valid()
	{
		return $this->offsetExists($this->_current_row);
	}

} // End Database_Result
