<?php declare(strict_types = 1);

namespace Suilven\Flickr\Controller;

use SilverStripe\ORM\DataList;

/**
 * Class \Suilven\Flickr\Controller\FlickrExportController
 */
class FlickrExportController extends \PageController
{
    private static $allowed_actions = [
        'toJson',
    ];

    public function index()
    {
        return 'wibble';
    }


    public function toJson(): void
    {
        $flickrSetID = $this->request->param('ID');
        $flickrSet = DataList::create('FlickrSet')->where('FlickrID = '.$flickrSetID)->first();
        $images = [];
        foreach ($flickrSet->FlickrPhotos() as $fp) {
            $image = [];

            $image['Lat'] = $fp->Lat;
            $image['Lon'] = $fp->Lon;

            $image['ThumbnalURL'] = $fp-> ThumbnalURL;
            $image['MediumURL'] = $fp-> MediumURL;
            $image['SmallURL'] = $fp-> SmallURL;
            $image['LargeURL'] = $fp-> LargeURL;
            $image['Title'] = $fp-> Title;
            $image['Description'] = $fp-> Title;
            \array_push($images, $image);
        }

        \file_put_contents("/tmp/output.json", \stripslashes(\json_encode($images)));
    }
}
