require_once('Path/To/Client/Client.php');
use Infrz\OAuth\Client\Client;

// initialize Client
$client = new Client();
// generate the Authorization Request Uri
$auth_request_uri = $client->getAuthorizationRequestUri();
// exchange auth_code for auth_token
$auth_token = $client->getAuthToken($code);
// retrieve user information by auth_token
$user = $client->getUser($auth_token)
