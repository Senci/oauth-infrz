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
use Infrz\OAuth\Model\PageToken;

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
        $user = $this->insertUser('2king', 'Joe King', 'joe@king.com', array('admin', 'svs', 'oauth_client'));
        $user2 = $this->insertUser('2ill', 'Eve Ill', 'eve@ill.com', array('oauth_client'));

        $scope = json_decode('{"available":["kennung","name"],"required":["kennung"],"info":{"kennung":"we need this to display identify you."}}');
        $scope_empty = json_decode('{"available":[],"required":[],"info":{}}');
        $scope_full = json_decode('{"available":["kennung","name","email","groups"],"required":["kennung","email","groups"],"info":{"kennung":"Required for identification","name":"Needed if you want to use our services with your name","email":"Required for communication","groups":"Required for authentication"}}');
        // client fixtures
        $this->insertClient(
            'Trustworthy inc.',
            $user,
            'A corporation you can trust!',
            array('tw.com', '192.168.1.56'),
            'https://tw.com/',
            $scope
        );
        $this->insertClient(
            'Ikum GmbH',
            $user,
            'Let us be friends... <3!',
            array('ig.com', 'yourmama.com'),
            'https://ig.com/',
            $scope_empty
        );
        $client = $this->insertClient(
            'Demo Application',
            $user,
            'This application is completely redundant!',
            array('https://localhost/'),
            'https://localhost/Demo',
            $scope_full
        );
        $this->insertClient(
            'Eve Pharm',
            $user2,
            'We wantz your Dataz, nao! <br/> P.S.: Buy our cheap medicine please!',
            array('ei.com'),
            'https://ei.com/',
            $scope
        );

        // auth_code fixtures
        $this->insertAuthCode($client, $user, array('kennung', 'groups'));

        // auth_token fixtures
        $auth_token = $this->insertAuthToken($client, $user, array('kennung', 'groups'));

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
     * @param \StdClass $scope
     * @return Client
     */
    public function insertClient($name, User $user, $description, $host, $redirect_uri, $scope)
    {
        $client_id = $this->getUniqueHash($name, 'client', 'client_id');
        $client_secret = $this->getUniqueHash($redirect_uri, 'client', 'client_secret');
        $insert_query = 'INSERT INTO client
                          (name, user_id, description, host, client_id, client_secret, redirect_uri, scope)
                         VALUES
                          (:name, :user_id, :description, :host, :client_id, :client_secret, :redirect_uri, :scope)';
        $stmt = $this->db->prepare($insert_query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':user_id', $user->id);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':host', json_encode($host));
        $stmt->bindParam(':client_id', $client_id);
        $stmt->bindParam(':client_secret', $client_secret);
        $stmt->bindParam(':redirect_uri', $redirect_uri);
        $stmt->bindValue(':scope', json_encode($scope));
        $stmt->execute();

        return $this->getClientByClientId($client_id);
    }

    /**
     * Updates the client in the database.
     *
     * @param Client $client
     * @return Client
     */
    public function updateClient(Client $client)
    {
        $update_query = 'UPDATE client SET
                          name = :name, description = :description, host = :host,
                          redirect_uri = :redirect_uri, scope = :scope
                         WHERE
                          id = :id';
        $stmt = $this->db->prepare($update_query);
        $stmt->bindParam(':name', $client->name);
        $stmt->bindParam(':description', $client->description);
        $stmt->bindParam(':host', json_encode($client->host));
        $stmt->bindParam(':redirect_uri', $client->redirect_uri);
        $stmt->bindValue(':scope', json_encode($client->scope));
        $stmt->bindValue(':id', $client->id);
        $stmt->execute();

        return $this->getClientById($client->id);
    }

    /**
     * Generates new client_id and client_secret and updates those in the database.
     *
     * @param $client
     */
    public function updateClientCredentials($client)
    {
        $client_id = $this->getUniqueHash($client->secret, 'client', 'client_id');
        $client_secret = $this->getUniqueHash($client->key, 'client', 'client_secret');
        $update_query = 'UPDATE client SET
                          client_id = :client_id, client_secret = :client_secret
                         WHERE
                          id = :id';
        $stmt = $this->db->prepare($update_query);
        $stmt->bindParam(':client_id', $client_id);
        $stmt->bindParam(':client_secret', $client_secret);
        $stmt->bindValue(':id', $client->id);
        $stmt->execute();

        return $this->getClientById($client->id);
    }

    /**
     * Deletes a Client and all its data from database.
     *
     * @param Client $client
     * @return bool Indicates whether the delete was successful.
     * @throws \PDOException Throws an exception when there has been a db error.
     */
    public function deleteClient(Client $client)
    {
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
        $delete_query = 'DELETE FROM client WHERE id = :id';
        $stmt = $this->db->prepare($delete_query);
        $stmt->bindParam(':id', $client->id);

        return $stmt->execute();
    }

    /**
     * Returns a client by its id (in database).
     *
     * @param string $id
     * @return Client
     */
    public function getClientById($id)
    {
        $stmt = $this->db->prepare('SELECT * FROM client WHERE id = :id;');
        $stmt->bindParam(':id', $id);
        $stmt->setFetchMode(\PDO::FETCH_CLASS, 'Infrz\OAuth\Model\Client');
        $stmt->execute();
        $client = $stmt->fetch();

        return $client;
    }

    /**
     * Returns a client by its client_id.
     *
     * @param string $client_id
     * @return Client
     */
    public function getClientByClientId($client_id)
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
     * @param string $kennung An identifier for the IT at UHH. also known as "Kennung".
     * @param string $name
     * @param string $email
     * @param array $groups
     * @return User
     */
    public function insertUser($kennung, $name, $email, $groups)
    {
        $insert_query = 'INSERT INTO user (kennung, name, email, groups)
                        VALUES (:kennung, :name, :email, :groups);';
        $stmt = $this->db->prepare($insert_query);
        $stmt->bindParam(':kennung', $kennung);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindValue(':groups', json_encode($groups));
        $stmt->execute();

        return $this->getUserByKennung($kennung);
    }

    /**
     * Updates the user in the database.
     *
     * @param User $user
     * @return User
     */
    public function updateUser(User $user)
    {
        $update_query = 'UPDATE user SET
                          groups = :groups
                         WHERE
                          id = :id';
        $stmt = $this->db->prepare($update_query);
        $stmt->bindValue(':groups', json_encode($user->groups));
        $stmt->bindValue(':id', $user->id);
        $stmt->execute();

        return $this->getUserById($user->id);
    }

    /**
     * Deletes a user from database by his kennung
     *
     * @param string $kennung
     * @return bool Indicates whether the delete was successful.
     */
    public function deleteUser($kennung)
    {
        $delete_query = 'DELETE FROM auth_token WHERE kennung = :kennung';
        $stmt = $this->db->prepare($delete_query);
        $stmt->bindParam(':kennung', $kennung);

        return $stmt->execute();
    }

    /**
     * Returns a user by his kennung
     *
     * @param string $kennung
     * @return User
     */
    public function getUserByKennung($kennung)
    {
        $stmt = $this->db->prepare('SELECT * FROM user WHERE kennung = :kennung;');
        $stmt->setFetchMode(\PDO::FETCH_CLASS, 'Infrz\OAuth\Model\User');
        $stmt->bindParam(':kennung', $kennung);
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
        $auth_code = $this->getUniqueHash($user->kennung, 'auth_code', 'code');
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
     * Returns an auth_token by the id.
     *
     * @param int $id
     * @return AuthToken
     */
    public function getAuthTokenById($id)
    {
        $stmt = $this->db->prepare('SELECT * FROM auth_token WHERE id = :id;');
        $stmt->setFetchMode(\PDO::FETCH_CLASS, 'Infrz\OAuth\Model\AuthToken');
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $auth_token = $stmt->fetch();

        return $auth_token;
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
        $stmt->bindParam('token', $refresh_token);
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
     * Creates a web_token for the user in the database.
     *
     * @param User $user The user object as retrieved from db.
     * @return WebToken
     */
    public function insertWebToken(User $user)
    {
        $web_token = $this->getUniqueHash($user->kennung, 'web_token', 'token');
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
     * Creates a page_token for the user in the database.
     *
     * @param User $user The user object as retrieved from db.
     * @return WebToken
     */
    public function insertPageToken(User $user)
    {
        $page_token = $this->getUniqueHash($user->kennung, 'page_token', 'token');
        $insert_token = 'INSERT INTO page_token (user_id, token, expires_at)
                         VALUES (:user_id, :token, :expires_at);';
        $stmt = $this->db->prepare($insert_token);
        $stmt->bindParam('user_id', $user->id);
        $stmt->bindParam('token', $page_token);
        $stmt->bindValue('expires_at', time()+(30*60));
        $stmt->execute();

        return $this->getPageTokenByToken($page_token);
    }

    /**
     * Deletes a page_token from database by the page_token-value.
     *
     * @param string $token
     * @return bool Indicates whether the delete was successful.
     */
    public function deletePageToken($token)
    {
        $query = 'DELETE FROM page_token WHERE token = :token';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':token', $token);

        return $stmt->execute();
    }

    /**
     * Returns a page_token by the page_token-value.
     *
     * @param $token
     * @return PageToken
     */
    public function getPageTokenByToken($token)
    {
        $stmt = $this->db->prepare('SELECT * FROM page_token WHERE token = :token;');
        $stmt->setFetchMode(\PDO::FETCH_CLASS, 'Infrz\OAuth\Model\PageToken');
        $stmt->bindParam(':token', $token);
        $stmt->execute();

        return $stmt->fetch();
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
        do {
            $random = bin2hex(openssl_random_pseudo_bytes(50));
            $hashMe = sprintf('%s - %s : %s', $random, $data, time());
            $result = hash('sha512', $hashMe);
            $select = sprintf('SELECT id FROM %s WHERE %s = :hash;', $tableName, $columnName);
            $query = $this->db->prepare($select);
            if (!$query) {
                throw new \PDOException(sprintf('"%s" is not a valid query.', $select));
            }
            $query->bindParam(':hash', $result);
            $query->execute();
            $duplicate = $query->fetch();
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
            $this->db->exec('DROP TABLE page_token');
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
            host varchar,
            scope varchar);'
        );

        $this->db->exec(
            'CREATE TABLE IF NOT EXISTS user (
            id INTEGER primary key,
            kennung varchar unique,
            name  varchar,
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

        $this->db->exec(
            'CREATE TABLE IF NOT EXISTS page_token (
            id INTEGER primary key,
            user_id int,
            token varchar(128) unique,
            expires_at int);'
        );
    }
}
