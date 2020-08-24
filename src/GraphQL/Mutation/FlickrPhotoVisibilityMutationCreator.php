<?php declare(strict_types = 1);

namespace Suilven\Flickr\GraphQL\Mutation;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use SilverStripe\GraphQL\MutationCreator;
use SilverStripe\GraphQL\OperationResolver;
use SilverStripe\ORM\DataObject;
use Suilven\Flickr\Model\Flickr\FlickrPhoto;

// @phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter

/**
 * Class FlickrPhotoVisibilityMutationCreator
 *
 * @package Suilven\Flickr\GraphQL\Mutation
 */
class FlickrPhotoVisibilityMutationCreator extends MutationCreator implements OperationResolver
{
    /** @return array<string,array<string,string>> */
    public function attributes(): array
    {
        return [
            'name' => 'toggleVisibility',
            'description' => 'Toggle the visiblity of a Flickr photo',
        ];
    }


    /** @inheritDoc */
    public function type()
    {
        return $this->manager->getType('flickrphoto');
    }


    /** @return array<string, array<string, \GraphQL\Type\Definition\Type>> */
    public function args(): array
    {
        return [
            'ID' => ['type' => Type::nonNull(Type::int())],
        ];
    }


    /** @inheritDoc */
    public function resolve($object, array $args, $context, ResolveInfo $info)
    {
        if (!isset($args['ID'])) {
            throw new \InvalidArgumentException('ID parameter is required');
        }

        /** @var FlickrPhoto $flickrPhoto */
        $flickrPhoto = DataObject::get_by_id(FlickrPhoto::class, $args['ID']);
        $flickrPhoto->Visible = !$flickrPhoto->Visible;
        $flickrPhoto->write();

        return $flickrPhoto;
    }
}
