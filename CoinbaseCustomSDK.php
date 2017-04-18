<?php

/**
 * CoinbaseCustomSDK - A custom class built to handle coinbase transactions on http://gold2naira.com.
 *
 * PHP Version 5.6
 * @package CoinbaseCustomSDK
 * @author George Imoedemhe (https://linkedin.com/georgeio) <george.imoedemhe@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @note This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */

define( "CB_API_BASE", "https://api.coinbase.com/v2/" );
define( "CB_TIME_API", CB_API_BASE . "time" );
define( "CB_VERSION", "2015-04-08" );


class CoinbaseCustomSDK
{ 
	private $api_key;
	private $api_secret;
	private $account_id;
	private $request_time;
	private $request_signature;
	private $request_headers;
	private $endpoint;


	public function __construct($api_key = '', $api_secret	= '')
	{
		$this->api_key	=	$api_key;
		$this->api_secret	=	$api_secret;
		$this->setAccountId();
	}

	/**
	* Prepare request header.
	*
	* @param string $calling_method
	*
	* @return null
	*/
	private function prepareHeader($calling_method)
	{
		$this->request_time = $this->getServerTime();
		$this->request_signature = $this->calculateRequestSign($calling_method);

		$this->request_headers = [
								"cache-control: no-cache",
								"content-type: application/json",
					    		"cb-version: " . CB_VERSION,
								"cb-access-timestamp: " . $this->request_time, 
								"cb-access-sign: " . $this->request_signature,
								"cb-access-key: " . $this->api_key,
							];
	}

	/**
	* Computes signature for request.
	*
	* @param string $calling_method
	*
	* @return string
	*/
	private function calculateRequestSign($calling_method)
	{
		$allowed_methods = ['getAccounts', 'createNewAddress', 'getAddressTransactions'];

		if( ! in_array($calling_method, $allowed_methods) ) {
			throw new Exception("Error: Call to unallowed method: '$calling_method'");	

			return;
		}

		if( $calling_method == 'getAccounts' ) {
			$hash = hash_hmac('sha256', $this->request_time . 'GET' . '/v2/' . $this->endpoint, $this->api_secret);
		}

		if( $calling_method == 'createNewAddress' ) {
			$hash = hash_hmac('sha256', $this->request_time . 'POST' . '/v2/' . $this->endpoint . '{"name": "' . $this->address_name . '"}', $this->api_secret);
		}

		if( $calling_method == 'getAddressTransactions' ) {
			$hash = hash_hmac('sha256', $this->request_time . 'GET' . '/v2/' . $this->endpoint, $this->api_secret);
		}

		if( ! $hash ) {
			throw new Exception("Error: Hash Could not be calculated");
			return;
		}

		return $hash;
	}

	/**
	* Set the primary account id based on the API credentials.
	*
	* @param null
	*
	* @return null
	*/
	public function setAccountId()
	{
		$accounts = $this->getAccounts();

		if( ! count($accounts) ) {
			throw new Exception("Error: No accounts found.");
			return;	
		}

		foreach ($accounts as $account) {
			if( $account->primary == true ) {
				$this->account_id = $account->id;
				break;
			}

			continue;
		}

		return;
	}

	/**
	* Retreive primary account id for the supplied API credential.
	*
	* @param null
	*
	* @return string
	*/
	public function getAccountId()
	{
		if( ! $this->account_id) {
			throw new Exception("Error: No accounts found.");
		}

		return $this->account_id;
	}


	/**
	* Retreive the current time on the coinbase server.
	*
	* @param null
	*
	* @return timestamp
	*/
	public function getServerTime()
	{

		$api_response = file_get_contents(CB_TIME_API);
		$api_response = json_decode($api_response);

		if ( ! $api_response->data || ! $api_response->data->epoch ) {
			throw new Exception("Error: Could not retreive time from the coinbase server");
			return;
		}

		return $api_response->data->epoch;
	}

	/**
	* Get list of all accounts linked to the API Key.
	*
	* @param null
	*
	* @return array
	*/
	public function getAccounts()
	{	
		$this->endpoint = 'accounts';
		$response = $this->makeGetRequest();
		$response = json_decode($response);

		return $response->data;
	}

	/**
	* Create a new Bitcoin/Ethereum address.
	*
	* @param string $name
	*
	* @return json object
	*/
	public function createNewAddress($name='New BitCoin Address On CoinBASE')
	{
		$body = "{\"name\": \"$name\"}";
		$this->address_name = $name;
		$this->endpoint = 'accounts/' . $this->getAccountId() . '/addresses';

		$response = $this->makePostRequest($body);

		return $response;
	}

	/**
	* Get list of transactions linked to an address.
	*
	* @param string $address_id
	*
	* @return json object
	*/
	public function getAddressTransactions($address_id)
	{
		$this->endpoint = 'accounts/' . $this->getAccountId() . '/addresses/' . $address_id . '/transactions';
		$response = $this->makeGetRequest();

		return $response;

	}

	public function makeGetRequest()
	{
		return $this->makeCurlRequest(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,2)[1]['function'], 'GET');
	}

	public function makePostRequest( $body = '' )
	{
		return $this->makeCurlRequest(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,2)[1]['function'], 'POST');
	}


	private function makeCurlRequest( $calling_method, $method = 'GET', $body = '')
	{

		$this->prepareHeader($calling_method);

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => "https://api.coinbase.com/v2/" . $this->endpoint,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
	     	// CURLOPT_SSL_VERIFYPEER => false,
	      //   CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "$method",
  			CURLOPT_POSTFIELDS => $body,
			CURLOPT_HTTPHEADER => $this->request_headers,
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {

		  	throw new Exception("cURL Error #:" . $err);
		  	return;
		}

		return $response;
	}

}