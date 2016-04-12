<?php
namespace App\Controller;

class OAuthController extends BaseController
{
    /**
     * Will grant auth_code to then request access token
     * GET /oauth/authorize
     */
    public function authorize()
    {
        $server = $this->get('authorization_server');
        $params = $this->getQueryParams();

        // First ensure the parameters in the query string are correct
        try {

            $authParams = $server->getGrantType('authorization_code')->checkAuthorizeParams();

        } catch (\Exception $e) {

            return $this->renderJson( array(
                'error'     =>  $e->errorType,
                'message'   =>  $e->getMessage(),
            ), $e->httpStatusCode);
        }

        // Normally at this point you would show the user a sign-in screen and ask
        // them to authorize the requested scopes. In this system, if they are not
        // logged in then redirect to the login screen.
        if ($this->get('auth')->isAuthenticated()) {

            // get account
            $attributes = $this->get('auth')->getAttributes();

        } else {

            // redirect to the login page
            isset($params['returnTo']) or $params['returnTo'] = '/session';
            return $this->redirect('/session?' . http_build_query(array(
                'returnTo' => $params['redirect_uri'],
            )));
        }

        // Create a new authorize request which will respond with a redirect URI that the user will be redirected to

        // here we wanna pass the owner type and id.
        // TODO does redirect uri also contain returnTo?
        $redirectUri = $server->getGrantType('authorization_code')->newAuthorizeRequest('user', $attributes['id'], $authParams);

        return $this->redirect($redirectUri);
    }

    /**
     * Will grant access token to then use APIs (e.g. accounts)
     * GET /oauth/authorize
     */
    public function accessToken()
    {
        $server = $this->get('authorization_server');

        try {

            // invalid_client?
            $response = $server->issueAccessToken();

            $this->get('response')->write( json_encode($response) );
            return $this->get('response')->withStatus( 200 );

        } catch (\Exception $e) {

            return $this->renderJson( array(
                'error'     =>  $e->errorType,
                'message'   =>  $e->getMessage(),
            ), 500 ); // TODO $e->getHttpHeaders()

        }
    }
}
