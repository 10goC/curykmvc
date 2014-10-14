<?php
namespace Mvc\Db\Statement;

use Mvc\Db\Adapter\MysqliAdapter;

class Mysqli
{
	/**
	 * The database adapter
	 * @var Mvc\Db\Adapter\MysqliAdapter
	 */
	protected $adapter;
	protected $resource;
	protected $keys;
	protected $values;
	protected $meta;
	protected $sql = '';
	protected $isPrepared = false;
	
	public function __construct(MysqliAdapter $adapter, $sql)
	{
		$this->adapter = $adapter;
		$this->sql = $sql;
	}
	
	/**
	 * Prepare
	 *
	 * @param string $sql
	 * @throws \Exception
	 * @return Mvc\Db\Statement\Mysqli
	 */
	public function prepare($sql = null)
	{
		if ($this->isPrepared) {
			throw new \Exception('This statement has already been prepared');
		}
	
		$sql = ($sql) ?: $this->sql;
	
		$this->resource = $this->adapter->getDb()->prepare($sql);
		if (!$this->resource instanceof \mysqli_stmt) {
			throw new \Exception(
				"Statement couldn't be produced with sql: $sql",
				null,
				new \Exception($this->adapter->getDb()->error, $this->adapter->getDb()->errno)
			);
		}
	
		$this->isPrepared = true;
		return $this;
	}
	
	/**
	 * Executes a prepared statement.
	 *
	 * @param array $params OPTIONAL Values to bind to parameter placeholders.
	 * @return bool
	 * @throws Exception
	 */
	public function execute(array $params = null)
	{
		if (!$this->resource) {
			return false;
		}
	
		// if no params were given as an argument to execute(),
		// then default to the bindParam array
		if ($params === null) {
			$params = $this->bindParam;
		}
		// send $params as input parameters to the statement
		if ($params) {
			array_unshift($params, str_repeat('s', count($params)));
			$stmtParams = array();
			foreach ($params as $k => &$value) {
				$stmtParams[$k] = &$value;
			}
			call_user_func_array(
				array($this->resource, 'bind_param'),
				$stmtParams
			);
		}
	
		// execute the statement
		$retval = $this->resource->execute();
		if ($retval === false) {
			throw new \Exception("Mysqli statement execute error : " . $this->resource->error, $this->resource->errno);
		}
	
		// retain metadata
		if ($this->meta === null) {
			$this->meta = $this->resource->result_metadata();
			if ($this->resource->errno) {
				/**
				 * @see Zend_Db_Statement_Mysqli_Exception
				 */
				require_once 'Zend/Db/Statement/Mysqli/Exception.php';
				throw new Zend_Db_Statement_Mysqli_Exception("Mysqli statement metadata error: " . $this->resource->error, $this->resource->errno);
			}
		}
	
		// statements that have no result set do not return metadata
		if ($this->meta !== false) {
	
			// get the column names that will result
			$this->keys = array();
			foreach ($this->meta->fetch_fields() as $col) {
				$this->keys[] = $col->name;
			}
	
			// set up a binding space for result variables
			$this->values = array_fill(0, count($this->keys), null);
	
			// set up references to the result binding space.
			// just passing $this->values in the call_user_func_array()
			// below won't work, you need references.
			$refs = array();
			foreach ($this->values as $i => &$f) {
				$refs[$i] = &$f;
			}
	
			$this->resource->store_result();
			// bind to the result variables
			call_user_func_array(
				array($this->resource, 'bind_result'),
				$this->values
			);
		}
		return $retval;
	}
	
	/**
     * Fetches a row from the result set.
     *
     * @param int $style  OPTIONAL Fetch mode for this fetch operation.
     * @param int $cursor OPTIONAL Absolute, relative, or other.
     * @param int $offset OPTIONAL Number for absolute or relative cursors.
     * @return mixed Array, object, or scalar depending on fetch mode.
     * @throws Zend_Db_Statement_Mysqli_Exception
     */
    public function fetchRow($cursor = null, $offset = null)
    {
        if (!$this->resource) {
            return false;
        }
        // fetch the next result
        $retval = $this->resource->fetch();
        switch ($retval) {
            case null: // end of data
            case false: // error occurred
                $this->resource->reset();
                return false;
            default:
                // fallthrough
        }

        // dereference the result values, otherwise things like fetchAll()
        // return the same values for every entry (because of the reference).
        $values = array();
        foreach ($this->values as $key => $val) {
            $values[] = $val;
        }

        $row = array_combine($this->keys, $values);
        return $row;
    }
	
	/**
     * Closes the cursor and the statement.
     *
     * @return bool
     */
    public function close()
    {
        if ($this->resource) {
            $r = $this->resource->close();
            $this->resource = null;
            return $r;
        }
        return false;
    }
	
}