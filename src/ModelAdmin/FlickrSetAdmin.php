<?php declare(strict_types = 1);

namespace Suilven\Flickr\ModelAdmin;

use SilverStripe\Admin\ModelAdmin;
use Suilven\Flickr\Model\Flickr\FlickrSet;

/**
 * Class \Suilven\Flickr\ModelAdmin\FlickrSetAdmin
 */
class FlickrSetAdmin extends ModelAdmin
{
    /** @var string[]  */
    private static $managed_models = [FlickrSet::class];

    /** @var string  */
    private static $url_segment = 'flickr_sets';

    /** @var string  */
    private static $menu_title = 'Flickr Sets';

    /** @var string  */
    private static $menu_icon = 'weboftalent/flickr:icons/photo.png';
}
