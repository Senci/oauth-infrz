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
use Infrz\OAuth\Model\RefreshToken;
use Infrz\OAuth\Model\WebToken;

class DatabaseWrapper
{
    protected $db;

    public function __construct()
    {
        $this->db = new \PDO('sqlite:oauth-infrz.sqlite3');
        $this->setUpDatabase();
        //$this->loadFixtures();

        /*echo "<pre>";

        $stmt = $this->db->prepare('SELECT * FROM user;');
        $stmt->setFetchMode(\PDO::FETCH_CLASS, 'Infrz\OAuth\Model\User');
        $stmt->execute();
        $user = $stmt->fetch();
        var_dump($user);
        var_dump($user->isMemberOf('admin'));

        echo "</pre>";
        exit();*/
    }

    /**
     * Loads fixtures for test- & dev-environment
     */
    public function loadFixtures()
    {
        // user fixtures
        $user = $this->insertUser('2king', 'Joe', 'King', 'joe@king.com', array('admin', 'svs', 'oauth_client'));

        // client fixtures
        $client = $this->insertClient(
            'Trustworthy inc.',
            $user,
            'A corporation you can trust!',
            'https://tw.com/',
            array('alias', 'groups')
        );

        // auth_code fixtures
        $this->insertAuthCode($client, $user, array('alias', 'groups'));

        // auth_token fixtures
        $auth_token = $this->insertAuthToken($client, $user, array('alias', 'groups'));

        // refresh_token fixtures
        $this->insertRefreshToken($auth_token);
    }

    /**
     * Creates a client in the database
     *
     * @param string $name The Name of the Client
     * @param User $user The Name of the Client
     * @param string $description A brief description of the new client and its functionality/purpose.
     * @param string $redirect_uri The url to which the user is redirected after authorization.
     * @param array $default_scope
     * @return Client
     */
    public function insertClient($name, User $user, $description, $redirect_uri, $default_scope)
    {
        $client_id = $this->getUniqueHash($name, 'client', 'client_id');
        $client_secret = $this->getUniqueHash($redirect_uri, 'client', 'client_secret');
        $insert_query = 'INSERT INTO client
                          (name, user_id, description, client_id, client_secret, redirect_uri, default_scope)
                         VALUES
                          (:name, :user_id, :description, :client_id, :client_secret, :redirect_uri, :default_scope)';
        $stmt = $this->db->prepare($insert_query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':user_id', $user->id);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':client_id', $client_id);
        $stmt->bindParam(':client_secret', $client_secret);
        $stmt->bindParam(':redirect_uri', $redirect_uri);
        $stmt->bindValue(':default_scope', json_encode($default_scope));
        $stmt->execute();

