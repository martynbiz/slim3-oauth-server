<?php
session_start();

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__)));

require APPLICATION_PATH . '/vendor/autoload.php';


$provider = new \League\OAuth2\Client\Provider\GenericProvider([
    'clientId'                => 'jt_qa',    // The client ID assigned to you by the provider
    'clientSecret'            => 'qa1234567890',   // The client password assigned to you by the provider
    // 'redirectUri'             => 'http://oauth-client.martyndev/',
    'urlAuthorize'            => 'http://brentertainment.com/oauth2/lockdin/authorize',
    'urlAccessToken'          => 'http://oauth.jt.martyndev/auth/access_token',
    'urlResourceOwnerDetails' => 'http://brentertainment.com/oauth2/lockdin/resource'
]);

try {

    // Try to get an access token using the authorization code grant.
    $accessToken = $provider->getAccessToken('client_credentials');

    // We have an access token, which we may use in authenticated
    // requests against the service provider's API.
    echo $accessToken->getToken() . "\n";
    echo $accessToken->getRefreshToken() . "\n";
    echo $accessToken->getExpires() . "\n";
    echo ($accessToken->hasExpired() ? 'expired' : 'not expired') . "\n";

} catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {

    // Failed to get the access token or user details.
    exit($e->getMessage());

}
