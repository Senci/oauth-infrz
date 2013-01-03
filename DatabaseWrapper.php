<?php

namespace Infrz\OAuth;

class DatabaseWrapper
{
    protected $pdo;

    public function __construct()
    {
        $this->pdo = new \PDO('sqlite:oauth-infrz.sqlite3');
        $this->setUpDatabase();
        //$this->loadFixtures();
    }

    /**
     * Loads fixtures for test- & dev-environment
     */
    public function loadFixtures()
    {
        // client fixtures
        $client = $this->insertClient('Trustworthy inc.', 'A corporation you can trust!', 'https://trustworthy.com/');

        // user fixtures
        $user = $this->insertUser('2king', 'Joe', 'King', 'joe@king.com');

        // auth_token fixtures
        $this->insertAuthToken($client, $user);

        // auth_code fixtures
        $this->insertAuthCode($client, $user);
    }

    /**
     * Creates a client in the database
     *
     * @param string $name The Name of the Client
     * @param string $description A brief description
     * @param string $redirect_uri
     * @return \StdClass The Client
     */
    public function insertClient($name, $description, $redirect_uri)
    {
        $client_id = $this->getUniqueHash($name, 'client', 'client_id');
        $client_secret = $this->getUniqueHash($redirect_uri, 'client', 'client_secret');
        $insert_client = 'INSERT INTO client (name, description, client_id, client_secret, redirect_uri)
                          VALUES (:name, :description, :client_id, :client_secret, :redirect_uri)';
        $query = $this->pdo->prepare($insert_client);
        $query->bindValue(':name', $name);
        $query->bindValue(':description', $description);
        $query->bindValue(':client_id', $client_id);
        $query->bindValue(':client_secret', $client_secret);
        $query->bindValue(':redirect_uri', $redirect_uri);
        $query->execute();

        return $this->getClientById($client_id);
    }

    /**
     * Returns a client by its client_id
     *
     * @param string $client_id
     * @return \StdClass client as StdClass
     */
    public function getClientById($client_id)
    {
        $query = $this->pdo->prepare('SELECT * FROM client WHERE client_id = :client_id;');
        $query->bindParam(':client_id', $client_id);
        $query->execute();
        $result = $query->fetchObject();

        return $result;
    }

    /**
     * Creates a user in the database
     *
     * @param string $alias An identifier for the IT at UHH. also known as "Kennung"
     * @param string $first_name
     * @param string $last_name
     * @param string $email
     * @return \StdClass The user as StdClass
     */
    public function insertUser($alias, $first_name, $last_name, $email)
    {
        $insert_user = 'INSERT INTO user (alias, first_name, last_name, email)
                        VALUES (:alias, :first_name, :last_name, :email);';
        $query = $this->pdo->prepare($insert_user);
        $query->bindValue(':alias', $alias);
        $query->bindValue(':first_name', $first_name);
        $query->bindValue(':last_name', $last_name);
        $query->bindValue(':email', $email);
        $query->execute();

        return $this->getUserByAlias($alias);
    }

    /**
     * Returns a user by its alias (also known as "Kennung")
     *
     * @param string $alias
     * @return \StdClass user as StdClass
     */
    public function getUserByAlias($alias)
    {
        $query = $this->pdo->prepare('SELECT * FROM user WHERE alias = :alias;');
        $query->bindParam(':alias', $alias);
        $query->execute();
        $result = $query->fetchObject();

        return $result;
    }

    /**
     * Creates an auth_token for the client and user in the database
     *
     * @param \StdClass $client the client object as retrieved from db
     * @param \StdClass $user the user object as retrieved from db
     * @return \StdClass the auth_token as StdClass
     */
    public function insertAuthToken(\StdClass $client, \StdClass $user)
    {
        $auth_token = $this->getUniqueHash($client->name, 'auth_token', 'token');
        $insert_token = 'INSERT INTO auth_token (user_id, client_id, token)
                         VALUES (:user_id, :client_id, :token);';
        $query = $this->pdo->prepare($insert_token);
        $query->bindValue('user_id', $user->id);
        $query->bindValue('client_id', $client->id);
        $query->bindValue('token', $auth_token);
        $query->execute();

        return $this->getAuthTokenByToken($auth_token);
    }

