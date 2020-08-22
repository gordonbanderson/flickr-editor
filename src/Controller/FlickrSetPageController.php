<?php declare(strict_types = 1);

namespace Suilven\Flickr\Controller;

/**
 * Class \Suilven\Flickr\Controller\FlickrSetPageController
 */
class FlickrSetPageController extends \PageController
{
    public function FlickrPhotos()
    {
        if (!isset($this->FlickrPics)) {
            $images = $this->FlickrSetForPage()->FlickrPhotos();
            $this->FlickrPics = $images;
        }

        return $this->FlickrPics;
    }



    /*
    I use this for highslide to replace the URLs in javascript if javascript is available, otherwise default to normal page URLs
    @return Mapping of silverstripe ID to URL
    */
    public function IdToUrlJson()
    {
        $result = [];
        foreach ($this->FlickrPhotos() as $fp) {
            $result[$fp->ID] = $fp->LargeURL;
        }

        return \json_encode($result);
    }


    public function HasGeo()
    {
        return $this->FlickrSetForPage()->HasGeo();
    }
}
