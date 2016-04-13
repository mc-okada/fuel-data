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

namespace Fuel\Tasks;

/**
* データベースのデータのダンプ/ロード用タスク
*
* Usage:
* php oil r db:dump = データベースのデータをcsvにダンプ config/data.php参照
* php oil r db:load = ダンプしたcsvをデータベースにロード
*/
class Data
{
	public function __construct()
	{
		\Config::load('db', true);
		\Config::load('data', true);
	}

	/**
	 * This method gets ran when a valid method name is not used in the command.
	 *
	 * Usage (from command line):
	 *
	 * php oil r db
	 *
	 * @return string
	 */
	public function run($args = NULL)
	{
		echo <<<HELP
Usage:
php oil refine data

Description:
 This task will dump/load database data (no schema, data only).

 Examples:
  php oil r data:dump [--dump-dir=/var/tmp/data] [--connections=default,default2...] [--tables=table1,table2...]
  php oil r data:load [--load-dir=/var/tmp/data] [--do-truncate] [--no-truncate]

HELP;
	}

	/**
	 * Usage (from command line)
	 *
	 * php oil r data:dump [--dump-dir=/var/tmp/data] [--connections=default,default2...] [--tables=table1,table2...]
	 *
	 * @return string
	 */
	public function dump($args = NULL)
	{
		$config = \Config::get('data');

		$data_path = \Cli::option('dump-dir', null);
		if ($data_path) {
			$config['data_path'] = $data_path;
		}

		$con_str  = \Cli::option('connections', null);
		if ($con_str) {
			$config['connections'] = explode(',', $con_str);
		}


		$table_str = \Cli::option('tables', null);
		if ($table_str) {
			$config['tables'] = explode(',', $table_str);
		}

		$data = \Data::forge($config);

		foreach ($data->get_dump_tables() as $table) {
			\Cli::write("dump table [$table]");
			$data->dump_table($table);
		}

		return 'dump complete.';
	}

	/**
	 * Usage (from command line)
	 *
	 * php oil r data:load [--do-truncate] [--no-truncate] [--load-dir=/var/tmp/data]
	 *
	 * @return string
	 */
	public function load($args = NULL)
	{
		$config = \Config::get('data');

		$data_path = \Cli::option('load-dir', null);
		if ($data_path) {
			$config['data_path'] = $data_path;
		}

		$truncate = \Cli::option('do-truncate', null);
		if ($truncate) {
			$config['do_truncate'] = true;
		}

		$no_truncate = \Cli::option('no-truncate', null);
		if ($no_truncate) {
			$config['do_truncate'] = false;
		}

		$data = \Data::forge($config);

		foreach ($data->get_load_tables() as $table) {
			\Cli::write("load table [$table]");
			$data->load_table($table);
		}

		return 'load complete.';
	}
}
/* End of file tasks/data.php */
