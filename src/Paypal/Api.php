<?php
namespace Paypal;
use Paypal\HttpHelper;

class Api {
	
	private $_scene = [
		"sandbox" => "https://api.sandbox.paypal.com",
		"production" => "https://api.paypal.com"
	];
	private $_http = null;
	private $_apiUrl = null;
	private $_token = null;
	private $_client_id = null;
	private $_client_secret = null;

	/**
	 * 	Class constructor.
	 *	
	 */
	public function __construct($scene, $client_id, $client_secret)
	{
		$this->_http = new HttpHelper;
		$this->_apiUrl = $this->_scene[$scene];
		$this->_client_id = $client_id;
		$this->_client_secret = $client_secret;
	}

	/**
	 * 	Set PayPal default header for the curl instance.
	 *
	 * 	@return void
	 */
	private function _setDefaultHeaders()
	{
		$this->_http->addHeader("PayPal-Partner-Attribution-Id: PP-DemoPortal-EC-Psdk-ORDv2-php");
	}

	/**
	 * 	Actual call to curl helper to create an order using PayPal REST APIs.
	 *	
	 * 	Reset curl helper.
	 *	Set default PayPal headers.
	 * 	Set API call specific headers.
	 *	Set curl url.
	 *	Set curl body.
	 *
     *	@param array $postData Url to be called using curl
	 * 	@return array PayPal REST create response
	 */
	private function _createOrder($postData)
	{
		$this->_http->resetHelper();
		$this->_setDefaultHeaders();
		$this->_http->addHeader("Content-Type: application/json");
		$this->_http->addHeader("Authorization: Bearer " . $this->_token);
		$this->_http->setUrl($this->_createApiUrl("checkout/orders"));
		$this->_http->setBody($postData);
		return $this->_http->sendRequest(); 
	}

	/**
	 * 	Create the PayPal REST endpoint url.
	 *
	 *	Use the configurations and combine resources to create the endpoint.
	 *
     *	@param string $resource Url to be called using curl
	 * 	@return string REST API url depending on environment.
	 */
	private function _createApiUrl($resource)
	{
		return $this->_apiUrl ."/v". ($resource == 'oauth2/token'?"1":"2") ."/". $resource;
	}

	/**
	 * 	Request for PayPal REST oath bearer token.
	 *	
	 * 	Reset curl helper. 
	 *	Set default PayPal headers.
	 *	Set curl url.
	 *	Set curl credentials.
	 *	Set curl body.
	 *	Set class token attribute with bearer token.
	 *
	 * 	@return void
	 */
	private function _getToken()
	{
		$this->_http->resetHelper();
		$this->_setDefaultHeaders();
		$this->_http->setUrl($this->_createApiUrl("oauth2/token"));
		$this->_http->setAuthentication($this->_client_id . ":" . $this->_client_secret);
		$this->_http->setBody("grant_type=client_credentials");
		$returnData = $this->_http->sendRequest();
		$this->_token = $returnData['access_token'];
	}

	/**
	 * 	Call private order create class function to forward curl request to helper.
	 *	
	 * 	Check for bearer token.
	 *	Call internal REST create order function.
	 *
     *	@param array $postData Url to be called using curl
	 * 	@return array Formatted API response
	 */
	public function orderCreate($postData)
	{
		if($this->_token === null) {
			$this->_getToken();
		}
		$returnData = $this->_createOrder($postData);
		//$_SESSION['order_id'] = $returnData['id'];
		return [
			"ack" => true,
			"data" => [
				"id" => $returnData['id']
			]
		];
	}

	public function test()
	{
		return $this->_apiUrl;
	}
	
}