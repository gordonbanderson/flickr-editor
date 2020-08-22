<?php declare(strict_types = 1);

namespace Suilven\Flickr\Helper;

use Suilven\Flickr\Model\Flickr\FlickrTag;

class FlickrBatchHelper extends FlickrHelper
{
    /**
     * @param \Suilven\Flickr\Model\Flickr\FlickrSet $flickrSet
     * @param array<string> $batchTags
     * @return int the number of photos updated
     * @throws \SilverStripe\ORM\ValidationException
     */
    public function batchUpdateSet(
        FlickrSet $flickrSet,
        string $batchTitle,
        string $batchDescription,
        array $batchTags
    ): int {
        //FIXME authentication


        $flickrPhotos = $flickrSet->FlickrPhotos();

        // $batchDescription = $batchDescription ."\n\n".$flickrSet->ImageFooter;
        // $batchDescription = $batchDescription ."\n\n".$this->SiteConfig()->ImageFooter;

        $tags = [];
        foreach ($batchTags as $batchTag) {
            $batchTag = \trim($batchTag);
            $lowerCaseTag = \strtolower($batchTag);
            //$possibleTags = DataList::create('FlickrTag')->where("Value='".$lowerCaseTag."'")
            $possibleTags = FlickrTag::get()->filter(['Value' => $lowerCaseTag]);

            if ($possibleTags->count() === 0) {
                $tag = new FlickrTag();
                $tag->Value = $lowerCaseTag;
                $tag->RawValue = $batchTag;
                $tag->write();
            } else {
                $tag = $possibleTags->first();
            }

            \array_push($tags, $tag->ID);
        }

        foreach ($flickrPhotos as $fp) {
            $fp->Title=$batchTitle;
            $fp->Description = $batchDescription;
            $fp->FlickrTags()->addMany($tags);
            $fp->write();
        }

        return $flickrPhotos->count();
    }
}
