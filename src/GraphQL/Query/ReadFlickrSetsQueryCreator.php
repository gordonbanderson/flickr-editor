<?php
namespace Suilven\Flickr\GraphQL\Query;


use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use SilverStripe\GraphQL\OperationResolver;
use SilverStripe\GraphQL\QueryCreator;
use Suilven\Flickr\Model\Flickr\FlickrSet;

class ReadFlickrSetsQueryCreator extends QueryCreator implements OperationResolver
{

    public function attributes()
    {
        return [
            'name' => 'readFlickrSets'
        ];
    }

    public function args()
    {
        return [
            'ID' => ['type' => Type::int()]
        ];
    }

    public function type()
    {
        return Type::listOf($this->manager->getType('flickrset'));
    }

    /**
     * @inheritDoc
     */
    public function resolve($object, array $args, $context, ResolveInfo $info)
    {
        $sets = FlickrSet::get();
        if (isset($args['ID'])) {
            $sets = $sets->filter('ID', $args['ID']);
        }

        return $sets;
    }
}
