<?php declare(strict_types = 1);

namespace Suilven\Flickr;

use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridField_HTMLProvider;
use SilverStripe\View\ArrayData;

// @phpcs:disable

/**
 * GridFieldExifData displays read only exif data for a Flickr photograph
 *
 * @see GridField
 * @package weboftalent-flickr
 * @subpackage fields-relational
 */
class GridFieldFlickrImage implements GridField_HTMLProvider
{
    /**
     * Returns a map where the keys are fragment names and the values are
     * pieces of HTML to add to these fragments.
     *
     * Here are 4 built-in fragments: 'header', 'footer', 'before', and
     * 'after', but components may also specify fragments of their own.
     *
     * To specify a new fragment, specify a new fragment by including the
     * text "$DefineFragment(fragmentname)" in the HTML that you return.
     *
     * Fragment names should only contain alphanumerics, -, and _.
     *
     * If you attempt to return HTML for a fragment that doesn't exist, an
     * exception will be thrown when the {@link GridField} is rendered.
     *
     * @param GridField $gridField
     * @return array
     */
    public function getHTMLFragments($gridField)
    {
        $forTemplate = new ArrayData([]);

        return [
            'header' => $forTemplate->renderWith('GridFieldFlickrImage'),
        ];
    }
}
