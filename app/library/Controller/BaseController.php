<?php
namespace App\Controller;

use MartynBiz\Slim3Controller\Controller;

abstract class BaseController extends Controller
{
    /**
     * Render data as a json response
     * @param string $data
     * @param string $status
     */
    protected function renderJson($data, $status=200)
    {
        $this->get('response')->write(json_encode($data));
        return $this->get('response')->withStatus( $status )->withHeader('Content-type', 'application/json');
    }
}
