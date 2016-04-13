<?php
session_start();

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__)));

require APPLICATION_PATH . '/vendor/autoload.php';




use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;

class JTProvider extends \League\OAuth2\Client\Provider\AbstractProvider
{
    use BearerAuthorizationTrait;

    /**
     * @var string HTTP method used to fetch access tokens.
     */
    const METHOD_PUT = 'PUT';

    /**
     * @var string HTTP method used to fetch access tokens.
     */
    const METHOD_DELETE = 'DELETE';

    /**
     * @var string
     */
    protected $urlAccessToken;

    /**
     * @var string
     */
    protected $urlApi;

    /**
     * @var string
     */
    private $responseError = 'error';

    /**
     * This will generate
     */
    public function getApiUrl($path='', $params=array())
    {
        $url = $this->urlApi . $path;

        // if params are given, attach those too
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }

    /**
     * @inheritdoc
     */
    public function getBaseAuthorizationUrl()
    {
        return $this->urlAuthorize;
    }

    /**
     * @inheritdoc
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return $this->urlAccessToken;
    }

    /**
     * @inheritdoc
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return $this->urlResourceOwnerDetails;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultScopes()
    {
        return $this->scopes;
    }

    /**
     * @inheritdoc
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (!empty($data[$this->responseError])) {
            $error = $data[$this->responseError];
            $code  = $this->responseCode ? $data[$this->responseCode] : 0;
            throw new IdentityProviderException($error, $code, $data);
        }
    }

    /**
     * @inheritdoc
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new GenericResourceOwner($response, $this->responseResourceOwnerId);
    }

    /**
     * Requests and returns the resource owner of given access token.
     *
     * @param  AccessToken $token
     * @return ResourceOwnerInterface
     */
    public function getArticles(AccessToken $token, $query=array())
    {
        // set default options
        $query = array_merge(array(
            'query' => array(),
            'start' => 0,
            'limit' => 20,
        ), $query);

        // we use the same url for get, update, delete; just the method is different
        $url = $this->getApiUrl('/articles', $query);

        $options = array(
            'headers' => ['content-type' => 'application/x-www-form-urlencoded'],
            // 'body' => http_build_query(array(
            //     'query' => $query,
            //     'start' => $options['start'],
            //     'limit' => $options['limit'],
            // )),
        );

        // build request/ get response
        $request = $this->getAuthenticatedRequest(self::METHOD_GET, $url, $token, $options);

        $response = $this->sendRequest($request);
        $parsed = $this->parseResponse($response);

        // checks for 'error' name in array response
        $this->checkResponse($response, $parsed);

        return $parsed; //$this->createResourceOwner($parsed, $token);
    }

    /**
     * Requests and returns the resource owner of given access token.
     *
     * @param  AccessToken $token
     * @return ResourceOwnerInterface
     */
    public function createArticle(AccessToken $token, $values)
    {
        // $url = $this->getApiUrl('/articles');
        // $request = $this->getAuthenticatedRequest(self::METHOD_POST, $url, $token);
        // $response = $this->getResponse($request);
        //
        // // return $this->createResourceOwner($response, $token);
    }

    /**
     * Requests and returns the resource owner of given access token.
     *
     * @param  AccessToken $token
     * @return ResourceOwnerInterface
     */
    public function updateArticle(AccessToken $token, $id, $values)
    {
        // $url = $this->getApiUrl('/articles');
        // $request = $this->getAuthenticatedRequest(self::METHOD_POST, $url, $token);
        // $response = $this->getResponse($request);
        //
        // // return $this->createResourceOwner($response, $token);
    }

    /**
     * Requests and returns the resource owner of given access token.
     *
     * @param  AccessToken $token
     * @return ResourceOwnerInterface
     */
    public function deleteArticle(AccessToken $token, $id)
    {
        // $url = $this->getApiUrl('/articles');
        // $request = $this->getAuthenticatedRequest(self::METHOD_POST, $url, $token);
        // $response = $this->getResponse($request);
        //
        // // return $this->createResourceOwner($response, $token);
    }
}


$provider = new JTProvider([
    'clientId'                => 'jt_qa',    // The client ID assigned to you by the provider
    'clientSecret'            => 'qa1234567890',   // The client password assigned to you by the provider
    // 'redirectUri'             => 'http://oauth-client.martyndev/',
    // 'urlAuthorize'            => 'http://brentertainment.com/oauth2/lockdin/authorize',
    'urlAccessToken'          => 'http://oauth.jt.martyndev/auth/access_token',
    // 'urlResourceOwnerDetails' => 'http://brentertainment.com/oauth2/lockdin/resource'

    'urlApi'            => 'http://oauth.jt.martyndev/api/jt',
]);

try {

    // Try to get an access token using the authorization code grant.
    $accessToken = $provider->getAccessToken('client_credentials');

    // TODO where to store access_token? db? cache?

    // get the jt stuff
    $articles = $provider->getArticles($accessToken, array(
        'query' => array(
            'search' => 'restaurants and bars',
            'author' => 'martyn.bissett',
            'prefecture' => 'tokyo',
            'category' => 'bars',
            'subcategory' => 'culture',
            'sponsored' => true,
        ),
        'options' => array(
            'limit' => 30,
            'start' => 1,
        ),
    ));

    var_dump($articles); exit;

    // // Using the access token, we may look up details about the
    // // resource owner.
    // $resourceOwner = $provider->getResourceOwner($accessToken);
    //
    // var_export($resourceOwner->toArray());
    //
    // // The provider provides a way to get an authenticated API request for
    // // the service, using the access token; it returns an object conforming
    // // to Psr\Http\Message\RequestInterface.
    // $request = $provider->getAuthenticatedRequest(
    //     'GET',
    //     'http://brentertainment.com/oauth2/lockdin/resource',
    //     $accessToken
    // );

} catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {

    // Failed to get the access token or user details.
    exit($e->getMessage());

}
