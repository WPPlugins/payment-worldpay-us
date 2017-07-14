<?php
namespace Worldpay;
/**
 * Main class from which all requests are made to Worldpay.
 * @author Payment Plugins - https://paymentplugins.com
 * @copyright 2015 Payment Plugins
 */
final class WorldpayUS {
	
	private static $services = array(
			'production'=>'https://gwapi.securenet.com/api/', 
			'sandbox'=>'https://gwapi.demo.securenet.com/api/'
	);
	
	private static $errors = array(
			'na'=>'Not an array',
			'token'=>'Invalid token.',
			'ip'=>'Invalid Parameter.',
			'ne'=>'No environment defined.',
			'mc'=>'Missing configuration argument(s).',
			'ssl'=>'SSL cannot be disabled for production mode.',
			'ssl_cacert'=>'The Worldpay SSL certificate cannot be validated.',
			'request_timeout'=>'The request to Worldpay timed out.',
			'request_error'=>'There was an error while connecting to Worldpay.',
			'ssl_nf'=>'The SSL certificate could not be found.'
	);
	
	private static $endpoints = array(
			'AuthandCap'=>'Payments/Charge',
			'AuthOnly'=>'Payments/Authorize',
			'Refund'=>'Payments/Refund',
			'Verify'=>'Payments/Verify',
			'Void'=>'Payments/Void',
			'CreateCustomer'=>'Customers',
			'CreatePaymentMethod'=>'Customers/%s/PaymentMethod',
			'DeletePaymentMethod'=>'Customers/%s/PaymentMethod/%s',
			'Customer'=>'Customers/%s',
			'RecurringBilling'=>'Customers/%s/PaymentSchedules/Recurring',
			'VariableBilling'=>'Customers/%s/PaymentSchedules/Variable',
			'InstallmentPlan'=>'Customers/%s/PaymentSchedules/Installment',
			'UpdateRecurringPlan'=>'Customers/%s/PaymentSchedules/recurring/%s',
			'UpdateInstallment'=>'Customers/%s/PaymentSchedules/installment/%s',
			'UpdateVariablePlan'=>'Customers/%s/PaymentSchedules/variable/%s',
			'DeletePaymentPlan'=>'Customers/%s/PaymentSchedules/%s',
			'GetPaymentPlan'=>'Customers/%s/PaymentSchedules/%s',
			'GetPaymentAccount'=>'Customers/%s/PaymentMethod/%s'
	);
	
	private static $developerApplication = array(
			'developerId'=>10000605,
			'version'=>'1.0'
	);
	
	public $timeout = 60;
	
	private $secureId;
	
	private $secureKey;
	
	private $disableSSL = false;
	
	private $environment;
	
	private static $_instance;
	/**
	 * The secure key represents the merchant secure key used for authentication when making RESTful calls to
	 * Worldpay.
	 * @param string $secureKey
	 */
	public function __construct($secureId = null, $secureKey = null, $timeout = false){
		
	}
	
	public static function configuration($environment = 'sandbox', $secureId = null, $secureKey = null, $timeout = false){
		if($secureKey == null || $secureId == null){
			self::throwError('mc');
		}
		if($timeout){
			$this->setTimeout($timeout);
		}
		$worldpay = new self();
		self::$_instance = $worldpay;
		$worldpay->environment = $environment;
		$worldpay->secureId = $secureId;
		$worldpay->secureKey = $secureKey;
		return $worldpay;
	}
	
	/**
	 * @return WorldpayUS
	 */
	public static function instance(){
		return self::$_instance;
	}
	/**
	 * Disable SSL. Localhost sometimes needs SSL disabled. 
	 * @param bool $bool
	 */
	public function disableSSL($bool = false){
		$this->disableSSL = $bool;
	}
	
	/**
	 * Throws an error when a configuration issue is detected.
	 * @param string $message
	 */
	public static function throwError($code, $message = null){
		if($message == null){
			$message = self::$errors[$code];
		}
		throw new WorldpayUSException($code, $message);
	}
	
	public function setTimeout($timeout = 65){
		$this->timeout = $timeout;
	}
	/**
	 * Accepts an array of parameters which are used to run a transaction on a payment method.
	 * @param array $params
	 */
	public function transaction($params = array()){
		$params = $this->add_developerApplication($params);
		ParameterValidations::checkAuthorizationCaptureParams($params);
		
		$response = $this->sendRequest(self::buildJSON($params), self::$endpoints['AuthandCap'], 'POST');
		return self::handleResponse($response, Constants::TRANSACTION);
	}
	
