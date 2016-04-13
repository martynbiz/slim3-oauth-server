<?php

use Phinx\Seed\AbstractSeed;

class OauthClientsSeeder extends AbstractSeed
{
    public function run()
    {
        $data = array(
            array(
                'id'    => 'jt_qa',
                'secret' => 'qa1234567890',
                'name' => 'JapanTravel Questions',
            ),
        );

        $oauth_clients = $this->table('oauth_clients');
        $oauth_clients->insert($data)
              ->save();
    }
}
