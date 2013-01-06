# OAuth-Infrz
Single-Sign-On solution for ActiveDirectory of "Informatik Rechenzentrum" at Uni Hamburg.

# Installation
## Dependencies
* __twig/twig__: _"1.*"_

### Installing dependencies
To install all dependencies, run `php composer.phar install` in your shell from the main directory.

# Valid Calls
Keep in mind that all url-values have to be _urlencoded_ when passed (for convenience even on POST-requests).

## Web-Page
Web-Page calls return their information as HTML.
* **GET "/":** The main page with informational text.
* **GET "/login?redirect={r}":** The login form to be used with ActiveDirectory-Credentials.
 * *redirect*: The url to which the user is redirected after successful login.
* **POST "/login/authorize?username={un}&password={pw}&redirect={r}":** The login call, displaying status on login call and redirecting on success.
 * *username*: The infrz-alias ("Kennung") from the user.
 * *password*: The password to the Infrz account.
 * *redirect*: The url to which the user is redirected after successful login.
* **GET "/authorize?client_id={cid}&redirect_uri={ru}":** Authorize form displaying information about the client and scope.
 * *client_id*: The *client_id* of the client requesting an authorization.
 * *redirect_uri*: The url to which the user is redirected after successful permission grant.
 * The *grant_type* variable (from the OAuth2 specification) is intentionally being ignored. The authorize-type in this stage is *code*.
* **POST "/authorize/grant?code={c}":** Displaying information about the access grant and redirecting to client-site with code.
 * *code*: The verification *code* (from the OAuth2 specification) denoting that the user has accepted the permissions.
* **GET "/client"**: The client overview displays a list of all clients the currently logged in user manages.
 * The client module (and all its actions) is only accessible if the user has permissions to manage clients.
* **GET "/client/new"**: The form to register a new client.
* **POST "/client/register?name={n}&description={d}&redirect_uri={ru}"**: The actual call to register a new client.
 * *name*: The name of the new client.
 * *description*: A short description of the new client and its functionality/purpose.
 * *redirect_uri*: The url to which the user is redirected for authorization.
* **GET "/client/client?client_id={cid}"**: The page to a specific client.
 * *client_id*: The *client_id* of the client.

## REST
REST calls return their information as JSON.
* **POST "/authorize/token?grant_type={gt}client_id={cid}&client_secret={cs}&code={c}&redirect_uri={ru}"**: Returns a new authorization token (*auth_token*) if the call is valid.
 * *grant_type*: The *grant_type* (from Oauth2 specification) which is being used. The value has to be *authorization_code* or *refresh_token*.
 * *client_id*: The *client_id* from the requesting client.
 * *client_secret*: The *client_secret* mathing to the client_id.
 * *code*: Either the *verification code* from the user or a valid *refresh_token*, depending on *grant_type*-setting.
 * *redirect_uri*: The url to which the user gets forwarded after successful permission grant.
* **GET "/user?alias={a}&oauth_token={oat}"**: Returns the user-information.
 * *alias*: The infrz-alias ("Kennung") from the user.
 * *oauth_token*: The valid *oauth_token* from the client.

# Database Tables
SQLite3 is used as Database. The Database is saved in `oauth-infrz.sqlite3`.

## client
* id: INTEGER primary key
* name: varchar
* user_id: int
* description: text
* client_id: varchar unique
* client_secret: varchar
* redirect_uri: varchar

## user
* id: INTEGER primary key
* alias: varchar unique
* first_name: varchar
* last_name: varchar
* email: varchar
* {various user information}

## auth_token
* id: INTEGER primary key
* user_id: int
* client_id: int
* token: varchar unique

## auth_code
* id: INTEGER primary key
* user_id: int
* client_id: int
* code: varchar unique
