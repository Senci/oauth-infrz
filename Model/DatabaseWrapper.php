<?php
/**
 * @author   Senad Licina <senad@licina.eu>
 * @license  http://www.gnu.org/licenses/gpl.html GPLv3
 * @link     https://github.com/Senci/oauth-infrz/
 */

namespace Infrz\OAuth\Model;

use Infrz\OAuth\Model\Client;
use Infrz\OAuth\Model\User;
use Infrz\OAuth\Model\AuthToken;
use Infrz\OAuth\Model\AuthCode;

class DatabaseWrapper
{
    protected $db;

    public function __construct()
    {
        $this->db = new \PDO('sqlite:oauth-infrz.sqlite3');
        $this->setUpDatabase();
        //$this->loadFixtures();

//        echo "<pre>";
//
//        $stmt = $this->db->prepare('SELECT * FROM user;');
//        $user = new User();
//        var_dump($stmt->setFetchMode(\PDO::FETCH_INTO, $user));
//        $stmt->execute();
//        $stmt->fetch();
//        var_dump($user);
//
//        echo "</pre>";
//        exit();
    }

    /**
     * Loads fixtures for test- & dev-environment
     */
    public function loadFixtures()
    {
        // user fixtures
        $user = $this->insertUser('2king', 'Joe', 'King', 'joe@king.com');

        // client fixtures
        $client = $this->insertClient('Trustworthy inc.', $user, 'A corporation you can trust!', 'https://tw.com/');

        // auth_token fixtures
        $this->insertAuthToken($client, $user);

        // auth_code fixtures
        $this->insertAuthCode($client, $user);
    }

    /**
     * Creates a client in the database
     *
     * @param string $name The Name of the Client
     * @param User $user The Name of the Client
     * @param string $description A brief description
     * @param string $redirect_uri
     * @return Client The Client
     */
    public function insertClient($name, User $user, $description, $redirect_uri)
    {
        $client_id = $this->getUniqueHash($name, 'client', 'client_id');
        $client_secret = $this->getUniqueHash($redirect_uri, 'client', 'client_secret');
        $insert_query = 'INSERT INTO client (name, user_id, description, client_id, client_secret, redirect_uri)
                          VALUES (:name, :user, :description, :client_id, :client_secret, :redirect_uri)';
        $stmt = $this->db->prepare($insert_query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':user', $user->id);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':client_id', $client_id);
        $stmt->bindParam(':client_secret', $client_secret);
        $stmt->bindParam(':redirect_uri', $redirect_uri);
        $stmt->execute();

