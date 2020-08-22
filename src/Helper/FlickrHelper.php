<?php declare(strict_types = 1);

namespace Suilven\Flickr\Helper;

use OAuth\Common\Storage\Memory;
use OAuth\OAuth1\Token\StdOAuth1Token;
use Samwilson\PhpFlickr\PhotosApi;
use Samwilson\PhpFlickr\PhpFlickr;
use SilverStripe\Core\Environment;

class FlickrHelper
{
    public function getPhpFlickr(): PhpFlickr
    {
        $apiKey = Environment::getEnv('FLICKR_API_KEY');
        $apiSecret = Environment::getEnv('FLICKR_API_SECRET');
        $accessToken = Environment::getEnv('FLICKR_OAUTH_ACCESS_TOKEN');
        $accessTokenSecret = Environment::getEnv('FLICKR_OAUTH_ACCESS_SECRET');

        if (!isset($apiKey) || !isset($apiSecret) || !isset($accessToken) || !isset($accessTokenSecret)) {
            echo 'Please set $apiKey, $apiSecret, $accessToken, and $accessTokenSecret in .env';
            exit(1);
        }
        // Add your access token to the storage.
        $token = new StdOAuth1Token();
        $token->setAccessToken($accessToken);
        $token->setAccessTokenSecret($accessTokenSecret);
        $storage = new Memory();
        $storage->storeAccessToken('Flickr', $token);
        // Create PhpFlickr.
        $phpFlickr = new PhpFlickr($apiKey, $apiSecret);
        // Give PhpFlickr the storage containing the access token.
        $phpFlickr->setOauthStorage($storage);

        return $phpFlickr;
    }


    public function getPhotosHelper(): PhotosApi
    {
        $phpFlickr = $this->getPhpFlickr();

        return new PhotosApi($phpFlickr);
    }


    public function getPhotoSetsHelper(): PhotosApi
    {
        return $this->getPhotosHelper();
    }
}
