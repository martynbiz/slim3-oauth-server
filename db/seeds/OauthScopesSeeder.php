<?php

use Phinx\Seed\AbstractSeed;

class OauthScopesSeeder extends AbstractSeed
{
    public function run()
    {
        $data = array(
            array(
                'id'    => 'jt_getarticles',
                'description' => 'Get JapanTravel articles',
            ),
        );

        $oauth_clients = $this->table('oauth_scopes');
        $oauth_clients->insert($data)
              ->save();
    }
}
