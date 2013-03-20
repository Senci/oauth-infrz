public function signIn($username, $password) {
    $host = 'ldaps://fbidc2.informatik.uni-hamburg.de';
    $port = 636;
    // establish link to $host:$port
    $link = ldap_connect($host, $port);
    $mail = $username . '@informatik.uni-hamburg.de';
    // bind to link with user credentials, return false on failure
    if (!ldap_bind($link, $mail, $password)) {
        return false;
    }
    // set domain name, set filter for search and select fields to access
    $base_dn = 'dc=informatik,dc=uni-hamburg,dc=de';
    $filter = 'uid=' . $username;
    $fields = array('uid', 'sn', 'givenname', 'memberof');
    // fire the actual search, return false on failure
    $ldap_result = ldap_search($link, $base_dn, $filter, $fields);
    if (!$ldap_result or (ldap_count_entries($link, $ldap_result) != 1)) {
        return false;
    }
    // retrieve user information and generate a User Object from it
    $ldap_user = ldap_get_entries($link, $ldap_result);
    $ldap_user = $ldap_user[0];
    ldap_close($link);
    $user = $this->generateUser($ldap_user);

    return $user;
}
