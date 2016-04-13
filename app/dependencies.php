<?php

$container = $app->getContainer();

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new \Monolog\Logger($settings['name']);
    $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
    $logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['path'], \Monolog\Logger::DEBUG));
    return $logger;
};

// replace request with our own
$container['request'] = function ($c) {
    return \MartynBiz\Slim3Controller\Http\Request::createFromEnvironment($c->get('environment'));
};

// replace reponse with our own
$container['response'] = function ($c) {
    $headers = new \Slim\Http\Headers(['Content-Type' => 'text/html; charset=UTF-8']);
    $response = new \MartynBiz\Slim3Controller\Http\Response(200, $headers);
    return $response->withProtocolVersion($c->get('settings')['httpVersion']);
};

// A server which issues access tokens after successfully authenticating a client
// and resource owner, and authorizing the request.
$container['authorization_server'] = function ($c) {

    $server = new \League\OAuth2\Server\AuthorizationServer();

    // the oauth2-server we're using requires these objects for managing storage
    // of oauth items such as tokens
    $server->setSessionStorage(new \App\OAuth2\Storage\SessionStorage());
    $server->setAccessTokenStorage(new \App\OAuth2\Storage\AccessTokenStorage());
    $server->setRefreshTokenStorage(new \App\OAuth2\Storage\RefreshTokenStorage());
    $server->setClientStorage(new \App\OAuth2\Storage\ClientStorage());
    $server->setScopeStorage(new \App\OAuth2\Storage\ScopeStorage());
    $server->setAuthCodeStorage(new \App\OAuth2\Storage\AuthCodeStorage());

    // add a couple of grants for this server
    $server->addGrantType( new \App\OAuth2\Grant\RefreshTokenGrant() );
    $server->addGrantType( new \App\OAuth2\Grant\ClientCredentialsGrant() );

    return $server;
};

// A server which sits in front of protected resources (for example “tweets”,
// users’ photos, or personal data) and is capable of accepting and responsing
// to protected resource requests using access tokens.
$container['resource_server'] = function ($c) {

    // Set up the OAuth 2.0 resource server
    $sessionStorage = new \App\OAuth2\Storage\SessionStorage();
    $accessTokenStorage = new \App\OAuth2\Storage\AccessTokenStorage();
    $clientStorage = new \App\OAuth2\Storage\ClientStorage();
    $scopeStorage = new \App\OAuth2\Storage\ScopeStorage();

    $server = new \League\OAuth2\Server\ResourceServer(
        $sessionStorage,
        $accessTokenStorage,
        $clientStorage,
        $scopeStorage
    );

    return $server;
};
