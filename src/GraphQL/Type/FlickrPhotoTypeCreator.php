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
            'FlickrID' => ['type' => Type::nonNull(Type::id())],
            'Title' => ['type' => Type::string()],
            'ThumbnailWidth' => ['type' => Type::int()],
            'ThumbnailHeight' => ['type' => Type::int()],
            'ThumbnailURL' => ['type' => Type::string()],
            'MediumWidth' => ['type' => Type::int()],
            'MediumHeight' => ['type' => Type::int()],
            'MediumURL' => ['type' => Type::string()],
            'SmallURL' => ['type' => Type::string()],
            'SmallURL320' => ['type' => Type::string()],
            'LargeURL' => ['type' => Type::string()],
            'Visible' => ['type' => Type::boolean()],
            'Orientation' => ['type' => Type::int()],
            'CSRFToken' => ['type' => Type::string()],
            'TakenAt' => ['type' => Type::string()],
        ];
    }
}
