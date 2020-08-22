<?php declare(strict_types = 1);

namespace Suilven\Flickr\Helper;

use SilverStripe\ORM\DataObject;
use Suilven\Flickr\Model\Flickr\FlickrBucket;
use Suilven\Flickr\Model\Flickr\FlickrPhoto;
use Suilven\Flickr\Model\Flickr\FlickrSet;

class FlickrBucketHelper extends FlickrHelper
{
    // @todo DOCS
    public function createBucket($flickrSetID, $flickrPhotoIDs)
    {
        // @todo Check if the ORM does in
        $flickrPhotos = FlickrPhoto::get()->where('"ID" in ('.$flickrPhotoIDs.')');
        $flickrSet = DataObject::get_by_id(FlickrSet::class, $flickrSetID);
        $bucket = new FlickrBucket();
        $bucket->write();

        $bucketPhotos = $bucket->FlickrPhotos();
        foreach ($flickrPhotos as $fp) {
            $bucketPhotos->add($fp);
        }
        $bucket->FlickrSetID = $flickrSet->ID;
        $bucket->write();

        return $bucket;
    }
}
