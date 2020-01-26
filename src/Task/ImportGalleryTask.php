<?php
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
use Suilven\Flickr\Helper\FlickrGalleryHelper;


class ImportGalleryTask extends BuildTask
{

    protected $title = 'Import a Flickr gallery';

    protected $description = 'Import a flickr gallery';

    private static $segment = 'import-flickr-gallery';

    protected $enabled = true;


    public function run($request)
    {
        // check this script is being run by admin
        $canAccess = (Director::isDev() || Director::is_cli() || Permission::check("ADMIN"));
        if (!$canAccess) {
            return Security::permissionFailure($this);
        }

        $flickrGalleryID = $_GET['id'];

        $flickrGalleryHelper = new FlickrGalleryHelper();
        $flickrGalleryHelper->importGallery($flickrGalleryID);
    }








}
