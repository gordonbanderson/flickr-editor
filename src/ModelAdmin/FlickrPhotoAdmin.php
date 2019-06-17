<?php
namespace Suilven\Flickr\ModelAdmin;

use SilverStripe\Admin\ModelAdmin;

class FlickrPhotoAdmin extends ModelAdmin
{
    private static $managed_models = array(   //since 2.3.2
        'FlickrPhoto',
        'FlickrAuthor'
     );

    private static $url_segment = 'flickr_photos'; // will be linked as /admin/products
    private static $menu_title = 'Flickr Photos';

    private static $menu_icon = '/flickr/icons/photo.png';
}
