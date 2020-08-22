<?php declare(strict_types = 1);

namespace Suilven\Flickr\Task;

use SilverStripe\Control\Director;
use SilverStripe\Core\Environment;
use SilverStripe\Dev\BuildTask;
use SilverStripe\Security\Permission;

// @phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter

/**
 * Class GetOAuthToken
 *
 * @package Suilven\Flickr\Task
 */
class GetOAuthToken extends BuildTask
{

    protected $title = 'Get Flickr oAuth tokens';

    protected $description = 'Given the existence of API key and secret, get the oauth tokens';

    protected $enabled = true;

    private static $segment = 'get-flickr-oauth';


    /** @inheritdoc */
    public function run($request): void
    {
        // check this script is being run by admin
        $canAccess = (Director::isDev() || Director::is_cli() || Permission::check("ADMIN"));
        if (!$canAccess) {
            // @TODO fix return value above
            \error_log('Access denied');
            die;
        }

        $apiKey = Environment::getEnv('FLICKR_API_KEY');
        $apiSecret = Environment::getEnv('FLICKR_API_SECRET');

        if (!isset($apiKey) || !isset($apiSecret)) {
            \user_error('Please set FLICKR_API_KEY and FLICKR_API_SECRET in your .env file');
        }

        $flickr = new \Samwilson\PhpFlickr\PhpFlickr($apiKey, $apiSecret);

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

        if (!isset($accessToken) || !($accessToken instanceof \OAuth\Common\Token\TokenInterface)) {
            return;
        }

        /*
         * You should save the access token and its secret somewhere safe.
         */
        echo '$accessToken = "' . $accessToken->getAccessToken() . '";' . \PHP_EOL;
        echo '$accessTokenSecret = "' . $accessToken->getAccessTokenSecret() . '";' . \PHP_EOL;
        /*
         * Any methods can now be called.
         */
        $login = $flickr->test()->login();
        echo "You are authenticated as: {$login['username']}\n";
    }
}
