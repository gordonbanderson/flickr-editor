<?php declare(strict_types = 1);

namespace Suilven\Flickr\Controller;

use SilverStripe\Control\Director;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;

// @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps

/**
 * Class FlickrTagsController
 *
 * @package Suilven\Flickr\Controller
 */
class FlickrTagsController extends \PageController
{
    private static $allowed_actions = [
        'index',
        'photo',
        'photos',
    ];


    public function ColumnLayout(): string
    {
        return 'layout1col';
    }


    public function init(): void
    {
        parent::init();

        // Requirements, etc. here
    }


    /** @return \Suilven\Flickr\Controller\DataList<\Suilven\Flickr\Controller\FlickrPhoto> */
    public function photo(): DataList
    {
        $tagValue = Director::URLParam('ID');
        $this->Title = "Photos tagged '" . $tagValue . "'";

        // @todo This is very old style code, fix
        $tag = DataObject::get_one('Tag', "Value='" . $tagValue . "'");
        $this->TagValue = $tagValue;
        $this->Tag = $tag;

        if ($tag) {
            $this->FlickrPhotos = $tag->FlickrPhotos();
        }

        return $this->FlickrPhotos;
    }


    // @TODO check this method, old as

    /** @return \SilverStripe\ORM\DataList<\Suilven\Flickr\Controller\FlickrPhoto> */
    public function photos(): \SilverStripe\ORM\DataList
    {
        $this->Tags = DataObject::get('Tag');
        $this->Title = 'Tags for photos';

        $maxCount = DB::query("SELECT COUNT(TagID) as ct FROM FlickrPhoto_FlickrTags Group by' .'
            ' TagID Order by ct desc limit 1")->value();

        $sql = "select t.ID, t.ClassName, count(TagID) as Amount, t.Value
				From FlickrPhoto_FlickrTags ft
				inner join Tag t
				on t.ID = ft.TagID
				group by TagID
				order by t.Value
		;";

        $result = DB::query($sql);

        $tagCloud = \singleton('Tag')->buildDataObjectSet($result);
        foreach ($tagCloud as $tagV) {
            // font size in pixels
            $tagV->Amount = 10 + \round(32 * $tagV->Amount / $maxCount);
        }

        $this->TagCloud = $tagCloud;
    }
}
