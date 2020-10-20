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
    /** @var string */
    private static $table_name = 'FlickrAuthor';

    /** @var array<string,string> */
    private static $db = [
        'PathAlias' => 'Varchar',
        'DisplayName' => 'Varchar',
        'FlickrID' => 'Varchar',
    ];

    /** @var array<string> */
    private static $has_many = ['FlickrPhotos' => 'FlickrPhoto'];

    /** @var array<string,string> */
    private static $summary_fields = [
        'PathAlias' => 'URL',
        'DisplayName' => 'Display Name',
    ];

    /**
     * @var array<string,bool>
     * A search is made of the path alias during flickr set import
     */
    private static $indexes = [
        'PathAlias' => true,
    ];
}
