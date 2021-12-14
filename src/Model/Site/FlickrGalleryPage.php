<?php declare(strict_types = 1);

namespace Suilven\Flickr\Model\Site;

use Suilven\Flickr\Model\Flickr\FlickrGallery;

/**
 * Class \Suilven\Flickr\Model\Site\FlickrGalleryPage
 *
 * @property int $FlickrGalleryForPageID
 * @method \Suilven\Flickr\Model\Flickr\FlickrGallery FlickrGalleryForPage()
 */
class FlickrGalleryPage extends FlickrSetPage
{
    /** @var string */
    private static $table_name = 'FlickrGalleryPage';

    /** @var array<string,string> */
    private static $has_one = [
        'FlickrGalleryForPage' => FlickrGallery::class,
    ];
}
