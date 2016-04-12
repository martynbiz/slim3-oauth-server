<?php
namespace App\Controller;

use MartynBiz\Slim3Controller\Controller;
use App\Exception\InvalidReturnToUrl;
use App\Model\Account;

abstract class BaseController extends Controller
{
    /**
     * @var User
     */
    protected $currentUser;

    /**
     * @var string
     */
    protected $language;

    /**
     * Get the lang from the "language" cookie
     */
    protected function getLanguage()
    {
        // check if language has been set, otherwise get it from the cookie
        if (is_null($this->language)) {
            $request = $this->app->getRequest();
            $container = $this->app->getContainer();
            $settings = $container->get('settings')['i18n'];

            $this->language = $request->getCookie('language', $settings['default_locale']);
        }

        return $this->language;
    }

    /**
     * Render the view from within the controller
     * @param string $file Name of the template/ view to render
     * @param array $args Additional varables to pass to the view
     * @param Response?
     */
    protected function render($file, $args=array(), $status=200)
    {
        $settings = $this->get('settings');

        // so we can view the attributes when we log in (useful for debugging)
        $attributes = $this->get('auth')->getAttributes();
        $args['attributes'] = $attributes;

        // set the current user
        if($this->get('auth')->isAuthenticated()) {
            $args['current_user'] = $this->get('model.account')->where('email', $attributes['email'])->first();
        }

        // flash messages are us to show error messages and success
        $args['flash_message'] = $this->get('flash')->flushMessages();

        // // flash messages are us to show error messages and success
        // $args['translator'] = $this->get('i18n');

        return $this->get('view')->render($this->response, $file, $args);
    }

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

    /**
     * Will ensure that returnTo url is valid before doing redirect. Otherwise mean
     * people could use out login then redirect to a phishing site
     * @param string $returnTo The returnTo url that we want to check against our white list
     */
    protected function returnTo($returnTo)
    {
        $container = $this->app->getContainer();
        $settings = $container->get('settings');

        // check returnTo
        $host = parse_url($returnTo, PHP_URL_HOST);
        $found = false;
        $validReturnToArray = (is_array($settings['valid_return_to'])) ? $settings['valid_return_to'] : array($settings['valid_return_to']);
        foreach($validReturnToArray as $validReturnTo) {
            if ($host and preg_match($validReturnTo, $host)) {
                $found = true;
            }
        }
        if (! $found) {
            throw new InvalidReturnToUrl( $this->get('i18n')->translate('invalid_return_to') );
        }

        return parent::redirect($returnTo);
    }

    /**
     * Get the current sign in user account
     */
    protected function getSessionAccount()
    {
        $attributes = $this->get('auth')->getAttributes();
        return $this->get('model.account')->findByEmail($attributes['email']);
    }
}
