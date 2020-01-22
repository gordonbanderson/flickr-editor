<?php
namespace Suilven\Flickr\ShortCode;

use SilverStripe\ORM\DataList;
use SilverStripe\Core\Convert;
use SilverStripe\View\SSViewer;
use SilverStripe\View\ArrayData;
use Suilven\Flickr\Model\Flickr\FlickrPhoto;

class FlickrPhotoShortCodeHandler
{
    /**
     * 
     * @param unknown $arguments
     * @param string $caption
     * @param unknown $parser
     * @return void|string|\SilverStripe\ORM\FieldType\DBHTMLText
     */
    public static function parse_flickr($arguments, $caption = null, $parser = null)
    {

        if (empty($arguments['id'])) {
            return;
        }

        $customise = array();

        /*** SET DEFAULTS ***/
        $fp = DataList::create(FlickrPhoto::class)->filter(['FlickrID' => $arguments['id']])->first();


        if (!$fp) {
            return '';
        }

        $customise['FlickrImage'] = $fp;
        //set the caption


        if (($caption === null) || ($caption === '')) {
            if (isset($arguments['caption'])) {
                $caption = $arguments['caption'];
            }
        }


        $customise['Caption'] = $caption ? Convert::raw2xml($caption) : $fp->Title ;
        $customise['Position'] = !empty($arguments['position']) ? $arguments['position'] : 'center';
        $customise['HideExif'] = !empty($arguments['exif']) ? $arguments['exif'] : false;
        $customise['Small'] = true;
        if ($customise['Position'] == 'center') {
            $customise['Small'] = false;
        }

        $fp = null;

        //overide the defaults with the arguments supplied
        $customise = array_merge($customise, $arguments);

        //get our YouTube template
        $template = new SSViewer('Includes/ShortCodeFlickrPhoto');

        //return the customised template
        return $template->process(new ArrayData($customise));
    }
}
