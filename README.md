# OAuth-Infrz
Single-Sign-On solution for ActiveDirectory of "Informatik Rechenzentrum" at Uni Hamburg.

# Installation
## Installing dependencies
To install all dependencies, run `php composer.phar install` in your shell from the main directory.

# Database Tables
SQLite3 is used as Database. The Database is saved in `oauth-infrz.sqlite3`.

## client
* id: INTEGER primary key
* name: varchar
* description: text
* client_id: varchar unique
* client_secret: varchar
* redirect_url: varchar

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

