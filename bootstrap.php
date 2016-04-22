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

Autoloader::add_core_namespace('Data');

Autoloader::add_classes(array(
	'Data\\Data' => __DIR__ . '/classes/data.php',
	'Database_Pdo_Connection' => __DIR__ . '/classes/database/pdo/connection.php',
));
