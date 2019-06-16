<?php
namespace Suilven\Flickr;

use SilverStripe\Admin\ModelAdmin;

class FlickrSetAdmin extends ModelAdmin {

    private static $managed_models = array(   //since 2.3.2
		'FlickrSet'
	);

    private static $url_segment = 'flickr_sets'; // will be linked as /admin/products
    private static $menu_title = 'Flickr Sets';

    private static $menu_icon = '/flickr/icons/album.png';


}
