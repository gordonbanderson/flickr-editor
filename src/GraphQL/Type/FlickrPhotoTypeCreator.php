<?php
namespace Suilven\Flickr\GraphQL\Type;


use GraphQL\Type\Definition\Type;
use SilverStripe\GraphQL\TypeCreator;

class FlickrPhotoTypeCreator extends TypeCreator
{
    public function attributes()
    {
        return [
            'name' => 'flickrphoto'
        ];
    }

    public function fields()
    {
        return [
            'ID' => ['type' => Type::nonNull(Type::id())],
            'ThumbnailWidth' => ['type' => Type::int()],
            'ThumbnailHeight' => ['type' => Type::int()],
            'ThumbnailURL' => ['type' => Type::string()],
        ];
    }
}
