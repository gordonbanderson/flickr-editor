<?php declare(strict_types = 1);

namespace Suilven\Flickr\GraphQL\Query;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use SilverStripe\GraphQL\OperationResolver;
use SilverStripe\GraphQL\QueryCreator;
use SilverStripe\ORM\DataObject;
use Suilven\Flickr\Model\Flickr\FlickrSet;

// @phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter

/**
 * Class ReadFlickrPhotosQueryCreator
 *
 * @package Suilven\Flickr\GraphQL\Query
 */
class ReadFlickrPhotosQueryCreator extends QueryCreator implements OperationResolver
{

    /** @return array<string,string> */
    public function attributes(): array
    {
        return [
            'name' => 'readFlickrPhotos',
        ];
    }


    /** @return array<string,array<string,int>> */
    public function args(): array
    {
        return [
            'FlickrSetID' => ['type' => Type::int()],
        ];
    }


    /** @inheritDoc */
    public function type()
    {
        return Type::listOf($this->manager->getType('flickrphoto'));
    }


    /** @inheritDoc */
    public function resolve($object, array $args, $context, ResolveInfo $info)
    {

        if (!isset($args['FlickrSetID'])) {
            throw new \InvalidArgumentException('FlickrSetID parameter is required');
        }

        if (isset($args['FlickrSetID'])) {
            $flickrSet = DataObject::get_by_id(FlickrSet::class, $args['FlickrSetID']);
        }

        return $flickrSet->FlickrPhotos()->sort($flickrSet->SortOrder);
    }
}
