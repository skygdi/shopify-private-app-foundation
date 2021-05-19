<?php
 
namespace Skygdi\ShopifyPrivateAPPFoundation\Traits;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
 
trait ShopifyInstallTrait {
 
 	protected $token_file = "access_token.txt";
 	protected $store_domain = "store_domain.txt";

 	function loadShopDomain(){
 		if( !file_exists(storage_path()."/".$this->store_domain) ) abort(403,"Please re-install the APP");
 		$fp = fopen(storage_path()."/".$this->store_domain,"r");
		return fread($fp,1000);
 	}
 	
 	function loadAccessToken(){
 		if( !file_exists(storage_path()."/".$this->token_file) ) abort(403,"Please re-install the APP");
 		$fp = fopen(storage_path()."/".$this->token_file,"r");
		return fread($fp,1000);
 	}

    function hashCheck($request){
		// Set variables for our request
		$api_key 		= env('SHOPIFY_APP_API_KEY');
		$shared_secret 	= env('SHOPIFY_APP_API_SECRET');

		$params = $request->all(); // Retrieve all request parameters
		$hmac = $request->get('hmac'); // Retrieve HMAC request parameter

		$params = array_diff_key($params, array('hmac' => '')); // Remove hmac from params
		ksort($params); // Sort params lexographically
		$computed_hmac = hash_hmac('sha256', http_build_query($params), $shared_secret);

		if (hash_equals($hmac, $computed_hmac)) return true;
		return false;
	}

    function install(Request $request){
    	// Set variables for our request
		$shop = $request->get('shop');

		$api_key = env('SHOPIFY_APP_API_KEY');
		$scopes = env('SHOPIFY_APP_API_SCOPES');
		$redirect_uri = env('APP_URL')."/install_authorize";

		// Build install/approval URL to redirect to
		$install_url = "https://" . $shop . "/admin/oauth/authorize?client_id=" . $api_key . "&scope=" . $scopes . "&redirect_uri=" . urlencode($redirect_uri);

		// Redirect
		return redirect()->to($install_url);
    }

    function installAuthorize(Request $request){
    	// Set variables for our request
		$api_key = env('SHOPIFY_APP_API_KEY');
		$shared_secret = env('SHOPIFY_APP_API_SECRET');

		// Use hmac data to check that the response is from Shopify or not
		//if (hash_equals($hmac, $computed_hmac)) {
		if( $this->hashCheck($request) ){
			// Set variables for our request
			// Generate access token URL
			$access_token_url = "https://" . $request->get('shop') . "/admin/oauth/access_token";
			$client = new \GuzzleHttp\Client();
			try {
			    $response = $client->post($access_token_url, [
			    			'headers' => [
						        'Content-Type' 	=> 'application/json',
			        			'Accept'     	=> 'application/json'
						    ],
						    'query' => [
								"client_id" 	=> env('SHOPIFY_APP_API_KEY'), // Your API key
								"client_secret" => env('SHOPIFY_APP_API_SECRET'), // Your app credentials (secret key)
								"code" 			=> $request->get('code') // Grab the access key from the URL
							]
						]);
			    $result = json_decode($response->getBody(),true);
			    //dd($result);
			    $access_token = $result['access_token'];
			    // Show the access token (don't do this in production!)
			    // Store the access token
			    $fp = fopen(storage_path()."/".$this->token_file,"w+b");
	    		fwrite($fp, $access_token);

	    		$fp = fopen(storage_path()."/".$this->store_domain,"w+b");
	    		fwrite($fp, $request->get('shop'));
	    		

	    		//Redirect back to Apps
	    		return redirect()->to("https://".$request->get('shop')."/admin/apps");
			} catch (RequestException $e) {
				dd($e);
			    //echo Psr7\str($e->getRequest());
			    if ($e->hasResponse()) {
			    	echo $e->getResponse()->getBody();
			        //echo Psr7\str($e->getResponse());
			    }
			}
		} else {
			// Someone is trying to be shady!
			abort(403,"Hash check fail");
		}
    }
 
}
 