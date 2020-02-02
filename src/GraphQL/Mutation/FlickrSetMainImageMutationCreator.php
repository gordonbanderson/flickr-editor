<?php
namespace Suilven\Flickr\GraphQL\Mutation;


use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use SilverStripe\GraphQL\MutationCreator;
use SilverStripe\GraphQL\OperationResolver;
use SilverStripe\GraphQL\Pagination\Connection;
use SilverStripe\GraphQL\Pagination\PaginatedQueryCreator;
use SilverStripe\ORM\DataObject;
use Suilven\Flickr\Model\Flickr\FlickrPhoto;
use Suilven\Flickr\Model\Flickr\FlickrSet;

class FlickrSetMainImageMutationCreator extends MutationCreator implements OperationResolver
{
    public function attributes()
    {
        return [
            'name' => 'changeMainImage',
            'description' => 'Change the main image of a Flickr set'
        ];
    }

    public function type()
    {
        return $this->manager->getType('flickrset');
    }

    public function args()
    {
        return [
            'FlickrSetID' => ['type' => Type::nonNull(Type::int())],
            'FlickrPhotoID' => ['type' => Type::nonNull(Type::int())],
        ];
    }


    /**
     * @inheritDoc
     */
    public function resolve($object, array $args, $context, ResolveInfo $info)
    {
        // ID is the FlickrSet SilverStripe ID
        if (!isset($args['FlickrSetID'])) {
            throw new \InvalidArgumentException('SERVER FlickrSetID parameter is required');
        }

        if (!isset($args['FlickrPhotoID'])) {
            throw new \InvalidArgumentException('SERVER FlickrPhotoID parameter is required');
        }



        $flickrset = DataObject::get_by_id(FlickrSet::class, $args['FlickrSetID']);
        $flickrset->PrimaryFlickrPhotoID = $args['FlickrPhotoID'];
        $flickrset->write();
        return $flickrset;
    }
}
