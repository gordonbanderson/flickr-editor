<?php declare(strict_types = 1);

namespace Suilven\Flickr\Task;

use League\CLImate\CLImate;
use SilverStripe\Control\Director;
use SilverStripe\Core\Environment;
use SilverStripe\Dev\BuildTask;
use SilverStripe\Security\Permission;
use SilverStripe\Security\Security;

// @phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
// @phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint

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

    /** @var string */
    private static $segment = 'get-flickr-oauth';


    /**
     * @param \SilverStripe\Control\HTTPRequest $request
     * @return \SilverStripe\Control\HTTPResponse | void
     */
    public function run($request)
    {
        $climate = new CLImate();

        // check this script is being run by admin
        $canAccess = (Director::isDev() || Director::is_cli() || (bool) Permission::check("ADMIN"));
        if (!$canAccess) {
            return Security::permissionFailure();
        }

        $apiKey = Environment::getEnv('FLICKR_API_KEY');
        $apiSecret = Environment::getEnv('FLICKR_API_SECRET');

        if (!isset($apiKey) || !isset($apiSecret)) {
            \user_error('Please set FLICKR_API_KEY and FLICKR_API_SECRET in your .env file');
        }

        $flickr = new \Samwilson\PhpFlickr\PhpFlickr($apiKey, $apiSecret);

        $storage = new \OAuth\Common\Storage\Memory();
        $flickr->setOauthStorage($storage);
        $url = $flickr->getAuthUrl('delete');
        echo "Go to $url\nEnter access code: ";
        $code = (string) \fgets(\STDIN);
        $verifier = (string) \preg_replace('/[^0-9]/', '', $code);

        $accessToken = $flickr->retrieveAccessToken($verifier);

        if ($accessToken === '' || !($accessToken instanceof \OAuth\Common\Token\TokenInterface)) {
            $climate->error(('No access token returned by Flickr'));

            return;
        }

        $climate->backgroundRed('Keep these parameters safe');
        $climate->info('$accessToken = "' . $accessToken->getAccessToken() . '";');

        // @TODO the getAccessTokenSecret method does not exist.  As such is this entire task obsolete?
        // @phpstan-ignore-next-line
        $climate->info('$accessTokenSecret = "' . $accessToken->getAccessTokenSecret() . '";');
        /*
         * Any methods can now be called.
         */
        $login = $flickr->test()->login();
        $climate->info("You are authenticated as: {$login['username']}\n");
    }
}
