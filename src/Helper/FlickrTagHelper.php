<?php
namespace Suilven\Flickr\Helper;

use SilverStripe\ORM\ArrayList;
use Suilven\Flickr\Model\Flickr\FlickrTag;

class FlickrTagHelper extends FlickrHelper
{
    public function createOrFindTags($csv)
    {
        $result = new ArrayList();

        if (trim($csv) == '') {
            return $result; // ie empty array
        }

        $tags = explode(',', $csv);
        foreach ($tags as $tagName) {
            $tagName = trim($tagName);
            if (!$tagName) {
                continue;
            }

            // search for an existing tag, if there is not one create it
            $ftag = FlickrTag::get()->filter(['Value' => strtolower($tagName)])->first();
            if (!$ftag) {
                $ftag = FlickrTag::create();
                $ftag->RawValue = $tagName;
                $ftag->Value  = strtolower($tagName);
                $ftag->write();
            }

            $result->add($ftag);
        }

        return $result;
    }
}
