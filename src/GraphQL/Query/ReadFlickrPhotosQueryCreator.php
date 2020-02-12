<?php
namespace Suilven\Flickr\GraphQL\Query;


use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use SilverStripe\GraphQL\OperationResolver;
use SilverStripe\GraphQL\QueryCreator;
use SilverStripe\ORM\DataObject;
use Suilven\Flickr\Model\Flickr\FlickrSet;

class ReadFlickrPhotosQueryCreator extends QueryCreator implements OperationResolver
{

    public function attributes()
    {
        return [
            'name' => 'readFlickrPhotos'
        ];
    }

    public function args()
    {
        return [
            'FlickrSetID' => ['type' => Type::int()]
        ];
    }

    public function type()
    {
        return Type::listOf($this->manager->getType('flickrphoto'));
    }

    /**
     * @inheritDoc
     */
    public function resolve($object, array $args, $context, ResolveInfo $info)
    {

        if (!isset($args['FlickrSetID'])) {
            throw new \InvalidArgumentException('FlickrSetID parameter is required');
        }

        $photos = [];
        if (isset($args['FlickrSetID'])) {
            $flickrSet = DataObject::get_by_id(FlickrSet::class, $args['FlickrSetID']);

        }

        $photos = $flickrSet->FlickrPhotos()->sort($flickrSet->SortOrder);

        return $photos;
    }
}
