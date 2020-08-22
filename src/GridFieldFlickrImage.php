<?php declare(strict_types = 1);

namespace Suilven\Flickr;

use SilverStripe\Forms\GridField\GridField_HTMLProvider;
use SilverStripe\View\ArrayData;

/**
 * GridFieldExifData displays read only exif data for a Flickr photograph
 *
 * @see GridField
 * @package weboftalent-flickr
 * @subpackage fields-relational
 */
class GridFieldFlickrImage implements GridField_HTMLProvider
{
    public function getHTMLFragments($gridField)
    {
        $forTemplate = new ArrayData([]);

        return [
            'header' => $forTemplate->renderWith('GridFieldFlickrImage'),
        ];
    }
}
