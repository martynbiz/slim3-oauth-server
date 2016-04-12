<?php
namespace App\Middleware;

use League\OAuth2\Server\ResourceServer;

class CheckOAuthToken
{
    /**
     * @var League\OAuth2\Server\ResourceServer
     */
    protected $server;

    public function __construct(ResourceServer $server)
    {
        $this->server = $server;
    }

    /**
     * Example middleware invokable class
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($request, $response, $next)
    {
        try {

            // Check that an access token is present and is valid
            $this->server->isValidRequest();

            // pass onto the next callable
            $response = $next($request, $response);

        } catch (\League\OAuth2\Server\Exception\OAuthException $e) {

            $response->write(json_encode(array(
                'error'     =>  500,
                'message'   =>  $e->getMessage(),
            )));
            $response = $response->withStatus( 500 )->withHeader('Content-type', 'application/json');

        } catch (\Exception $e) {

            $response->write(json_encode(array(
                'error'     =>  500,
                'message'   =>  $e->getMessage(),
            )));
            $response = $response->withStatus( 500 )->withHeader('Content-type', 'application/json');

        }

        return $response;
    }
}
