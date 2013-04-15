$result = base64_encode(openssl_random_pseudo_bytes(96));
$result = strtr($result, '+/=', '-_.');
