<?php declare(strict_types = 1);

namespace Suilven\Flickr\GraphQL\Query;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use SilverStripe\GraphQL\OperationResolver;
use SilverStripe\GraphQL\QueryCreator;
use Suilven\Flickr\Model\Flickr\FlickrSet;

// @phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter

/**
 * Class ReadFlickrSetsQueryCreator
 *
 * @package Suilven\Flickr\GraphQL\Query
 */
class ReadFlickrSetsQueryCreator extends QueryCreator implements OperationResolver
{
    /** @return array<string,string> */
    public function attributes(): array
    {
        return [
            'name' => 'readFlickrSets',
        ];
    }


    /** @return array<string,array<string,int>> */
    public function args(): array
    {
        return [
            'ID' => ['type' => Type::int()],
        ];
    }


    /** @inheritDoc */
    public function type()
    {
        return Type::listOf($this->manager->getType('flickrset'));
    }


    /** @inheritDoc */
    public function resolve($object, array $args, $context, ResolveInfo $info)
    {
        $sets = FlickrSet::get();
        if (isset($args['ID'])) {
            $sets = $sets->filter('ID', $args['ID']);
        }

        return $sets;
    }
}
