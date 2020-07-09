<?php
/**
 * Base class for the PayKings Payment Response.
 *
 * @package    	PayKings Payment
 * @subpackage  PayKings_Request
 */


/**
 * Parses an PayKings Payment Response.
 *
 * @package PayKings Payment
 * @subpackage    PayKings_Request
 */
class PayKings_Response {

    const APPROVED = 1;
    const DECLINED = 2;
    const ERROR = 3;

	public $approved;
    public $declined;
    public $error;

    public $response;
	public $responsetext;
	public $authcode;
	public $transactionid;
    public $avsresponse;
    public $cvvresponse;
    public $orderid;
    public $type;
    public $response_code;

}
