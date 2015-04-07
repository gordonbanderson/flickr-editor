
<?php

class FlickrPhotoAdmin extends ModelAdmin {

  public static $managed_models = array(   //since 2.3.2
      'FlickrPhoto'
   );

  static $url_segment = 'flickr_photos'; // will be linked as /admin/products
  static $menu_title = 'Flickr Photos';

  static $menu_icon = '/flickr/icons/photo.png';

}
