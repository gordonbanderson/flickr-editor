<?php declare(strict_types = 1);

namespace Suilven\Flickr\Helper;

use SilverStripe\ORM\ArrayList;
use Suilven\Flickr\Model\Flickr\FlickrTag;

class FlickrTagHelper extends FlickrHelper
{
    /**
     * @param ?string $csv tags in CVS format
     * @return \SilverStripe\ORM\ArrayList<\Suilven\Flickr\Model\Flickr\FlickrTag>
     * @throws \SilverStripe\ORM\ValidationException
     */
    public function createOrFindTags(?string $csv): ArrayList
    {
        $result = new ArrayList();

        if (is_null($csv) || \trim($csv) === '') {
            // ie empty array
            return $result;
        }

        $tags = \explode(',', $csv);
        foreach ($tags as $tagName) {
            $tagName = \trim($tagName);
            if (!isset($tagName)) {
                continue;
            }

            // search for an existing tag, if there is not one create it
            $ftag = FlickrTag::get()->filter(['Value' => \strtolower($tagName)])->first();
            if (!isset($ftag)) {
                $ftag = FlickrTag::create();
                $ftag->RawValue = $tagName;
                $ftag->Value = \strtolower($tagName);
                $ftag->write();
            }

            $result->add($ftag);
        }

        return $result;
    }
}
