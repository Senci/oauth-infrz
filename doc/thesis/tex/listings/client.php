require_once('Path/To/Client/Client.php');
use Infrz\OAuth\Client\Client;

// initialize Client
$client = new Client();
// generate the Authorization Request Uri
$auth_request_uri = $client->getAuthorizationRequestUri();
// exchange auth_code for access_token
$access_token = $client->getAccessToken($code);
// retrieve user information by access_token
$user = $client->getUser($access_token)
