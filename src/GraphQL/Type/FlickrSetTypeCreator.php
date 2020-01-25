<?php
namespace Suilven\Flickr\GraphQL\Type;


use GraphQL\Type\Definition\Type;
use SilverStripe\GraphQL\TypeCreator;

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
        return [
            'ID' => ['type' => Type::nonNull(Type::id())],
            'FlickrID' => ['type' => Type::nonNull(Type::id())],
            'Title' => ['type' => Type::string()],
        ];
    }
}
