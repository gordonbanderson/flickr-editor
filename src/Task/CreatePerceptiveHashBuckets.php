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
use Suilven\Flickr\Helper\FlickrPerceptiveHashHelper;
use Suilven\Flickr\Helper\FlickrSetHelper;

// @phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint

/**
 * Class CreatePerceptiveHashBuckets
 *
 * @package Suilven\Flickr\Task
 */
class CreatePerceptiveHashBuckets extends BuildTask
{

    protected $title = 'Create buckets for a flickr set based on perceptive hash';

    protected $description = 'Create buckets based on perceptive hash';

    protected $enabled = true;

    /** @var string */
    private static $segment = 'buckets-from-perceptive-hash';

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

        $flickrSetID = $request->getVar('id');

        $flickrSetHelper = new FlickrSetHelper();
        $flickrSet = $flickrSetHelper->getOrCreateFlickrSet($flickrSetID);

        $pHashHelper = new FlickrPerceptiveHashHelper();
        $bucketsArrary = $pHashHelper->calculateSequences($flickrSet);
        \print_r($bucketsArrary);

        $buckets = $flickrSet->FlickrBuckets();

        // @TODO something funny going on here with types
        // @phpstan-ignore-next-line
        if ($buckets->Count() !== 0) {
            return;
        }
    }
}
