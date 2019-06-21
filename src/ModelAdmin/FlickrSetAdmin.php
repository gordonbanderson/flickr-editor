<?php
namespace Suilven\Flickr\ModelAdmin;

use SilverStripe\Admin\ModelAdmin;
use Suilven\Flickr\Model\Flickr\FlickrSet;

class FlickrSetAdmin extends ModelAdmin
{
    private static $managed_models = [FlickrSet::class];

    private static $url_segment = 'flickr_sets'; // will be linked as /admin/products
    private static $menu_title = 'Flickr Sets';

    private static $menu_icon = 'weboftalent/flickr:icons/photo.png';
}