	/**
	 * Acceptns an array of parameters used to refund the order. 
	 * @param array $params
	 */
	public function refund($params = array()){
		$params = $this->add_developerApplication($params);
		ParameterValidations::checkRefundParams($params);
		$response = $this->sendRequest(self::buildJSON($params), self::$endpoints['Refund'], 'POST');
		
		return self::handleResponse($response, Constants::REFUND);
	}
	
	public function recurringPaymentPlan($params){
		$params = $this->add_developerApplication($params);
		ParameterValidations::checkRecurringBillingParams($params);
		$endpoint = sprintf(self::$endpoints['RecurringBilling'], $params['customerId']);
		$response = $this->sendRequest($this->buildJSON($params), $endpoint, 'POST');
		
		return self::handleResponse($response, Constants::PAYMENTPLAN);
	}
	
	public function installmentPlan($params){
		$params = $this->add_developerApplication($params);
		ParameterValidations::checkInstallmentPlan($params);
		$endpoint = sprintf(self::$endpoints['InstallmentPlan'], $params['customerId']);
		$response = $this->sendRequest($this->buildJSON($params), $endpoint, 'POST');
		
		return self::handleResponse($response, Constants::PAYMENTPLAN);
	}
	
	/**
	 * Creates a subscription within the Worldpay system based on the given parameters.
	 * @param array $params
	 */
	public function variablePaymentPlan($params){
		$params = $this->add_developerApplication($params);
		ParameterValidations::checkRecurringBillingParams($params);
		$endpoint = sprintf(self::$endpoints['VariableBilling'], $params['customerId']);
		$response = $this->sendRequest($this->buildJSON($params), $endpoint, 'POST');
		
		return self::handleResponse($response, Constants::PAYMENTPLAN);
	}
	
	public function createCustomer($params){
		$params = $this->add_developerApplication($params);
		ParameterValidations::checkCustomerCreateParams($params);
		$endpoint = sprintf(self::$endpoints['CreateCustomer']);
		$response = $this->sendRequest(self::buildJSON($params), $endpoint, 'POST');
		
		return self::handleResponse($response, Constants::CREATE_CUSTOMER);
	}
	
	public function createPaymentMethod($params){
		$params = $this->add_developerApplication($params);
		ParameterValidations::checkPaymentMethodCreateParams($params);
		$endpoint = sprintf(self::$endpoints['CreatePaymentMethod'], $params['customerId']);
		$response = $this->sendRequest(self::buildJSON($params), $endpoint, 'POST');
		
		return self::handleResponse($response, Constants::CREATE_PAYMENTMETHOD);
	}
	
	public function deletePaymentMethod($params){
		$params = $this->add_developerApplication($params);
		ParameterValidations::checkPaymentMethodDelete($params);
		$endpoint = sprintf(self::$endpoints['DeletePaymentMethod'], $params['customerId'], $params['paymentMethodId']);
	
		$response = $this->sendRequest($this->buildJSON($params), $endpoint, 'DELETE');
		return self::handleResponse($response, Constants::DELETE_PMTMTHD);
	}
	
	/**
	 * Fetches the customer based on the provided parameters.
	 * @param array $params
	 */
	public function getCustomer($customerId){
		$params['customerId'] = $customerId;
		$params = $this->add_developerApplication($params);
		ParameterValidations::checkCustomerSearch($params);
		$endpoint = sprintf(self::$endpoints['Customer'], $customerId);
		$response = $this->sendRequest(self::buildJSON($customerId), $endpoint, 'GET');
		
		return self::handleResponse($response, Constants::GET_CUSTOMER);
	}
	
	public function deleteCustomer($customerId){
		$params['customerId'] = $customerId;
		$params = $this->add_developerApplication($params);
		ParameterValidations::checkCustomerSearch($customerId);
		$endpoint = sprintf(self::$endpoints['Customer'], $customerId);
		$response = $this->sendRequest($this->buildJSON($params), $endpoint, 'DELETE');
		
		return self::handleResponse($response, Constants::DELETE_CUSTOMER);
	}
	
	public function updateCustomer($params){
		$params = $this->add_developerApplication($params);
		//ParameterValidations::checkCustomerSearch($customerId);
		$endpoint = sprintf(self::$endpoints['Customer'], $params['customerId']);
		$response = $this->sendRequest(self::buildJSON($params), $endpoint, 'PUT');
		
		return self::handleResponse($response, Constants::UPDATE_CUSTOMER);
	}
	
	
	public function getPaymentMethod($params){
		$params = $this->add_developerApplication($params);
		$endpoint = sprintf(self::$endpoints['GetPaymentAccount'], $params['customerId'], $params['paymentMethodId']);
		$response = $this->sendRequest(self::buildJSON($params), $endpoint, 'GET');
		
		return self::handleResponse($response, Constants::GET_PAYMENTACCOUNT);
	}
	