        return $this->getClientById($client_id);
    }

    /**
     * Deletes a client and all its data from database by the client_id
     *
     * @param string $client_id
     * @return bool Indicates whether the delete was successful.
     */
    public function deleteClient($client_id)
    {
        $client = $this->getClientById($client_id);

        // delete all token belonging to the client
        $selectToken_query = 'SELECT token FROM auth_token WHERE client_id = :client_id';
        $token_stmt = $this->db->prepare($selectToken_query);
        $token_stmt->bindParam(':client_id', $client->id);
        $token_stmt->execute();

        foreach ($token_stmt as $token) {
            if (!$this->deleteAuthToken($token['token'])) {
                throw new \PDOException('There has been an error deleting all tokens from client.');
            }
        }

        // delete all codes belonging to the client
        $selectCodeQuery = 'SELECT code FROM auth_code WHERE client_id = :client_id';
        $code_stmt = $this->db->prepare($selectCodeQuery);
        $code_stmt->bindParam(':client_id', $client->id);
        $code_stmt->execute();

        foreach ($code_stmt as $code) {
            if (!$this->deleteAuthCode($code['code'])) {
                throw new \PDOException('There has been an error deleting all codes from client.');
            }
        }

        // delete client itself
        $delete_query = 'DELETE FROM client WHERE client_id = :client_id';
        $stmt = $this->db->prepare($delete_query);
        $stmt->bindParam(':client_id', $client_id);

        return $stmt->execute();
    }

    /**
     * Returns a client by its client_id
     *
     * @param string $client_id
     * @return Client client as StdClass
     */
    public function getClientById($client_id)
    {
        $client = new Client();
        $stmt = $this->db->prepare('SELECT * FROM client WHERE client_id = :client_id;');
        $stmt->bindParam(':client_id', $client_id);
        $stmt->setFetchMode(\PDO::FETCH_INTO, $client);
        $stmt->execute();
        $stmt->fetch();

        return $client;
    }

    /**
     * Creates a user in the database
     *
     * @param string $alias An identifier for the IT at UHH. also known as "Kennung"
     * @param string $first_name
     * @param string $last_name
     * @param string $email
     * @return User The User
     */
    public function insertUser($alias, $first_name, $last_name, $email)
    {
        $insert_query = 'INSERT INTO user (alias, first_name, last_name, email)
                        VALUES (:alias, :first_name, :last_name, :email);';
        $stmt = $this->db->prepare($insert_query);
        $stmt->bindParam(':alias', $alias);
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        return $this->getUserByAlias($alias);
    }

    /**
     * Deletes an user from database by the alias
     *
     * @param string $alias
     * @return bool Indicates whether the delete was successful.
     */
    public function deleteUser($alias)
    {
        $delete_query = 'DELETE FROM auth_token WHERE alias = :alias';
        $stmt = $this->db->prepare($delete_query);
        $stmt->bindParam(':alias', $alias);

        return $stmt->execute();
    }

    /**
     * Returns a user by its alias (also known as "Kennung")
     *
     * @param string $alias
     * @return \StdClass user as StdClass
     */
    public function getUserByAlias($alias)
    {
        $user = new User();
        $stmt = $this->db->prepare('SELECT * FROM user WHERE alias = :alias;');
        $stmt->setFetchMode(\PDO::FETCH_INTO, $user);
        $stmt->bindParam(':alias', $alias);
        $stmt->execute();
        $stmt->fetch();

        return $user;
    }

    /**
     * Creates an auth_token for the client and user in the database
     *
     * @param Client $client the client object as retrieved from db
     * @param User $user the user object as retrieved from db
     * @return AuthToken the auth_token as StdClass
     */
    public function insertAuthToken(CLient $client, User $user)
    {
        $auth_token = $this->getUniqueHash($client->name, 'auth_token', 'token');
        $insert_token = 'INSERT INTO auth_token (user_id, client_id, token)
                         VALUES (:user_id, :client_id, :token);';
        $stmt = $this->db->prepare($insert_token);
        $stmt->bindParam('user_id', $user->id);
        $stmt->bindParam('client_id', $client->id);
        $stmt->bindParam('token', $auth_token);
        $stmt->execute();

        return $this->getAuthTokenByToken($auth_token);
    }

    /**
     * Deletes an auth_token from database by the auth_token-value
     *
     * @param string $auth_token
     * @return bool Indicates whether the delete was successful.
     */
    public function deleteAuthToken($auth_token)
    {
        $query = 'DELETE FROM auth_token WHERE token = :auth_token';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':auth_token', $auth_token);

        return $stmt->execute();
    }

    /**
     * Returns an auth_token by the auth_token value
     *
     * @param $token
     * @return AuthToken the auth_token as StdClass
     */
    public function getAuthTokenByToken($token)
    {
        $auth_token = new AuthToken();
        $stmt = $this->db->prepare('SELECT * FROM auth_token WHERE token = :token;');
        $stmt->setFetchMode(\PDO::FETCH_INTO, $auth_token);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        $stmt->fetch();

        return $auth_token;
    }

    /**
     * Creates an auth_code for the client and user in the database
     *
     * @param User $user
     * @param Client $client
     * @return AuthCode the auth_code
     */
    public function insertAuthCode(\StdClass $client, \StdClass $user)
    {
        $auth_code = $this->getUniqueHash($user->alias, 'auth_code', 'code');
        $query = 'INSERT INTO auth_code (user_id, client_id, code)
                         VALUES (:user_id, :client_id, :code);';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam('user_id', $user->id);
        $stmt->bindParam('client_id', $client->id);
        $stmt->bindParam('code', $auth_code);
        $stmt->execute();

        return $this->getAuthCodeByCode($auth_code);
    }

    /**
     * Deletes an auth_code from database by the auth_code-value
     *
     * @param string $auth_code
     * @return bool Indicates whether the delete was successful.
     */
    public function deleteAuthCode($auth_code)
    {
        $query = 'DELETE FROM auth_code WHERE code = :auth_code';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':auth_code', $auth_code);

        return $stmt->execute();
    }

    /**
     * Returns an auth_code by the auth_code value
     *
     * @param $code
     * @return AuthCode the auth_code
     */
    public function getAuthCodeByCode($code)
    {
        $query = new AuthCode();
        $stmt = $this->db->prepare('SELECT * FROM auth_code WHERE code = :code;');
        $stmt->setFetchMode(\PDO::FETCH_INTO, $query);
        $stmt->bindParam(':code', $code);
        $stmt->execute();
        $result = $stmt->fetch();

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
            $query = $this->db->prepare($select);
            if (!$query) {
                throw new \PDOException(sprintf('"%s" is not a valid query.'), $select);
            }
            $query->bindParam(':hash', $result);
            $query->execute();
            $duplicate = $query->fetch();
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
            $this->db->exec('DROP TABLE client');
            $this->db->exec('DROP TABLE user');
            $this->db->exec('DROP TABLE auth_token');
            $this->db->exec('DROP TABLE auth_code');
        }

        $this->db->exec(
            'CREATE TABLE IF NOT EXISTS client (
            id INTEGER primary key,
            name varchar(100),
            user_id,
            description text,
            client_id varchar(128) unique,
            client_secret varchar(128),
            redirect_uri varchar);'
        );

        $this->db->exec(
            'CREATE TABLE IF NOT EXISTS user (
            id INTEGER primary key,
            alias varchar unique,
            first_name varchar,
            last_name varchar,
            email varchar);'
        );

        $this->db->exec(
            'CREATE TABLE IF NOT EXISTS auth_token (
            id INTEGER primary key,
            user_id int,
            client_id int,
            token varchar(128) unique);'
        );

        $this->db->exec(
            'CREATE TABLE IF NOT EXISTS auth_code (
            id INTEGER primary key,
            user_id int,
            client_id int,
            code varchar(128) unique);'
        );
    }
}
