<?php declare(strict_types = 1);

namespace Suilven\Flickr\ModelAdmin;

use SilverStripe\Admin\ModelAdmin;
use Suilven\Flickr\Model\Flickr\FlickrSet;

/**
 * Class \Suilven\Flickr\ModelAdmin\FlickrSetAdmin
 */
class FlickrSetAdmin extends ModelAdmin
{
    private static $managed_models = [FlickrSet::class];

    // will be linked as /admin/products
    private static $url_segment = 'flickr_sets';
    private static $menu_title = 'Flickr Sets';

    private static $menu_icon = 'weboftalent/flickr:icons/photo.png';
}
