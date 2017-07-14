<?php

namespace PaymentPlugins;

/**
 * Class that makes all requests to Payment Plugins RESTful web service.
 *
 * @author Payment Plugins
 * @copyright 2015, Payment Plugins
 *           
 */
class PaymentPlugins {
	private $config;
	private $disableSSL;
	private static $services = array (
			'development' => 'https://development.paymentplugins.com:443/paymentplugins/v1/merchants/',
			'sandbox' => 'https://api.sandbox.paymentplugins.com:443/',
			'production' => 'https://api.paymentplugins.com:443/' 
	);
	private static $endpoints = array (
			'GetToken' => 'client_token',
			'Account' => 'account' 
	);
	private static $exceptions = array (
			'nc' => 'There is not a valid configuration set.',
			'400' => 'There was an error processing the request.',
			'404' => 'The requested service was not found.',
			'500' => 'Internal server error.',
			'502' => 'Bad Gateway.',
			'503' => 'There was an error on our end.' 
	);
	private static $httpCodes = array (
			'success' => array (
					200,
					201,
					202,
					204 
			),
			'warning' => array (
					301,
					302,
					303,
					304 
			),
			'info' => array (
					305,
					307 
			),
			'error' => array (
					40,
					401,
					403,
					404,
					405,
					406,
					407,
					408,
					409,
					410,
					411,
					412,
					413,
					414,
					415,
					416,
					417 
			),
			'failure' => array (
					500,
					501,
					502,
					503,
					504,
					505 
			) 
	);
	public function __construct() {
	}
	public function __get($key) {
	}
	public static function newInstance() {
		if (Configuration::$config == null) {
			self::throwException ( 'nc' );
		}
		$_instance = new self ();
		$_instance->config = Configuration::$config;
		return $_instance;
	}
	public static function throwException($code, $message = null) {
		if ($message == null) {
			$message = self::$exceptions [$code];
		}
		throw new PaymentPluginsException ( $code, $message );
	}
	private function getCertPath($path = false) {
		$cert_path = $path ? $path : DIRECTORY_SEPARATOR . 'ssl' . DIRECTORY_SEPARATOR;
		
		$ca_path = realpath ( dirname ( __FILE__ ) . $cert_path . 'api_paymentplugins_com.crt' );
		
		if (! file_exists ( $ca_path )) {
			self::throwError ( 'ssl_nf' );
		}
		return $ca_path;
	}
	public function getClientToken($params = null) {
		$clientToken = $this->_sendRequest ( $params, self::$endpoints ['GetToken'], 'POST' );
		return isset ( $clientToken ['clientToken'] ) ? $clientToken ['clientToken'] : '';
	}
	
	/**
	 * Fetch the account data for the user.
	 *
	 * @param array $params        	
	 */
	public function getAccount() {
		$response = $this->_sendRequest ( null, self::$endpoints ['Account'], 'GET' );
		return $response;
	}
	private function getAuthorizationHeader() {
		return base64_encode ( $this->config->getPublicKey () . ':' . $this->config->getPrivateKey () );
	}
	private function encodeJson($data) {
		return json_encode ( $data );
	}
	private function decodeJson($data) {
		return json_decode ( $data, true );
	}
	public function disableSSL($disable) {
		$this->disableSSL = $disable;
	}
	private function _sendRequest($params = null, $endpoint, $method) {
		$url = self::$services [$this->config->getEnvironment ()] . $endpoint;
		$headers = array (
				'Content-type: application/json',
				'Accept: application/json',
				'Authorization: Basic ' . $this->getAuthorizationHeader () 
		);
		$curl = curl_init ();
		curl_setopt ( $curl, CURLOPT_URL, $url );
		curl_setopt ( $curl, CURLOPT_CUSTOMREQUEST, $method );
		curl_setopt ( $curl, CURLOPT_HTTPHEADER, $headers );
		curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, true );
		if ($params != null) {
			$json = $this->encodeJson ( $params );
			curl_setopt ( $curl, CURLOPT_POSTFIELDS, $json );
		}
		if ($this->config->getEnvironment () === 'sandbox' || $this->disableSSL == true) {
			curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt ( $curl, CURLOPT_SSL_VERIFYHOST, 0 );
		} else {
			curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, true );
			curl_setopt ( $curl, CURLOPT_SSL_VERIFYHOST, 2 );
			curl_setopt ( $curl, CURLOPT_CAINFO, $this->getCertPath () );
		}
		$response = curl_exec ( $curl );
		$httpCode = curl_getinfo ( $curl, CURLINFO_HTTP_CODE );
		curl_close ( $curl );
		
		return $this->_processResponse ( $httpCode, $this->decodeJson ( $response ) );
	}
	private function _processResponse($httpCode, $body) {
		if (! in_array ( $httpCode, self::$httpCodes ['success'] )) {
			self::throwException ( $httpCode, $body ['message'] ['text'] );
		} else
			return $body;
	}
}
?>