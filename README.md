# OAuth-Infrz
Single-Sign-On solution for ActiveDirectory of "Informatik Rechenzentrum" at Uni Hamburg.

# Installation
## Installing dependencies
To install all dependencies, run `php composer.phar install` in your shell from the main directory.

# Valid Calls
## Web-Page
* GET "/": Main page with informational text
* GET "/login": Login page to be used with ActiveDirectory-Credentials
* GET "/client": Client overview (only accessible if the user has permissions to manage clients)
** GET "/client/register": Form to register a new client
** POST "/client/register?name={n}&description={d}&redirect_uri={ru}": actual call to register a new client
** GET "/client/client?id={client_id}": page to the client with {client_id}

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
