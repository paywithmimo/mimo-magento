<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Mimo
 * @package    Mimo (mimo.com)
 * @copyright  Copyright (c) 2011 Dwolla (https://www.mimo.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mimo_Mimo_Block_Form extends Mage_Payment_Block_Form
{ 
	
    /**
     * @var string error messages returned from Mimo
     */
    private $errorMessage = false;
    
   const LIVE_API_SERVER = "https://www.mimo.com.ng/oauth/v2";
    const LIVE_USER_API_SERVER = "https://www.mimo.com.ng/partner/";
    
    const STAGE_API_SERVER = "https://sandbox.mimo.com.ng/oauth/v2";
    const STAGE_USER_API_SERVER = "https://sandbox.mimo.com.ng/partner/";
    /**
     * Sets the initial state of the client
     *
     * @param string $apiKey
     * @param string $apiSecret
     * @param string $redirectUri
     * @param string $mode
     * @throws InvalidArgumentException
     */
	
	 /**
     * Constructor. Set template.
     */
    protected function _construct()
    {
    	if(Mage::getModel('mimo/paymentmethod')->getConfigData('mimo_server') == 'Live')
    	{
    		$this->apiServerUrl = self::LIVE_API_SERVER;
    		$this->apiServerUrlUser=self::LIVE_USER_API_SERVER;
    	}
    	else
    	{
    		$this->apiServerUrl = self::STAGE_API_SERVER;
    		$this->apiServerUrlUser=self::STAGE_USER_API_SERVER;
    	}
    	
        $this->setTemplate('mimo/form.phtml');
		parent::_construct();
    }	
    protected function getApiKey()
    {	
    	return Mage::getModel('mimo/paymentmethod')->getConfigData('api_key');
    }

    protected function getApiCode()
    {    
    	return Mage::getModel('mimo/paymentmethod')->getConfigData('api_code');
    }
    public function getOnepage()
    {
    	return $this->getUrl('checkout/multishipping/billing/', array('_secure'=>true));
    }   
    public function getQueryString()
    {    	
    	return  $this->getRequest()->getParam('code');
    }    
    /**
     * Get oauth authenitcation URL
     *
     * @return string URL
     */
    public function getAuthUrl()
    {
    	$params = array(
    			'client_id' => $this->getApiKey(),
    			'url' => $this->getOnepage(),
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
    		return $this->setError('Please pass an oauth code.');
    	}    
    	$params = array(
    			'client_id' => $this->getApiKey(),
    			'client_secret' => $this->getApiCode(),
    			'url' => $this->getOnepage(),
    			'grant_type' => 'authorization_code',
    			'code' => $_GET['code']
    	);
    	$url =  $this->apiServerUrl.'/token?' . http_build_query($params);    	
    	$response = $this->curl($url, 'POST');
    	
    	 
    	if (isset($response['error'])) {
    		$this->errorMessage = $response['error_description'];
    		return false;
    	}
    	/* if(isset($response['access_token'])){
    	 */
    	
    	return $response['access_token'];
    /*	}
    	 else{
    		return;
    	} */
    }
    public function getError()
    {
    	if (!$this->errorMessage) {
    		return false;
    	}
    
    	$error = $this->errorMessage;
    	$this->errorMessage = false;
    
    	return $error;
    }
    
    /**
     * @param string $message Error message
     */
    protected function setError($message)
    {
    	$this->errorMessage = $message;
    }
    
    /**
     * Parse API response
     *
     * @param array $response
     * @return array
     */
    protected function parse($response)
    {
    	if (!$response['Success']) {
    		$this->errorMessage = $response['Message'];
    
    		// Exception for /register method
    		if ($response['Response']) {
    			$this->errorMessage .= " :: " . json_encode($response['Response']);
    		}
    
    		return false;
    	}
    
    	return $response['Response'];
    }
    /**
     * Grab information for the given transaction ID
     *
     * @param float amount to which information is pulled
     * @return array Transaction information
     */
    public function transaction($amount = false,$notes='Magento Money Transfer')
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
    #	print_r($response);exit;
    	return $response;
    }
    public function isPlaceOrder()
    { 
    	$info = $this->getInfoInstance();
    	if ($info instanceof Mage_Sales_Model_Quote_Payment) {
    		return false;
    	} elseif ($info instanceof Mage_Sales_Model_Order_Payment) {
    		return true;
    	}
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
    	$params['access_token'] = $this->getToken();
    
    	$delimiter = (strpos($request, '?') === false) ? '?' : '&';
    	$url =  $request . $delimiter . http_build_query($params);
    	
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
    	$params['access_token'] = $this->getToken();
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
   
    	// If HTTP response wasn't 200,
    	// log it as an error!
    	$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    	
    	if ($code !== 200 && $code!=='201' ) {
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
    
    /**
    * @param string $token oauth token
    */
    public function setToken($token)
    {
    $this->accessToken = $token;
    }
    
    /**
    * @return string oauth token
    */
    public function getToken()
    {
 		   return $this->accessToken;
    }
} 
