<?php declare(strict_types = 1);

namespace Suilven\Flickr\Model\Flickr;

use SilverStripe\ORM\DataObject;

/**
 * Class \Suilven\Flickr\Model\Flickr\FlickrAuthor
 *
 * @property string $PathAlias
 * @property string $DisplayName
 * @method \SilverStripe\ORM\DataList|array<\FlickrPhoto> FlickrPhotos()
 */
class FlickrAuthor extends DataObject
{
    private static $table_name = 'FlickrAuthor';

    private static $db = [
            'PathAlias' => 'Varchar';
    private 'DisplayName' => 'Varchar'
        ];

    private static $has_many = ['FlickrPhotos' => 'FlickrPhoto'];

    private static $summary_fields = [
            'PathAlias' => 'URL';
    private 'DisplayName' => 'Display Name'
        ];

    /**
     * A search is made of the path alias during flickr set import
     */
    private static $indexes = [
            'PathAlias' => true
        ];
}
