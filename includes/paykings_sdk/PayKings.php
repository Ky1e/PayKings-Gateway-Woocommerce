<?php
/**
 * The PayKings Payment PHP SDK. Include this file in your project.
 *
 * @package PayKings Payment
 */
require dirname(__FILE__) . '/lib/shared/PayKings_Request.php';
require dirname(__FILE__) . '/lib/shared/PayKings_Response.php';
require dirname(__FILE__) . '/lib/PayKings.php';

/**
 * Exception class for PayKings Payment PHP SDK.
 *
 * @package PayKings Payment
 */
class PayKings_Exception extends Exception {

}