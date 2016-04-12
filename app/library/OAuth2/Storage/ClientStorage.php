<?php

namespace App\OAuth2\Storage;

use Illuminate\Database\Capsule\Manager as Capsule;
use League\OAuth2\Server\Entity\ClientEntity;
use League\OAuth2\Server\Entity\SessionEntity;
use League\OAuth2\Server\Storage\AbstractStorage;
use League\OAuth2\Server\Storage\ClientInterface;

class ClientStorage extends AbstractStorage implements ClientInterface
{
    /**
     * {@inheritdoc}
     */
    public function get($clientId, $clientSecret = null, $redirectUri = null, $grantType = null)
    {
        $query = Capsule::table('oauth_clients')
                          ->select('oauth_clients.*')
                          ->where('oauth_clients.id', $clientId);

        if ($clientSecret !== null) {
            $query->where('oauth_clients.secret', $clientSecret);
        }

        if ($redirectUri) {

            // this will ensure that we can still include /paths and ?query_strings in our $redirect_uri but
            // it must match exactly in the db so we'll strip here
            $redirectParsed = $this->getRedirectUriDomain($redirectUri);

            $query->join('oauth_client_redirect_uris', 'oauth_clients.id', '=', 'oauth_client_redirect_uris.client_id')
                  ->select(['oauth_clients.*', 'oauth_client_redirect_uris.*'])
                  ->where('oauth_client_redirect_uris.redirect_uri', $redirectParsed);
        }

        $result = $query->get();

        // TODO not really sure what the deal is here. it hops between id and client_id
        // in the db it's "id" but during an auth request it changes to client_id - check that
        // out some other time. for now, just choose
        $clientId = isset($result[0]->client_id) ? $result[0]->client_id : (isset($result[0]->id) ? $result[0]->id : null);

        if (count($result) === 1) {
            $client = new ClientEntity($this->server);
            $client->hydrate([
                'id'    =>  $clientId, // TODO "id" should be a primary key, if possible
                'name'  =>  $result[0]->name,
            ]);

            return $client;
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function getBySession(SessionEntity $session)
    {
        $result = Capsule::table('oauth_clients')
                            ->select(['oauth_clients.id', 'oauth_clients.name'])
                            ->join('oauth_sessions', 'oauth_clients.id', '=', 'oauth_sessions.client_id')
                            ->where('oauth_sessions.id', $session->getId())
                            ->get();

        if (count($result) === 1) {
            $client = new ClientEntity($this->server);
            $client->hydrate([
                'id'    =>  $result[0]->id, //['id'],
                'name'  =>  $result[0]->name, //['name'],
            ]);

            return $client;
        }

        return;
    }

    /**
     * This will get only the domain (japantravel.com) from a
     * redirect uri (https://th.japantravel.com/login?returnTo=http...) so that we
     * can compare it with our database client redirect uri and not have to enter
     * every single language (en, th, fr, etc) and other (admin, api, etc) subdomain manually
     * TODO needs tested
     * @param string $redirectUri
     * @return boolean
     */
    public function getRedirectUriDomain($redirectUri)
    {
        // this will ensure that we can still include /paths and ?query_strings in our $redirect_uri but
        // it must match exactly in the db so we'll strip here
        $redirectParsed = parse_url($redirectUri, PHP_URL_HOST); // eg. en.japantravel.com

        // rather than having to set every sub-domain in the database too, we'll just strip the subdomain
        // from the url. as we have all these under our control then this ought to be safe to do so
        // so en.japantravel.com -> japantravel.com
        $redirectParsed = preg_replace("/[a-zA-Z0-9_-]*\./", "", $redirectParsed, 1);

        return $redirectParsed;
    }
}
