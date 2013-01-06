# OAuth-Infrz
Single-Sign-On solution for ActiveDirectory of "Informatik Rechenzentrum" at Uni Hamburg.

# Installation
## Installing dependencies
To install all dependencies, run `php composer.phar install` in your shell from the main directory.

# Valid Calls
## Web-Page
Web-Page calls return their information as HTML.
* __GET "/":__ Main page with informational text
* __GET "/login?redirect={r}":__ Login form to be used with ActiveDirectory-Credentials
 * __POST "/login/authorize?username={un}&password={pw}&redirect={r}":__ Login call, displaying status on login call and redirecting on success
* __GET "/authorize?client_id={cid}&response_type={rt}&redirect_uri={ru}":__ Authorize form displaying information about the client and scope
 * __POST "/authorize/grant?code={c}":__ Displaying information about the access grant and redirecting to client-site with code
* __GET "/client"__: Client overview (only accessible if the user has permissions to manage clients)
 * __GET "/client/register"__: Form to register a new client
 * __POST "/client/register?name={n}&description={d}&redirect_uri={ru}"__: Actual call to register a new client
 * __GET "/client/client?client_id={cid}"__: Page to the client

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
