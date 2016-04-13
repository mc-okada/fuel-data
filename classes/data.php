<?php
/**
 * Data
 *
 * Database dumper/loader
 *
 * @package    Data
 * @version    0.1
 * @author     okada
 * @license    MIT License
 * @link       http://github.com/mc-okada
 */

namespace Data;

class Data
{
	protected $_data_path   = null;
	protected $_connections = null;
	protected $_tables      = null;
	protected $_exclude_tables = null;
	protected $_do_truncate = null;

	public static function _init()
	{
		\Config::load('db', true);
		\Config::load('data', true);
	}

	public static function forge($arguments = array())
	{
		//save/load path setting
		$data_path = \Config::get('data.data_path', APPPATH. 'data'. DS);
		if (isset($arguments['data_path'])) {
			$data_path = $arguments['data_path'];
		}

		//use connection setting
		if (isset($arguments['connections'])) {
			$connections = $arguments['connections'];
		} else {
			$targets = \Config::get('data.target_connections', false);
			if ($targets) {
				$connections = $targets;
			} else {
				$con = \Config::get('db', false);
				$connections = array();
				foreach ($con as $key => $value) {
					if (isset($value['connection'])) {
						$connections[] = $key;
					}
				}
			}
		}

		//non-use table setting
		if (isset($arguments['exclude_tables'])) {
			$exclude_tables = (array)$arguments['exclude_tables'];
		} else {
			$exclude_tables = \Config::get('data.exclude_tables', array());
		}

		$tables = array();
		if (isset($arguments['tables'])) {
			$tables = (array)$arguments['tables'];
		}

		if (isset($arguments['do_truncate'])) {
			$do_truncate = $arguments['do_truncate'];
		} else {
			$do_truncate = \Config::get('data.do_truncate', false);
		}

		return new static($data_path, $connections, $tables, $exclude_tables, $do_truncate);
	}

	private static function _extract_connection_table_list($connection, $tables)
	{
		$results = array();
		foreach ($tables as $table) {
			$specs = explode('.', $table);
			if (count($specs) >= 2) {
				if ($specs[0] == $connection) {
					$results[] = $specs[1];
				}
			} else {
				$results[] = $table;
			}
		}
		return $results;
	}

	public static function extract_tables_from_db($connections, $exclude_tables, $spec_tables)
	{
		$results = array();
		foreach ($connections as $key => $value) {
			if (is_numeric($key)) {
				$con_str  = $value;
				$excludes = $exclude_tables;
			} else {
				$con_str  = $key;
				$excludes = empty($value['exclude_tables']) ? array() : $value['exclude_tables'];
				$excludes = array_merge($exclude_tables, $excludes);
			}
			$excludes = self::_extract_connection_table_list($con_str, $excludes);

			$target_tables = self::_extract_connection_table_list($con_str, $spec_tables);

			$tables     = \DB::list_tables(null, $con_str);
			$prefix     = \Config::get("db.{$con_str}.table_prefix", '');
			$prefix_len = strlen($prefix);

			foreach ($tables as $table) {
				//if prefix defined, no-prefix-table ignored.
				if ($prefix_len) {
					if ($prefix == substr($table, 0, $prefix_len)) {
						$table = substr($table, $prefix_len);
					} else {
						continue;
					}
				}

				//ignored table
				if (in_array($table, $excludes) || ($target_tables && !in_array($table, $target_tables))) {
					continue;
				}

				$results[] = sprintf('%s.%s', $con_str, $table);
			}
		}
		return $results;
	}

	public static function extract_tables_from_dir($data_dir, $exclude_tables, $spec_tables)
	{
		$results = array();
		foreach (new \GlobIterator($data_dir. DS. '*'. DS. '*.csv') as $m) {
			$base_name = $m->getBaseName();
			$path_name = $m->getPathName();
			$table     = substr($base_name, 0, -1 * strlen('.csv'));
			if (\preg_match("~/(\\w+)/$base_name\$~", $path_name, $matches)) {
				$con_str = $matches[1];

				$excludes = self::_extract_connection_table_list($con_str, $exclude_tables);

				$target_tables = self::_extract_connection_table_list($con_str, $spec_tables);

				if (in_array($table, $excludes) || ($target_tables && !in_array($table, $target_tables))) {
					continue;
				}

				$results[] = sprintf('%s.%s', $con_str, $table);
			}
		}
		return $results;
	}

	public function __construct($data_path, $connections, $tables, $exclude_tables, $do_truncate)
	{
		$this->_data_path   = $data_path;
		$this->_connections = $connections;
		$this->_tables      = $tables;
		$this->_exclude_tables = $exclude_tables;
		$this->_do_truncate = $do_truncate;
	}

	public function get_load_tables()
	{
		return self::extract_tables_from_dir($this->_data_path, $this->_exclude_tables, $this->_tables);
	}

	public function get_dump_tables()
	{
		return self::extract_tables_from_db($this->_connections, $this->_exclude_tables, $this->_tables);
	}

	public function load_table($table_str)
	{
		$root_path   = $this->_data_path;
		$do_truncate = $this->_do_truncate;

		list($con_str, $table) = explode('.', $table_str);
		$file = $root_path. DS. $con_str. DS. $table. '.csv';

		$fp = fopen($file, 'r');
		if (!$fp) {
			throw new \PhpErrorException("Can't open file. [$file]");
		}

		if ($do_truncate) {
			\DBUtil::truncate_table($table, $con_str);
		}

		$columns = fgetcsv($fp);
		while (($data = fgetcsv($fp)) !== FALSE) {
			$values = array();
			for ($i = 0; $i < count($columns); $i++) {
				$values[$columns[$i]] = $data[$i];
			}
			$rs = \DB::insert($table)->set($values)->execute($con_str);
		}
		fclose($fp);
	}

	public function dump_table($table_str)
	{
		$root_path = $this->_data_path;

		list($con_str, $table) = explode('.', $table_str);
		$path = $root_path. '/'. $con_str;
		if (!file_exists($path) && !@mkdir($path)) {
			throw new \PhpErrorException("Can't create directory. [$path]");
		}

		$prefix  = \Config::get("db.{$con_str}.table_prefix", '');
		$columns = \DB::list_columns($prefix.$table, null, $con_str);

		$file = $path. DS. $table. '.csv';
		if (!($fp = @fopen($file, 'w'))) {
			throw new \PhpErrorException("Can't create file. [$file]");
		}

		$cols = array_keys($columns);
		fputcsv($fp, $cols);

		$rows = \DB::select()->from($table)->execute($con_str);
		foreach ($rows as $row) {
			fputcsv($fp, $row);
		}
		fclose($fp);
	}
}

/* End of file data.php */
