<?php declare(strict_types = 1);

namespace Suilven\Flickr\ModelAdmin;

use SilverStripe\Admin\ModelAdmin;
use SilverStripe\ORM\DataObject;
use Suilven\Flickr\Model\Flickr\FlickrAuthor;
use Suilven\Flickr\Model\Flickr\FlickrPhoto;

/**
 * Class \Suilven\Flickr\ModelAdmin\FlickrPhotoAdmin
 */
class FlickrPhotoAdmin extends ModelAdmin
{
    /**
     * @var array<string>
     */
    private static $managed_models = [
        FlickrPhoto::class,
        FlickrAuthor::class,
     ];

    /** @var string  */
    private static $url_segment = 'flickr_photos';

    /** @var string  */
    private static $menu_title = 'Flickr Photos';

    // @todo Check if I moved this over to Suilven namespace
    /** @var string  */
    private static $menu_icon = 'weboftalent/flickr:icons/photo.png';
}
