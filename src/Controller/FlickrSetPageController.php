<?php declare(strict_types = 1);

namespace Suilven\Flickr\Controller;

// @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
use SilverStripe\ORM\SS_List;

/**
 * Class \Suilven\Flickr\Controller\FlickrSetPageController
 */
class FlickrSetPageController extends \PageController
{
    /** @var \Suilven\Flickr\Controller\SS_List<\Suilven\Flickr\Controller\FlickrPhoto>|null */
    private $FlickrPics;

    public function FlickrPhotos(): ?SS_List
    {
        if (!isset($this->FlickrPics)) {
            $images = $this->FlickrSetForPage()->FlickrPhotos();
            $this->FlickrPics = $images;
        }

        return $this->FlickrPics;
    }


    /**
    * I use this for highslide to replace the URLs in javascript if javascript is available, otherwise
    * default to normal page URLs
     *
     * @return string JSON mapping of silverstripe ID to URL
    */
    public function IdToUrlJson(): string
    {
        $result = [];
        foreach ($this->FlickrPhotos() as $fp) {
            $result[$fp->ID] = $fp->LargeURL;
        }

        return \json_encode($result);
    }


    /** @return bool true if the flickr set has geo */
    public function HasGeo(): bool
    {
        return $this->FlickrSetForPage()->HasGeo();
    }
}