    /**
     * Returns an auth_token by the auth_token value
     *
     * @param $token
     * @return \StdClass the auth_token as StdClass
     */
    public function getAuthTokenByToken($token)
    {
        $query = $this->pdo->prepare('SELECT * FROM auth_token WHERE token = :token;');
        $query->bindParam(':token', $token);
        $query->execute();
        $result = $query->fetchObject();

        return $result;
    }

    /**
     * Creates an auth_code for the client and user in the database
     *
     * @param \StdClass $user
     * @param \StdClass $client
     * @return \StdClass the auth_code as StdClass
     */
    public function insertAuthCode(\StdClass $user, \StdClass $client)
    {
        $auth_code = $this->getUniqueHash($user->alias, 'auth_code', 'code');
        $insert_token = 'INSERT INTO auth_code (user_id, client_id, code)
                         VALUES (:user_id, :client_id, :code);';
        $query = $this->pdo->prepare($insert_token);
        $query->bindValue('user_id', $user->id);
        $query->bindValue('client_id', $client->id);
        $query->bindValue('code', $auth_code);
        $query->execute();

        return $this->getAuthCodeByCode($auth_code);
    }

    /**
     * Returns an auth_code by the auth_code value
     *
     * @param $code
     * @return \StdClass the auth_code as StdClass
     */
    public function getAuthCodeByCode($code)
    {
        $query = $this->pdo->prepare('SELECT * FROM auth_code WHERE code = :code;');
        $query->bindParam(':code', $code);
        $query->execute();
        $result = $query->fetchObject();

        return $result;
    }

    /**
     * Generates a salted hash which is unique for its table-column.
     *
     * @param string $data string to be hashed
     * @param string $tableName name of the table in which the string has to be unique
     * @param string $columnName name of the column of the table in which the string has to be unique
     * @return string the unique hash with 128 characters
     * @throws \PDOException thrown when $tableName or $columnName are wrong
     */
    protected function getUniqueHash($data, $tableName, $columnName)
    {
        $i = 0;
        do {
            $now = new \DateTime();
            $result = sha1($i . $now->getTimestamp());
            $result = hash('sha512', $result . $data);
            $select = sprintf('SELECT id FROM %s WHERE %s = :hash;', $tableName, $columnName);
            $query = $this->pdo->prepare($select);
            if (!$query) {
                throw new \PDOException(sprintf('"%s" is not a valid query.'), $select);
            }
            $query->bindParam(':hash', $result);
            $query->execute();
            $duplicate = $query->fetchObject();
            $i++;
        } while ($duplicate);

        return $result;
    }

    /**
     * Initializes a Database
     *
     * @param bool $forceDropTables indicates whether to drop the current db. USE WITH CAUTION!
     */
    public function setUpDatabase($forceDropTables = false)
    {
        if ($forceDropTables) {
            $this->pdo->exec('DROP TABLE client');
            $this->pdo->exec('DROP TABLE user');
            $this->pdo->exec('DROP TABLE auth_token');
            $this->pdo->exec('DROP TABLE auth_code');
        }

        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS client (
            id INTEGER primary key,
            name varchar(100),
            description text,
            client_id varchar(128) unique,
            client_secret varchar(128),
            redirect_uri varchar);'
        );

        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS user (
            id INTEGER primary key,
            alias varchar unique,
            first_name varchar,
            last_name varchar,
            email varchar);'
        );

        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS auth_token (
            id INTEGER primary key,
            user_id int,
            client_id int,
            token varchar(128) unique);'
        );

        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS auth_code (
            id INTEGER primary key,
            user_id int,
            client_id int,
            code varchar(128) unique);'
        );
    }
}
