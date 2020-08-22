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
    private static $table_name = 'FlickrGalleryPage';

    private static $has_one = [
        'FlickrGalleryForPage' => FlickrGallery::class,
    ];

    public function getFlickrImageCollectionForPage()
    {
        return $this->FlickrGalleryForPage();
    }
}
