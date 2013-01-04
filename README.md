# OAuth-Infrz
Single-Sign-On solution for ActiveDirectory of "Informatik Rechenzentrum" at Uni Hamburg.

# Installation
## Installing dependencies
To install all dependencies, run `php composer.phar install` in your shell from the main directory.

# Valid Calls
## Web-Page
* __'''GET "/":__ Main page with informational text
* __GET "/login":__ Login page to be used with ActiveDirectory-Credentials
* __GET "/client"__: Client overview (only accessible if the user has permissions to manage clients)
 * __GET "/client/register"__: Form to register a new client
 * __POST "/client/register?name={n}&description={d}&redirect_uri={ru}"__: actual call to register a new client
 * __GET "/client/client?id={client_id}"__: page to the client with {client_id}

## REST
to be defined

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
