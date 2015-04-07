<?php

class FlickrSetAdmin extends ModelAdmin {

  public static $managed_models = array(   //since 2.3.2
	  'FlickrSet'
   );

	static $url_segment = 'flickr_sets'; // will be linked as /admin/products
	static $menu_title = 'Flickr Sets';

	static $menu_icon = '/flickr/icons/album.png';


}
