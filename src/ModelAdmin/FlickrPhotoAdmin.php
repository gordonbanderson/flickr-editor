<?php
namespace Suilven\Flickr\ModelAdmin;

use SilverStripe\Admin\ModelAdmin;
use Suilven\Flickr\Model\Flickr\FlickrAuthor;
use Suilven\Flickr\Model\Flickr\FlickrPhoto;

/**
 * Class \Suilven\Flickr\ModelAdmin\FlickrPhotoAdmin
 *
 */
class FlickrPhotoAdmin extends ModelAdmin
{
    private static $managed_models = [
        FlickrPhoto::class,
        FlickrAuthor::class
     ];

    private static $url_segment = 'flickr_photos'; // will be linked as /admin/products
    private static $menu_title = 'Flickr Photos';

    private static $menu_icon = 'weboftalent/flickr:icons/photo.png';
}
