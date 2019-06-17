<?php
namespace Suilven\Flickr\Model\Flickr;

use SilverStripe\ORM\DataObject;

class FlickrAuthor extends DataObject
{
    private static $table_name = 'FlickrAuthor';

    private static $db = array(
            'PathAlias' => 'Varchar',
            'DisplayName' => 'Varchar'
        );

    private static $has_many = array('FlickrPhotos' => 'FlickrPhoto');


    private static $summary_fields = array(
            'PathAlias' => 'URL',
            'DisplayName' => 'Display Name'
        );


    /**
     * A search is made of the path alias during flickr set import
     */
    private static $indexes = array(
            'PathAlias' => true
        );
}
