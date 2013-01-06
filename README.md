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
* __GET "/":__ The main page with informational text.
* __GET "/login?redirect={r}":__ The login form to be used with ActiveDirectory-Credentials.
 * _redirect_: The url to which the user is redirected after successful login.
* __POST "/login/authorize?username={un}&password={pw}&redirect={r}":__ The login call, displaying status on login call and redirecting on success.
 * _username_: The infrz-alias ("Kennung") from the user.
 * _password_: The password to the Infrz account.
 * _redirect_: The url to which the user is redirected after successful login.
* __GET "/authorize?client_id={cid}&redirect_uri={ru}":__ Authorize form displaying information about the client and scope.
 * _client\_id_: The `client_id` of the client requesting an authorization.
 * _redirect\_uri_: The url to which the user is redirected after successful login.
 * The `grant\_type` variable (from the OAuth2 specification) is intentionally being ignored. The authorize-type in this stage is `code`.
* __POST "/authorize/grant?code={c}":__ Displaying information about the access grant and redirecting to client-site with code.
 * _code_: The verification `code` (from the OAuth2 specification) denoting that the user has accepted the permissions.
* __GET "/client"__: The client overview displays a list of all clients the currently logged in user manages.
 * The client module (and all its actions) is only accessible if the user has permissions to manage clients.
* __GET "/client/new"__: The form to register a new client.
* __POST "/client/register?name={n}&description={d}&redirect_uri={ru}"__: The actual call to register a new client.
 * _name_: The name of the new client.
 * _description_: A short description of the new client and its functionality/purpose.
 * _redirect_uri_: The url to which the user is redirected for authorization.
* __GET "/client/client?client_id={cid}"__: The page to a specific client.
 * _client\_id_: The `client_id` of the client.

## REST
REST calls return their information as JSON.
* __POST "/authorize/token?grant_type={gt}client_id={cid}&client_secret={cs}&code={c}&redirect_uri={ru}"__: returns a new token if valid
* __GET "/user?alias={a}&oauth_token={oat}"__: returns the user-information

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
