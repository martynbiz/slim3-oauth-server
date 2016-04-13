<?php
namespace App\Api\JT\Controller;

use App\Controller\BaseController;

/**
 * /api/* route is under the control of CheckOAuthToken middleware
 * Anything we wanna protect by valid access token can go in this controller
 */
class ArticlesController extends BaseController
{
    /**
     * This will be the currently logged in account, in oauth terms - the owner
     * GET /api/account
     */
    public function index()
    {
        try {

            // $server = $this->get('resource_server');
            // $accessToken = $server->getAccessToken();
            //
            // // // ensure that the access token has the required scope
            // // if (! $accessToken->hasScope('getaccount')) {
            // //     throw new \Exception('Access token does not have required scope to get account info');
            // // }
            //
            // // use the $ownerId to lookup the account
            // $session = $accessToken->getSession();
            // $ownerId = $session->getOwnerId();
            // $account = $this->get('model.account')->find($ownerId);

            $this->renderJson( array() );

        } catch(\Exception $e) {

            return $this->renderJson( array(
                'error' => 500,
                'message' => $e->getMessage(),
            ), 500 );

        }
    }
}
