<?php declare(strict_types = 1);

namespace Suilven\Flickr\Task;

use SilverStripe\Control\Director;
use SilverStripe\Dev\BuildTask;
use SilverStripe\Security\Permission;
use SilverStripe\Security\Security;
use Suilven\Flickr\Helper\FlickrGalleryHelper;

// @phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint

/**
 * Class ImportGalleryTask
 *
 * @package Suilven\Flickr\Task
 */
class ImportGalleryTask extends BuildTask
{

    /** @var string */
    protected $title = 'Import a Flickr gallery';

    /** @var string */
    protected $description = 'Import a flickr gallery';

    /** @var bool */
    protected $enabled = true;

    /** @var string */
    private static $segment = 'import-flickr-gallery';


    /**
     * @param \SilverStripe\Control\HTTPRequest $request
     * @return \SilverStripe\Control\HTTPResponse | void
     */
    public function run($request)
    {
        // check this script is being run by admin
        $canAccess = (Director::isDev() || Director::is_cli() || (bool) Permission::check("ADMIN"));
        if (!$canAccess) {
            return Security::permissionFailure();
        }

        $flickrGalleryID = $request->getVar('id');

        $flickrGalleryHelper = new FlickrGalleryHelper();
        $flickrGalleryHelper->importGallery($flickrGalleryID);
    }
}
