<?php
/**
 * MiMo Payment Module for Magento > 1.6
 *
 * @category    MiMo
 * @package     MiMo_MiMoPaymentModule
 * @copyright   Copyright (c) 2012 Mimo Inc. (http://www.mimo.com.ng)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mimo_MimoPaymentModule_Model_Standard extends Mage_Payment_Model_Method_Abstract {
	protected $_code = 'MimoPaymentModule';

	protected $_isInitializeNeeded      = false;
	protected $_canUseInternal          = true;
	protected $_canUseForMultishipping  = true;
	
	protected $_canCapture = true;
	

	protected $_formBlockType = 'MimoPaymentModule/form';

	const LIVE_API_SERVER = "https://www.mimo.com.ng/oauth/v2/";
	const LIVE_USER_API_SERVER = "https://www.mimo.com.ng/partner/";

	const STAGE_API_SERVER = "https://sandbox.mimo.com.ng/oauth/v2/";
	const STAGE_USER_API_SERVER = "https://sandbox.mimo.com.ng/partner/";

	public function __construct()
	{
		// Get User ID
		
		$session = Mage::getSingleton('customer/session');
		$this->user_id = 0;
		if($session->isLoggedIn())
		{
			$customer = $session->getCustomer();
			$this->user_id = $customer->getID();
		
		}
		
		$mimo_mode = Mage::getStoreConfig('payment/MimoPaymentModule/MimoTestMode');
		
		$this->apiServerUrl = self::LIVE_API_SERVER;
		$this->apiServerUrlUser = self::LIVE_USER_API_SERVER;
		
		//$this->redirect_url = self::LIVE_USER_API_SERVER;
		
		if($mimo_mode == 'sandbox')
		{
			$this->apiServerUrl = self::STAGE_API_SERVER;
			$this->apiServerUrlUser = self::STAGE_USER_API_SERVER;
		}
		
		$this->apiKey = Mage::getStoreConfig('payment/MimoPaymentModule/MimoApiKey');
		$this->apiSecret = Mage::getStoreConfig('payment/MimoPaymentModule/MimoApiSecret');
		$this->redirectUri = Mage::getUrl('checkout/multishipping/billing/', array('_secure'=>true));
		
		if($this->apiKey == '' || $this->apiSecret == '')
		{
			if($_REQUEST['payment']['method'] == 'MimoPaymentModule')
			{
				Mage::getSingleton('core/session')->addError('MiMo is not configured properly. Please try configuring MiMo or change the payment Method.');
				header('Location:'.$this->redirectUri); exit;
			}
		}
		else
		{
			if((isset($_GET['code']) && $_GET['code'] != '') && (isset($_GET['response_type']) && $_GET['response_type'] == 'code'))
			{
			
				$token = '';
				$token = $this->requestToken($_GET['code']);
			
				$write = Mage::getSingleton("core/resource")->getConnection("core_write");
			
				// Creating Table if not exists
			
				$table_sql = "CREATE TABLE IF NOT EXISTS `mimo_access_tokens` (
				`nMimoAccessTokenID` int(11) NOT NULL AUTO_INCREMENT,
				`nMageUserID` int(11) DEFAULT NULL COMMENT 'Magento User ID',
				`sMimoAccessToken` varchar(200) DEFAULT NULL COMMENT 'MiMo Access Token',
				`dDateExpire` datetime DEFAULT NULL COMMENT 'Time when access token will expire',
				PRIMARY KEY (`nMimoAccessTokenID`)
				) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Store MiMo Session'";
			
				$table_create=$write->query($table_sql);
			
				$query = "INSERT INTO mimo_access_tokens (nMageUserID, sMimoAccessToken, dDateExpire) values (:user_id, :token, DATE_ADD(NOW(), INTERVAL ".($this->expires_in-300)." SECOND))";
			
				$binds = array(
						'user_id'      => $this->user_id,
						'token'     => $token
				);
			
				$write->query($query, $binds);
			}
			
		}
		
		
		
		
		parent::__construct();
	}

	
	/**
	 * Get oauth authenitcation URL
	 *
	 * @return string URL
	 */
	public function getAuthUrl()
	{
		
		$params = array(
				'client_id' => $this->apiKey,
				'url' => $this->redirectUri,
				'response_type' => 'code'
		);
		// Only append a redirectURI if one was explicitly specified
		if ($this->redirectUri) {
			$params['redirect_uri'] = $this->redirectUri;
		}
		$url =  $this->apiServerUrl.'/authenticate?' . http_build_query($params);
		return $url;
	}
	
	
	
	/**
	 * Request oauth token from Mimo
	 *
	 * @param string $code User authorization code returned from Mimo
	 * @return string oauth token
	 */
	public function requestToken($code)
	{
		if (!$code) {
			Mage::throwException($this->__('MiMo Code is not available'));
		}
		else
		{
			$params = array(
					'client_id' => $this->apiKey,
					'client_secret' => $this->apiSecret,
					'url' => $this->redirectUri,
					'grant_type' => 'authorization_code',
					'code' => $code
			);
			$url =  $this->apiServerUrl.'token?' . http_build_query($params);
			$response = $this->curl($url, 'POST');
			
			if (isset($response['error'])) {
				Mage::throwException($this->__($response['error_description']));
				return false;
			}
			
			$this->expires_in = $response['expires_in'];
			return $response['access_token'];
		}
		
	}
	
	
	
	/**
	 * Grab information for the given transaction ID
	 *
	 * @param float amount to which information is pulled
	 * @return array Transaction information
	 */
	public function transaction($amount = false,$notes='')
	{
		// Verify required paramteres
		if (!$amount) {
			return $this->setError('Please enter a transaction ID.');
		}
		$params = array('notes'=>$notes,
				'amount' => $amount
	
		);
		$url = $this->apiServerUrlUser.'transfers';
		$data['notes'] = $params['notes'];
		$data['amount'] = $params['amount'];
		
		$response = $this->post($url, $data,true);
		
		if (isset($response['error'])) {
			$this->errorMessage = $response['error_description'];
			return false;
		}
		return $response;
	}
	
	
	/**
	 * Executes POST request against API
	 *
	 * @param string $request
	 * @param array $params
	 * @param bool $includeToken Include oauth token in request?
	 * @return array|null
	 */
	protected function post($request, $params = false, $includeToken = true)
	{
		$params['access_token'] = $this->accessToken;
		$delimiter = (strpos($request, '?') === false) ? '?' : '&';
		$url =  $request . $delimiter . http_build_query($params); 
		
		
		//header('Location:'.$url); exit;
		$rawData = $this->curl($url, 'POST',array());
		return $rawData;
	}
	
	/**
	 * Executes GET requests against API
	 *
	 * @param string $request
	 * @param array $params
	 * @return array|null Array of results or null if json_decode fails in curl()
	 */
	protected function get($request, $params = array())
	{
		$params['access_token'] = $this->accessToken;
		$delimiter = (strpos($request, '?') === false) ? '?' : '&';
		$url =  $request . $delimiter . http_build_query($params);
		$rawData = $this->curl($url, 'GET');
		return $rawData;
	}
	
	
	
	/**
	 * Execute curl request
	 *
	 * @param string $url URL to send requests
	 * @param string $method HTTP method
	 * @param array $params request params
	 * @return array|null Returns array of results or null if json_decode fails
	 */
	protected function curl($url, $method = 'GET', $params = array())
	{
		// Encode POST data
		$data = json_encode($params);
		// Set request headers
		$headers = array('Accept: application/json', 'Content-type: application/json;charset=UTF-8');
		if ($method == 'POST') {
			$headers[] = 'Content-Length: ' . strlen($data);
		}
	
		// Set up our CURL request
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_USERPWD, "mimo:mimo");
		// Windows require this certificate
		$ca = dirname(__FILE__); 
		curl_setopt($ch, CURLOPT_CAINFO, $ca); // Set the location of the CA-bundle
		curl_setopt($ch, CURLOPT_CAINFO, $ca . '/cacert.pem'); // Set the location of the CA-bundle
		// Initiate request
		$rawData = curl_exec($ch);
		//print_r($rawData); 
		// If HTTP response wasn't 200,
		// log it as an error!
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
		if ($code !== 200 && $code!==201 ) {
			return array(
					'Success' => false,
					'Message' => "Request failed. Server responded with: {$code}"
					);
		}
	
		// All done with CURL
		curl_close($ch);
	
		// Otherwise, assume we got some
		// sort of a response
		return json_decode($rawData, true);
	}
	
	public function capture(Varien_Object $payment, $amount)
	{

		$write = Mage::getSingleton("core/resource")->getConnection("core_write");
		
		// Creating Table if not exists
		
		$table_sql = "CREATE TABLE IF NOT EXISTS `mimo_access_tokens` (
		`nMimoAccessTokenID` int(11) NOT NULL AUTO_INCREMENT,
		`nMageUserID` int(11) DEFAULT NULL COMMENT 'Magento User ID',
		`sMimoAccessToken` varchar(200) DEFAULT NULL COMMENT 'MiMo Access Token',
		`dDateExpire` datetime DEFAULT NULL COMMENT 'Time when access token will expire',
		PRIMARY KEY (`nMimoAccessTokenID`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Store MiMo Session'";
		
		$table_create=$write->query($table_sql);
		
		
		$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
		
		$sql        = "SELECT sMimoAccessToken from mimo_access_tokens WHERE nMageUserID='".$this->user_id."' AND dDateExpire>NOW() ORDER BY nMimoAccessTokenID DESC LIMIT 1";  
		$mimo_access_token = '';
		$mimo_access_token = $connection->fetchOne($sql); //fetchRow($sql), fetchOne($sql),...
		
		if($mimo_access_token != '')
		{
			$this->accessToken  =$mimo_access_token; 
			
			$data = $this->transaction($amount, 'MiMo Magento');
			
			Mage::getSingleton('core/session')->setMiMoTransactionID($data['transaction_id']);
			
			
			return $this;
			
			// Do payment
		}
		else
		{
			// Redirect to MiMo
			header('Location:'.$this->getAuthUrl()); exit;
			$this->_redirectUrl();
			die;
		}
		
		
	
		return $this;
	}

	public function getOrderPlaceRedirectUrl() {
		return Mage::getUrl('MimoPaymentModule/payment/redirect', array('_secure' => true));
	}
}
?>