	public function deletePaymentPlan($params){
		$params = $this->add_developerApplication($params);
		ParameterValidations::checkPaymentPlanDeleteParams($params);
		$endpoint = sprintf(self::$endpoints['DeletePaymentPlan'], $params['customerId'], $params['planId']);
		$response = $this->sendRequest($this->buildJSON($params), $endpoint, 'DELETE');
		
		return self::handleResponse($response, Constants::DELETE_PLAN);
	}
	
	public function getPaymentPlan($params){
		$params = $this->add_developerApplication($params);
		ParameterValidations::checkGetPaymentPlanParams($params);
		$endpoint = sprintf(self::$endpoints['GetPaymentPlan'], $params['customerId'], $params['planId']);
		$response = $this->sendRequest($this->buildJSON($params), $endpoint, 'GET');
		
		return self::handleResponse($response, Constants::PAYMENTPLAN);
	}
	public function updateRecurringPlan($params){
		$params = $this->add_developerApplication($params);
		ParameterValidations::checkPaymentPlanUpdateParams($params);
		$endpoint = sprintf(self::$endpoints['UpdateRecurringPlan'], $params['customerId'], $params['planId']);
		$response = $this->sendRequest($this->buildJSON($params), $endpoint, 'PUT');
		
		return self::handleResponse($response, Constants::PAYMENTPLAN);
	}
	
	/**
	 * 
	 * @param array $params 
	 * @return Response
	 */
	public function updateInstallment($params){
		$params = $this->add_developerApplication($params);
		ParameterValidations::checkInstallmentPlanUpdate($params);
		$endpoint = sprintf(self::$endpoints['UpdateInstallment'], $params['customerId'], $params['planId']);
		$response = $this->sendRequest($this->buildJSON($params), $endpoint, 'PUT');
		
		return self::handleResponse($response, Constants::PAYMENTPLAN);
	}
	
	/**
	 * Updates the designated variable plan.
	 * @param array $params
	 * @returnResponse
	 */
	public function updateVariablePlan($params){
		$params = $this->add_developerApplication($params);
		ParameterValidations::checkPaymentPlanUpdateParams($params);
		$endpoint = sprintf(self::$endpoints['UpdateVariablePlan'], $params['customerId'], $params['planId']);
		$response = $this->sendRequest($this->buildJSON($params), $endpoint, 'PUT');
		
		return self::handleResponse($response, Constants::PAYMENTPLAN);
	}
	
	/**
	 * Accepts an array of parameters to be encoided into JSON format. 
	 * @param array $params
	 */
	private static function buildJSON($params = array()){
		return json_encode($params);
	}
	
	private function add_developerApplication(array $params){
		$params['developerApplication'] = self::$developerApplication;
		return $params;
	}
	/**
	 * Sends the request using PHP CURL library, along with all parameters in JSON format to Worldpay via RESTful webservice.
	 * @param string $action
	 * @param string $json
	 */
	private function sendRequest($json = false, $action, $method = false){
		if(PHP_VERSION < 5.3){
			self::throwError(sprintf('PHP Version must be 5.3 or greater. Current PHP Version = %s', PHP_VERSION));
		}
		if(empty($this->environment) || $this->environment === ''){
			self::throwError('ne');
		}
		$url = self::$services[$this->environment];
		$credentials = sprintf('%s:%s', $this->secureId, $this->secureKey);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url.$action);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($ch, CURLOPT_CAINFO, $this->getCertPath());
		if($this->disableSSL){
			if($this->environment === 'production'){
				self::throwError('ssl');
			}
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		}
		else {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		}
		$headers = array(
				'Content-type: application/json',
				'Content-length: '. strlen($json),
				'Authorization: Basic '.base64_encode($credentials)
		);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$response = curl_exec($ch);
		$errno = curl_errno($ch);
		curl_close($ch);
		if($errno){
			if($errno == 60){
				self::throwError('ssl_cacert');
			}
			elseif($errno == 28){
				self::throwError('request_timeout');
			}
			else self::throwError('request_error');
		}
		return json_decode($response, true);
	}
	
	private static function handleResponse($response, $type){
		$response['requestType'] = $type;
		return Response::factory($response);
	}
	
	private function getCertPath($path = false){
		$cert_path = $path ? $path : DIRECTORY_SEPARATOR . 'ssl'. DIRECTORY_SEPARATOR;
		if($this->environment === 'sandbox'){
			$ca_path = realpath(dirname(__FILE__).$cert_path.'gwapi_demo_securenet_com.pem');
		}
		else {
			$ca_path = realpath(dirname(__FILE__).$cert_path.'gwapi_securenet_com.pem');
		}
		if(!file_exists($ca_path)){
			self::throwError('ssl_nf');
		}
		return $ca_path;
	}
}
?>