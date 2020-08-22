<?php declare(strict_types = 1);

/**
 * Created by PhpStorm.
 * User: gordon
 * Date: 11/4/2561
 * Time: 16:22 น.
 */

namespace Suilven\Flickr\Task;

use SilverStripe\Core\Environment;
use SilverStripe\Dev\BuildTask;

class GetOAuthToken extends BuildTask
{

    protected $title = 'Get Flickr oAuth tokens';

    protected $description = 'Given the existence of API key and secret, get the oauth tokens';

    protected $enabled = true;

    private static $segment = 'get-flickr-oauth';


    public function run($request): void
    {
        $apiKey = Environment::getEnv('FLICKR_API_KEY');
        $apiSecret = Environment::getEnv('FLICKR_API_SECRET');

        if (empty($apiKey) || empty($apiSecret)) {
            \user_error('Please set FLICKR_API_KEY and FLICKR_API_SECRET in your .env file');
        }


        $flickr = new \Samwilson\PhpFlickr\PhpFlickr($apiKey, $apiSecret);
        if (isset($_SERVER['SERVER_NAME'])) {
            /*
             * The web-browser workflow.
             */
            $storage = new \OAuth\Common\Storage\Session();
            $flickr->setOauthStorage($storage);
            if (!isset($_GET['oauth_token'])) {
                $callbackHere = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
                $url = $flickr->getAuthUrl('delete', $callbackHere);
                echo "<a href='$url'>$url</a>";
            }
            if (isset($_GET['oauth_token'])) {
                $accessToken = $flickr->retrieveAccessToken($_GET['oauth_verifier'], $_GET['oauth_token']);
            }
        } else {
            /*
             * The CLI workflow.
             */
            \error_log('CLI WORKFLOW');
            $storage = new \OAuth\Common\Storage\Memory();
            $flickr->setOauthStorage($storage);
            $url = $flickr->getAuthUrl('delete');
            echo "Go to $url\nEnter access code: ";
            $code = \fgets(\STDIN);
            $verifier = \preg_replace('/[^0-9]/', '', $code);
            $accessToken = $flickr->retrieveAccessToken($verifier);
        }
        if (!isset($accessToken) || !($accessToken instanceof \OAuth\Common\Token\TokenInterface)) {
            return;
        }

        /*
         * You should save the access token and its secret somewhere safe.
         */
        echo '$accessToken = "'.$accessToken->getAccessToken().'";'.\PHP_EOL;
        echo '$accessTokenSecret = "'.$accessToken->getAccessTokenSecret().'";'.\PHP_EOL;
        /*
         * Any methods can now be called.
         */
        $login = $flickr->test()->login();
        echo "You are authenticated as: {$login['username']}\n";
    }
}
