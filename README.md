# OAuth-Infrz
Single-Sign-On solution for ActiveDirectory of "Informatik Rechenzentrum" at Uni Hamburg.

# Installation
## Installing dependencies
To install all dependencies, run `php composer.phar install` in your shell from the main directory.

# Database Tables
SQLite3 is used as Database. The Database is saved in `oauth-infrz.db`.

## client
* id: int
* name: varchar
* description: text
* client_id: varchar
* client_secret: varchar
* redirect_url: varchar

## user
* id: int
* name: varchar
* email: varchar
* {various user information}

## auth_token
* id: int
* user_id: int
* client_id: int
* token: varchar

## auth_code
* id: int
* user_id: int
* client_id: int
* code: varchar

