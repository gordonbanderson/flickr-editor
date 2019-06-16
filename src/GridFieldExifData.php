<?php
namespace Suilven\Flickr;

use SilverStripe\View\ArrayData;
use SilverStripe\Forms\GridField\GridField_HTMLProvider;
/**
 * GridFieldExifData displays read only exif data for a Flickr photograph
 *
 * @see GridField
 *
 * @package weboftalent-flickr
 * @subpackage fields-relational
 */
class GridFieldExifData implements GridField_HTMLProvider {
	public function getHTMLFragments($gridField) {
		$forTemplate = new ArrayData(array());

		return array(
			'header' => $forTemplate->renderWith('GridFieldExifData'),
		);
	}
}

