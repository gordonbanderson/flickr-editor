<?php declare(strict_types = 1);

namespace Suilven\Flickr\Helper;

use SilverStripe\ORM\DataObject;
use Suilven\Flickr\Model\Flickr\FlickrBucket;
use Suilven\Flickr\Model\Flickr\FlickrPhoto;
use Suilven\Flickr\Model\Flickr\FlickrSet;

// @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps

/**
 * Class FlickrBucketHelper
 *
 * @package Suilven\Flickr\Helper
 */
class FlickrBucketHelper extends FlickrHelper
{
    /**
     * @param array<int> $flickrPhotoIDs
     * @throws \SilverStripe\ORM\ValidationException
     */
    public function createBucket(int $flickrSetID, array $flickrPhotoIDs): FlickrBucket
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
