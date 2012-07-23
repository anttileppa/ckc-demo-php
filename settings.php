<?php
/**
 * ckc-connector-php configuration file. 
 */

global $ckc_settings;

/**
 * Connector used as a backend for ckc-plugin. Connector should be located at ckc/connectors/NAME -folder and contain connector.php file.
 */
$ckc_settings->connector = 'demo';

/**
 * Database user name
 */
$ckc_settings->username = 'username';

/**
 * Database password
 */
$ckc_settings->password = 'password';

/**
 * Database hostname
 */
$ckc_settings->hostname = 'localhost';

/**
 * Database database name
 */
$ckc_settings->database = 'database';

?>