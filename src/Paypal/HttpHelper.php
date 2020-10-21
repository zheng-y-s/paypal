<?php 
namespace Paypal;
/**
	*	Http helper class for curl requests.
	*	
	* 	A class that provides a curl instance with default setup.
*/

class HttpHelper {
	
	public $_curl = null;
	public $_headers = array();
	
	/**
		* 	Class constructor.
		*	
	*/
	public function __construct() {
		$this->_initCurl();
	}
	
	/**
		* 	Class destructor.
		*
	*/
	public function __destruct() {
		curl_close($this->_curl);
	}
	
	/**
		* 	Initialize curl.
		*
		*	Check if curl is enabled in the PHP environment.
		*	Trigger error if curl is not available.
		*	Initialize curl and set defaults.
		*
		* 	@return void
	*/
	private function _initCurl() {
		if(!function_exists('curl_version')) {
			trigger_error("Curl not available", E_USER_ERROR);
		}
		else {
			$this->_curl = curl_init();
			$this->_setDefaults();
		}
	}
	
	/**
		* 	Set curl defaults.
		*	
		* 	@return void
	*/
	private function _setDefaults() {  
		curl_setopt($this->_curl, CURLOPT_VERBOSE, 1);
		curl_setopt($this->_curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($this->_curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($this->_curl, CURLOPT_SSLVERSION , 'CURL_SSLVERSION_TLSv1_2');
		curl_setopt($this->_curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->_curl, CURLOPT_MAXREDIRS, 10);
		curl_setopt($this->_curl, CURLOPT_TIMEOUT, 30);
		curl_setopt($this->_curl, CURLOPT_HEADER, 1);
		curl_setopt($this->_curl, CURLINFO_HEADER_OUT, 1);
	}
	
	/**
		* 	Set curl headers using the class header array property.
		*
		* 	@return void
	*/
	private function _setHeaders() {
		curl_setopt($this->_curl, CURLOPT_HTTPHEADER, $this->_headers); 
	}
	
	/**
		* 	Execute curl request.
		*
		*	Set headers for curl instance.
		*	Run curl instance.
		*	Return result or trigger warning.
		*
		* 	@return array Result of the curl request
	*/
	private function _sendRequest() {
		$this->_setHeaders();
		$result = curl_exec($this->_curl);
		if(curl_errno($this->_curl)){
			trigger_error("Request Error:" . curl_error($this->_curl), E_USER_WARNING);
		}
		$headerSize = curl_getinfo($this->_curl, CURLINFO_HEADER_SIZE);
		$body = substr($result, $headerSize);
		return json_decode($body, true);
	}
	
	/**
		* 	Reset the helper.
		*	
		*	Re-initialize the curl instance.
		*	Reset class header array property.
		*
		* 	@return void
	*/
	public function resetHelper() {
		$this->_curl = null;
		$this->_initCurl();
		$this->_headers = array();
	}
	
	/**
		* 	Set curl url.
		*	
		*	@param string $url Url to be called using curl
		* 	@return void
	*/
	public function setUrl($url) {
		curl_setopt($this->_curl, CURLOPT_URL, $url); 
	}
	
	/**
		* 	Set body of the curl request.
		*	
		*	URL encode the request body.
		*	Check if data is array and url encode.
		*	Set the curl body.
		*	Set curl post options
		*	
		*	@param array|string $postData Curl request body
		* 	@return void
	*/
	public function setBody($postData) {
		if(is_array($postData)) {
			$postData = json_encode($postData);
		}
		curl_setopt($this->_curl, CURLOPT_POSTFIELDS, $postData);
		curl_setopt($this->_curl, CURLOPT_POST, true);
	}

	/**
		* 	Set body of the curl request.
		*	
		*	URL encode the request body.
		*	Check if data is array and url encode.
		*	Set the curl body.
		*	Set curl method to PATCH
		*	
		*	@param array|string $data Curl request body
		* 	@return void
	*/
	public function setPatchBody($data){
		if(is_array($data)) {
			$data = json_encode($data);
		}
		curl_setopt($this->_curl, CURLOPT_POSTFIELDS, $data);
		curl_setopt($this->_curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
	}
	
	/**
		* 	Set authentication attributes of the curl request.
		*	
		*	@param string $authData Authentication credentials
		* 	@return void
	*/
	public function setAuthentication($authData) {
		curl_setopt($this->_curl, CURLOPT_USERPWD, $authData);
	}
	
	/**
		* 	Push header values into class header array property.
		*	
		*	@param string $header Header key-value as string
		* 	@return void
	*/
	public function addHeader($header) {
		$this->_headers[] = $header;
	}	
	
	/**
		* 	Public function to start execution of curl request.
		*	
		*	Check if post type request and setup curl options.
		*	If post request, set curl body with required data.
		*	Call internal function to execute curl instance.
		*
		* 	@return array Curl response
	*/
	public function sendRequest() {
		return $this->_sendRequest();
	}
	
}