        return $this->getClientById($client_id);
    }

    /**
     * Deletes a client and all its data from database by the client_id.
     *
     * @param string $client_id
     * @return bool Indicates whether the delete was successful.
     * @throws \PDOException Throws an exception when there has been a db error.
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
     * Returns a client by its client_id.
     *
     * @param string $client_id
     * @return Client
     */
    public function getClientById($client_id)
    {
        $stmt = $this->db->prepare('SELECT * FROM client WHERE client_id = :client_id;');
        $stmt->bindParam(':client_id', $client_id);
        $stmt->setFetchMode(\PDO::FETCH_CLASS, 'Infrz\OAuth\Model\Client');
        $stmt->execute();
        $client = $stmt->fetch();

        return $client;
    }

    /**
     * Returns all clients from the user.
     *
     * @param string $user_id
     * @return Client
     */
    public function getClientsFromUser($user)
    {
        $stmt = $this->db->prepare('SELECT * FROM client WHERE user_id = :user_id;');
        $stmt->bindParam(':user_id', $user->id);
        $stmt->setFetchMode(\PDO::FETCH_CLASS, 'Infrz\OAuth\Model\Client');
        $stmt->execute();
        $clients = $stmt->fetchAll();

        return $clients;
    }

    /**
     * Creates a user in the database.
     *
     * @param string $alias An identifier for the IT at UHH. also known as "Kennung".
     * @param string $first_name
     * @param string $last_name
     * @param string $email
     * @param array $groups
     * @return User
     */
    public function insertUser($alias, $first_name, $last_name, $email, $groups)
    {
        $insert_query = 'INSERT INTO user (alias, first_name, last_name, email, groups)
                        VALUES (:alias, :first_name, :last_name, :email, :groups);';
        $stmt = $this->db->prepare($insert_query);
        $stmt->bindParam(':alias', $alias);
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':email', $email);
        $stmt->bindValue(':groups', json_encode($groups));
        $stmt->execute();

        return $this->getUserByAlias($alias);
    }

    /**
     * Deletes a user from database by his alias
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
     * Returns a user by his alias (also known as "Kennung").
     *
     * @param string $alias
     * @return User
     */
    public function getUserByAlias($alias)
    {
        $stmt = $this->db->prepare('SELECT * FROM user WHERE alias = :alias;');
        $stmt->setFetchMode(\PDO::FETCH_CLASS, 'Infrz\OAuth\Model\User');
        $stmt->bindParam(':alias', $alias);
        $stmt->execute();
        $user = $stmt->fetch();

        return $user;
    }

    /**
     * Returns a user by his id.
     *
     * @param string $id
     * @return User
     */
    public function getUserById($id)
    {
        $stmt = $this->db->prepare('SELECT * FROM user WHERE id = :id;');
        $stmt->setFetchMode(\PDO::FETCH_CLASS, 'Infrz\OAuth\Model\User');
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $user = $stmt->fetch();

        return $user;
    }

    /**
     * Creates an auth_code for the client and user in the database.
     *
     * @param Client $client
     * @param User $user
     * @param array $scope The scope which the user grants to the client.
     * @return AuthCode
     */
    public function insertAuthCode(Client $client, User $user, $scope)
    {
        $auth_code = $this->getUniqueHash($user->alias, 'auth_code', 'code');
        $query = 'INSERT INTO auth_code (user_id, client_id, code, scope, created)
                         VALUES (:user_id, :client_id, :code, :scope, :created);';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam('user_id', $user->id);
        $stmt->bindParam('client_id', $client->id);
        $stmt->bindParam('code', $auth_code);
        $stmt->bindValue('scope', json_encode($scope));
        $stmt->bindValue('created', time());
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
     * Returns an auth_code by the auth_code-value
     *
     * @param $code
     * @return AuthCode the auth_code
     */
    public function getAuthCodeByCode($code)
    {
        $stmt = $this->db->prepare('SELECT * FROM auth_code WHERE code = :code;');
        $stmt->setFetchMode(\PDO::FETCH_CLASS, 'Infrz\OAuth\Model\AuthCode');
        $stmt->bindParam(':code', $code);
        $stmt->execute();
        $auth_code = $stmt->fetch();

        return $auth_code;
    }

    /**
     * Creates an auth_token for the client and user in the database.
     *
     * @param Client $client The client object as retrieved from db.
     * @param User $user The user object as retrieved from db.
     * @param array $scope The scope which the user grants to the client.
     * @return AuthToken
     */
    public function insertAuthToken(Client $client, User $user, $scope)
    {
        $auth_token = $this->getUniqueHash($client->name, 'auth_token', 'token');
        $insert_token = 'INSERT INTO auth_token (user_id, client_id, token, scope, expires_at)
                         VALUES (:user_id, :client_id, :token, :scope, :expires_at);';
        $stmt = $this->db->prepare($insert_token);
        $stmt->bindParam('user_id', $user->id);
        $stmt->bindParam('client_id', $client->id);
        $stmt->bindParam('token', $auth_token);
        $stmt->bindValue('scope', json_encode($scope));
        $stmt->bindValue('expires_at', time()+(30*60));
        $stmt->execute();

        return $this->getAuthTokenByToken($auth_token);
    }

    /**
     * Deletes an auth_token from database by the auth_token-value.
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
     * Returns an auth_token by the auth_token-value.
     *
     * @param $token
     * @return AuthToken
     */
    public function getAuthTokenByToken($token)
    {
        $stmt = $this->db->prepare('SELECT * FROM auth_token WHERE token = :token;');
        $stmt->setFetchMode(\PDO::FETCH_CLASS, 'Infrz\OAuth\Model\AuthToken');
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        $auth_token = $stmt->fetch();

        return $auth_token;
    }

    /**
     * Creates a refresh_token for the auth_token in the database.
     *
     * @param AuthToken $auth_token
     * @return RefreshToken
     */
    public function insertRefreshToken(AuthToken $auth_token)
    {
        $refresh_token = $this->getUniqueHash($auth_token->token, 'refresh_token', 'token');
        $query = 'INSERT INTO refresh_token (auth_token_id, token, created)
                         VALUES (:auth_token_id, :token, :created);';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam('auth_token_id', $auth_token->id);
        $stmt->bindParam('token', $token);
        $stmt->bindValue('created', time());
        $stmt->execute();

        return $this->getRefreshTokenByToken($refresh_token);
    }

    /**
     * Deletes a refresh_token from database by the refresh_token-value.
     *
     * @param string $refresh_token
     * @return bool Indicates whether the delete was successful.
     */
    public function deleteRefreshToken($refresh_token)
    {
        $query = 'DELETE FROM refresh_token WHERE token = :token';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':token', $refresh_token);

        return $stmt->execute();
    }

    /**
     * Returns a refresh_token by the refresh_token-value.
     *
     * @param $token
     * @return RefreshToken
     */
    public function getRefreshTokenByToken($token)
    {
        $stmt = $this->db->prepare('SELECT * FROM refresh_token WHERE token = :token;');
        $stmt->setFetchMode(\PDO::FETCH_CLASS, 'Infrz\OAuth\Model\RefreshToken');
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        $refresh_token = $stmt->fetch();

        return $refresh_token;
    }

    /**
     * Creates a web_token for the client and user in the database.
     *
     * @param User $user The user object as retrieved from db.
     * @return WebToken
     */
    public function insertWebToken(User $user)
    {
        $web_token = $this->getUniqueHash($user->alias, 'web_token', 'token');
        $insert_token = 'INSERT INTO web_token (user_id, token, expires_at)
                         VALUES (:user_id, :token, :expires_at);';
        $stmt = $this->db->prepare($insert_token);
        $stmt->bindParam('user_id', $user->id);
        $stmt->bindParam('token', $web_token);
        $stmt->bindValue('expires_at', time()+(30*60));
        $stmt->execute();

        return $this->getWebTokenByToken($web_token);
    }

    /**
     * Deletes a web_token from database by the web_token-value.
     *
     * @param string $token
     * @return bool Indicates whether the delete was successful.
     */
    public function deleteWebToken($token)
    {
        $query = 'DELETE FROM web_token WHERE token = :token';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':token', $token);

        return $stmt->execute();
    }

    /**
     * Returns a web_token by the web_token-value.
     *
     * @param $token
     * @return WebToken
     */
    public function getWebTokenByToken($token)
    {
        $stmt = $this->db->prepare('SELECT * FROM web_token WHERE token = :token;');
        $stmt->setFetchMode(\PDO::FETCH_CLASS, 'Infrz\OAuth\Model\WebToken');
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        $web_token = $stmt->fetch();

        return $web_token;
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
                throw new \PDOException(sprintf('"%s" is not a valid query.', $select));
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
            $this->db->exec('DROP TABLE refresh_token');
            $this->db->exec('DROP TABLE web_token');
        }

        $this->db->exec(
            'CREATE TABLE IF NOT EXISTS client (
            id INTEGER primary key,
            name varchar(100),
            user_id,
            description text,
            client_id varchar(128) unique,
            client_secret varchar(128),
            redirect_uri varchar,
            default_scope varchar);'
        );

        $this->db->exec(
            'CREATE TABLE IF NOT EXISTS user (
            id INTEGER primary key,
            alias varchar unique,
            first_name varchar,
            last_name varchar,
            email varchar,
            groups varchar);'
        );

        $this->db->exec(
            'CREATE TABLE IF NOT EXISTS auth_token (
            id INTEGER primary key,
            user_id int,
            client_id int,
            token varchar(128) unique,
            scope varchar,
            expires_at int);'
        );

        $this->db->exec(
            'CREATE TABLE IF NOT EXISTS auth_code (
            id INTEGER primary key,
            user_id int,
            client_id int,
            code varchar(128) unique,
            scope varchar,
            created int);'
        );

        $this->db->exec(
            'CREATE TABLE IF NOT EXISTS refresh_token (
            id INTEGER primary key,
            auth_token_id int,
            token varchar(128) unique,
            created int);'
        );

        $this->db->exec(
            'CREATE TABLE IF NOT EXISTS web_token (
            id INTEGER primary key,
            user_id int,
            token varchar(128) unique,
            expires_at int);'
        );
    }
}
