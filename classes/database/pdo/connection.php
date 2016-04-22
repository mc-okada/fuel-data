<?php
/**
 * PDO database connection extension.
 * for FuelPHP v1.8
 *
 * @note 1.8になってから、list_tablesの実装が無くなったので、一時的な対応
 */

class Database_Pdo_Connection extends \Fuel\Core\Database_PDO_Connection
{
	/**
	 * Lit tables
	 *
	 * @param string $like
	 *
	 * @throws \FuelException
	 */
	public function list_tables($like = null)
	{
		$driver_name = strtolower($this->driver_name());
		$func_name   = 'list_tables_'.$driver_name;

		if (method_exists($this, $func_name))
		{
			return $this->{$func_name}($like);
		}

		throw new \FuelException('Database method '.__METHOD__.' is not supported by '.__CLASS__);
	}

	/**
	* List tables for PDO_MYSQL
	*
	* @param string $like
	* @return array
	*/
	protected function list_tables_mysql($like = null)
	{
		$query = 'SHOW TABLES';

		if (is_string($like))
		{
			$query .= ' LIKE ' . $this->quote($like);
		}

		$q = $this->_connection->prepare($query);
		$q->execute();
		$result = $q->fetchAll();

		$tables = array();
		foreach ($result as $row)
		{
			$tables[] = reset($row);
		}

		return $tables;
	}
}

