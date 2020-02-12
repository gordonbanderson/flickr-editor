<?php
namespace Suilven\Flickr\GraphQL\Type;


use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use SilverStripe\GraphQL\Pagination\Connection;
use SilverStripe\GraphQL\TypeCreator;
use SilverStripe\Security\Security;
use SilverStripe\Security\SecurityToken;

class FlickrSetTypeCreator extends TypeCreator
{
    public function attributes()
    {
        return [
            'name' => 'flickrsets'
        ];
    }

    public function fields()
    {
        $photosConnection = Connection::create('FlickrPhotos')
            ->setConnectionType($this->manager->getType('flickrphoto'))
            ->setDescription('The photos in this flickr set')
            ->setSortableFields(['ID', 'Title'])
            ->setDefaultLimit(20)
            ->setMaximumLimit(500);

        return [
            'ID' => ['type' => Type::nonNull(Type::id())],
            'FlickrID' => ['type' => Type::nonNull(Type::id())],
            'Title' => ['type' => Type::string()],
            'SortOrder' => ['type' => Type::int()],

            'FlickrPhotos' => [
                'type' => $photosConnection->toType(),
                'args' => $photosConnection->args(),
                'resolve' => function($flickrSet, array $args, $context, ResolveInfo $info) use ($photosConnection) {
                    return $photosConnection->resolveList(
                        $flickrSet->FlickrPhotos()->sort($flickrSet->SortOrder),
                        $args,
                        $context
                    );
                }
            ]
        ];
    }




}
