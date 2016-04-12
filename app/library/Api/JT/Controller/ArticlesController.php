<?php
namespace App\Controller;

use App\Validator;

/**
 * /api/* route is under the control of CheckOAuthToken middleware
 * Anything we wanna protect by valid access token can go in this controller
 */
class ApiController extends BaseController
{
    /**
     * This will be the currently logged in account, in oauth terms - the owner
     * GET /api/account
     */
    public function getAccount()
    {
        try {

            $server = $this->get('resource_server');
            $accessToken = $server->getAccessToken();

            // // ensure that the access token has the required scope
            // if (! $accessToken->hasScope('getaccount')) {
            //     throw new \Exception('Access token does not have required scope to get account info');
            // }

            // use the $ownerId to lookup the account
            $session = $accessToken->getSession();
            $ownerId = $session->getOwnerId();
            $account = $this->get('model.account')->find($ownerId);

            $this->renderJson( $account->toArray() );

        } catch(\Exception $e) {

            return $this->renderJson( array(
                'error' => $e->getMessage(),
            ), 500 );

        }
    }

    /**
     * This will be the currently logged in account, in oauth terms - the owner
     * PUT /api/account/1
     * PUT /api/account/martyn.bissett
     */
    public function updateAccount()
    {
        try {

            $server = $this->get('resource_server');
            $accessToken = $server->getAccessToken();

            // // ensure that the access token has the required scope
            // if (! $accessToken->hasScope('updateaccount')) {
            //     throw new \Exception('Access token does not have required scope to update');
            // }

            // use the $ownerId to lookup the account
            $session = $accessToken->getSession();
            $ownerId = $session->getOwnerId();
            $account = $this->get('model.account')->find($ownerId);

            // validate form data

            // our simple custom validator for the form
            $validator = new Validator($this->getPost());
            $i18n = $this->get('i18n');

            // first_name
            if ($validator->has('first_name')) {
                $validator->check('first_name')
                    ->isNotEmpty( $i18n->translate('first_name_missing') );
            }

            // last_name
            if ($validator->has('last_name')) {
                $validator->check('last_name')
                    ->isNotEmpty( $i18n->translate('last_name_missing') );
            }

            // email
            if ($validator->has('email')) {
                $validator->check('email')
                    ->isNotEmpty( $i18n->translate('email_missing') )
                    ->isEmail( $i18n->translate('email_invalid') )
                    ->isUpdateUniqueEmail( $account->email, $i18n->translate('email_not_unique'), $this->get('model.account') );
            }

            // password
            if ($validator->has('password')) {
                $message = $i18n->translate('password_must_contain');
                $validator->check('password')
                    ->isNotEmpty($message)
                    ->hasLowerCase($message)
                    ->hasUpperCase($message)
                    ->isMinimumLength($message, 8);
            }

            // if valid, return account info
            if ($validator->isValid() and $account->fill( $this->getPost() )->save()) {

                // return success or fail
                return $this->renderJson( $account->toArray() );

            } elseif(! $validator->isValid()) {
                throw new \Exception('Validation error when updating resource owner'); // $validator->getErrors()
            } else {
                throw new \Exception('Database error when updating resource owner'); // $account->errors()
            }

        } catch(\Exception $e) {

            return $this->renderJson( array(
                'error' => $e->getMessage(),
            ), 500 );

        }
    }
}
