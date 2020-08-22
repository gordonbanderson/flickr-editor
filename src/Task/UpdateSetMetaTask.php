<?php declare(strict_types = 1);

/**
 * Created by PhpStorm.
 * User: gordon
 * Date: 11/4/2561
 * Time: 16:22 น.
 */

namespace Suilven\Flickr\Task;

use SilverStripe\Control\Director;
use SilverStripe\Dev\BuildTask;
use SilverStripe\Security\Permission;
use SilverStripe\Security\Security;
use Suilven\Flickr\Model\Flickr\FlickrSet;

class UpdateSetMetaTask extends BuildTask
{

    protected $title = 'Update Flickr metadata';

    protected $description = 'Updates Flickr metadata from edits made in SilverStripe';

    protected $enabled = true;

    private static $segment = 'update-flickr-set-metadata';


    /** @inheritdoc */
    public function run($request)
    {
        // check this script is being run by admin
        $canAccess = (Director::isDev() || Director::is_cli() || Permission::check("ADMIN"));
        if (!$canAccess) {
            return Security::permissionFailure($this);
        }

        $flickrSetID = $request->getVar('id');
        /** @var \Suilven\Flickr\Model\Flickr\FlickrSet $flickrSet */
        $flickrSet = FlickrSet::get()->filter(['FlickrID' => $flickrSetID])->first();
        $flickrSet->writeToFlickr();
    }
}
