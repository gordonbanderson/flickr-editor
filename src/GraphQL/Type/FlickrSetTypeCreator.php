<?php declare(strict_types = 1);

namespace Suilven\Flickr\GraphQL\Type;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use SilverStripe\GraphQL\Pagination\Connection;
use SilverStripe\GraphQL\TypeCreator;

// @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
// @phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter

/**
 * Class FlickrSetTypeCreator
 *
 * @package Suilven\Flickr\GraphQL\Type
 */
class FlickrSetTypeCreator extends TypeCreator
{
    /** @return array<string,string> */
    public function attributes(): array
    {
        return [
            'name' => 'flickrsets',
        ];
    }


    /** @return array<string,array<string,mixed>> */
    public function fields(): array
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
                'resolve' => static function ($flickrSet, array $args, $context, ResolveInfo $info)
 use ($photosConnection): void {
                    $photosConnection->resolveList(
                        $flickrSet->FlickrPhotos()->sort($flickrSet->SortOrder),
                        $args,
                        $context
                    );
                },
            ],
        ];
    }
}
