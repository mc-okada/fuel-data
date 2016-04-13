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

return array(
	//loadする前にtruncateする
	'do_truncate' => false,
	//データ（csv）の保存パス
	'data_path' =>	APPPATH. 'data'. DS,
	//除外するテーブル名
	//'exclude_tables' => array(
	//	'migration',
	//),
	//保存するデータの接続名 - 指定した場合はこの接続のみ
	//'target_connections' => array(
	//	'default',
	//),
	//または、その接続で除外したいテーブルがある場合（上のexeclude_tablesとマージされる）
	//'target_connections' => array(
	//	'default' => array(
	//		'exclude_tables' => array(
	//			'tmp_table',
	//		),
	//	),
	//),
);
