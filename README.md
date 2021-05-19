# A Shopify private APP skeleton


## Setup:
### Install:
```
composer require skygdi/shopify-private-app-foundation
```
### APP Setup: 
#### Allowed redirection URL(s):
```
https://your-app.ngrok.io/install
https://your-app.ngrok.io/install_authorize
```
#### Modify your .env:
```
SHOPIFY_APP_API_KEY="APP_key"
SHOPIFY_APP_API_SECRET="APP_secret"
SHOPIFY_APP_API_SCOPES="read_products,read_product_listings,read_themes,write_themes,read_script_tags,write_script_tags"
```
#### build your logic controller:
##### The entry URL is "/". So, you need to define this route on your own.
```
use Skygdi\ShopifyPrivateAPPFoundation\Traits\ShopifyInstallTrait;
class HomeController extends Controller
{
  use ShopifyInstallTrait; //Add
  function entry(Request $request){
    if( !$this->hashCheck($request) ) abort(403,"No Access Token Found");
    //Hash check success, means it came from Shopify APP.
    //Build your login code from here
    return redirect()->to('/admin');
  }
}
```
### Usage: 
#### Communicate with API token:
```
$this->loadShopDomain();
$this->loadAccessToken();
//Do your REST API call
```