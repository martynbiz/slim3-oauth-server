<?php
// Routes

$container = $app->getContainer();

// oauth routes allow issuing of auth_code, access_token
$app->group('/oauth', function () use ($app) {

    $controller = new App\Controller\OAuthController($app);

    // this route will issue a short term auth_code that is passed with the redirect
    $app->get('/authorize', $controller('authorize'))->setName('oauth_authorize');

    // this route will issue an access_token that is retrieved via a curl request and
    // verified by the previously issued auth_code
    $app->post('/access_token', $controller('accessToken'))->setName('oauth_access_token');
});

// api is a collection of resources that are only accessible after an access token
// has been granted (otherwise middleware will kick them out :)
$app->group('/api', function () use ($app) {

    $app->group('/jt', function () use ($app) {

        $controller = new App\Api\JT\Controller\ArticlesController($app);

        $app->get('/articles', $controller('index'))->setName('api_jt_articles_index');
    }

})->add( new App\Middleware\CheckOAuthToken( $container['resource_server'] ) );
