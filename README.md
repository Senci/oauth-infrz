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
 * The *response_type* variable (from the OAuth2 specification) is intentionally being ignored. The response-type at this stage is *code*.
* **POST "/authorize/grant?code={c}":** Displaying information about the access grant and redirecting to client-site with code.
 * *code*: The verification *code* (from the OAuth2 specification) denoting that the user has accepted the permissions.
* **GET "/client"**: The client overview displays a list of all clients the currently logged in user manages.
 * The client module (and all its actions) is only accessible if the user has permissions to manage clients.
* **GET "/client/new"**: The form to register a new client.
* **POST "/client/register?name={n}&description={d}&redirect_uri={ru}&default_scope={ds}"**: The actual call to register a new client.
 * *name*: The name of the new client.
 * *description*: A brief description of the new client and its functionality/purpose.
 * *redirect_uri*: The url to which the user is redirected after authorization.
 * *default_scope*: The *scope* which the client is requesting by default.
* **GET "/client/_{id}"**: The object page to the *client* with the given id.
* **GET "/client/_{id}/edit"**: The edit page to the *client* with the given id.
* **POST "/client/_{id}/save?name={n}&description={d}&redirect_uri={ru}&default_scope={ds}"**: The save action to the *client* with the given id.
 * *name*: The name of the new client.
 * *description*: A brief description of the new client and its functionality/purpose.
 * *redirect_uri*: The url to which the user is redirected after authorization.
 * *default_scope*: The *scope* which the client is requesting by default.
* **POST "/client/_{id}/delete"**: Deletes the *client* with the given id.

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
* default_scope: varchar

## user
* id: INTEGER primary key
* alias: varchar unique
* first_name: varchar
* last_name: varchar
* email: varchar
* groups: varchar
* {various user information}

## auth_token
* id: INTEGER primary key
* user_id: int
* client_id: int
* token: varchar unique
* scope: varchar
* expires_at: int

## auth_code
* id: INTEGER primary key
* user_id: int
* client_id: int
* code: varchar unique
* scope: varchar
* created: int

## refresh_token
* id: INTEGER primary key
* auth_token_id: int
* token: varchar unique
* created: int

## web_token
* id: INTEGER primary key
* user_id: int
* token: varchar unique
* expires_at: int
