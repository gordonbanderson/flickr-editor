<?php declare(strict_types = 1);

namespace Suilven\Flickr;

use SilverStripe\Forms\GridField\GridField_HTMLProvider;
use SilverStripe\View\ArrayData;

// @phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter

/**
 * GridFieldExifData displays read only exif data for a Flickr photograph
 *
 * @see GridField
 * @package weboftalent-flickr
 * @subpackage fields-relational
 */
class GridFieldFlickrImage implements GridField_HTMLProvider
{
    /** @return array<string,string> */
    public function getHTMLFragments(\SilverStripe\Forms\GridField\GridField $gridField): array
    {
        $forTemplate = new ArrayData([]);

        return [
            'header' => $forTemplate->renderWith('GridFieldFlickrImage'),
        ];
    }
}
