<?php
/**
 * Custom class to allow us to override certain methods (e.g. redirect handling)
 * We want to be able to return to any *.japantravel.com URL
 * e.g. https://th.japantravel.com/login?returnTo=http...
 * @author      Martyn Bissett <martyn@metroworks.co.jp>
 */

namespace App\OAuth2\Grant;

use League\OAuth2\Server\Entity\AccessTokenEntity;
use League\OAuth2\Server\Entity\AuthCodeEntity;
use League\OAuth2\Server\Entity\ClientEntity;
use League\OAuth2\Server\Entity\RefreshTokenEntity;
// use League\OAuth2\Server\Entity\SessionEntity;
use League\OAuth2\Server\Event;
use League\OAuth2\Server\Exception;
use League\OAuth2\Server\Util\SecureKey;

/**
 * Auth code grant class
 */
class AuthCodeGrant extends \League\OAuth2\Server\Grant\AuthCodeGrant
{
    /**
     * Complete the auth code grant
     *
     * @return array
     *
     * @throws
     */
    public function completeFlow()
    {
        // Get the required params
        $clientId = $this->server->getRequest()->request->get('client_id', $this->server->getRequest()->getUser());
        if (is_null($clientId)) {
            throw new Exception\InvalidRequestException('client_id');
        }

        $clientSecret = $this->server->getRequest()->request->get('client_secret',
            $this->server->getRequest()->getPassword());
        if ($this->shouldRequireClientSecret() && is_null($clientSecret)) {
            throw new Exception\InvalidRequestException('client_secret');
        }

        $redirectUri = $this->server->getRequest()->request->get('redirect_uri', null);
        if (is_null($redirectUri)) {
            throw new Exception\InvalidRequestException('redirect_uri');
        }

        // Validate client ID and client secret
        $clientStorage = $this->server->getClientStorage();
        $client = $clientStorage->get(
            $clientId,
            $clientSecret,
            $redirectUri,
            $this->getIdentifier()
        );

        if (($client instanceof ClientEntity) === false) {
            $this->server->getEventEmitter()->emit(new Event\ClientAuthenticationFailedEvent($this->server->getRequest()));
            throw new Exception\InvalidClientException();
        }

        // Validate the auth code
        $authCode = $this->server->getRequest()->request->get('code', null);

        if (is_null($authCode)) {
            throw new Exception\InvalidRequestException('code');
        }

        $code = $this->server->getAuthCodeStorage()->get($authCode);
        if (($code instanceof AuthCodeEntity) === false) {
            throw new Exception\InvalidRequestException('code');
        }

        // Ensure the auth code hasn't expired
        if ($code->isExpired() === true) {
            throw new Exception\InvalidRequestException('code');
        }

        // This line here we'll change. It should compare the redirect by it's host
        // without the subdomain either (as in some apps, eg. jt, we'll have many languages etc)
        // Check redirect URI presented matches redirect URI originally used in authorize request
        $redirectUriFromRequest = $clientStorage->getRedirectUriDomain($redirectUri);
        $redirectUriFromCode = $clientStorage->getRedirectUriDomain($code->getRedirectUri());
        if ($redirectUriFromRequest !== $redirectUriFromCode) {
            throw new Exception\InvalidRequestException('redirect_uri');
        }

        $session = $code->getSession();
        $session->associateClient($client);

        $authCodeScopes = $code->getScopes();

        // Generate the access token
        $accessToken = new AccessTokenEntity($this->server);
        $accessToken->setId(SecureKey::generate());
        $accessToken->setExpireTime($this->getAccessTokenTTL() + time());

        foreach ($authCodeScopes as $authCodeScope) {
            $session->associateScope($authCodeScope);
        }

        foreach ($session->getScopes() as $scope) {
            $accessToken->associateScope($scope);
        }

        $this->server->getTokenType()->setSession($session);
        $this->server->getTokenType()->setParam('access_token', $accessToken->getId());
        $this->server->getTokenType()->setParam('expires_in', $this->getAccessTokenTTL());

        // Associate a refresh token if set
        if ($this->server->hasGrantType('refresh_token')) {
            $refreshToken = new RefreshTokenEntity($this->server);
            $refreshToken->setId(SecureKey::generate());
            $refreshToken->setExpireTime($this->server->getGrantType('refresh_token')->getRefreshTokenTTL() + time());
            $this->server->getTokenType()->setParam('refresh_token', $refreshToken->getId());
        }

        // Expire the auth code
        $code->expire();

        // Save all the things
        $accessToken->setSession($session);
        $accessToken->save();

        if (isset($refreshToken) && $this->server->hasGrantType('refresh_token')) {
            $refreshToken->setAccessToken($accessToken);
            $refreshToken->save();
        }

        return $this->server->getTokenType()->generateResponse();
    }
}
