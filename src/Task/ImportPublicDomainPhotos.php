<?php declare(strict_types = 1);

namespace Suilven\Flickr\Task;

use League\CLImate\CLImate;
use SilverStripe\Control\Director;
use SilverStripe\Dev\BuildTask;
use SilverStripe\Security\Permission;
use SilverStripe\Security\Security;
use Suilven\Flickr\Helper\FlickrExifHelper;
use Suilven\Flickr\Helper\FlickrPhotoHelper;
use Suilven\Flickr\Helper\FlickrSetHelper;

// @phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint

/**
 * Class ImportSetTask
 *
 * @package Suilven\Flickr\Task
 */
class ImportPublicDomainPhotos extends BuildTask
{

    /** @var string */
    protected $title = 'Import photos that are public domain';

    /** @var string */
    protected $description = 'Import public domain photos with a tag';

    /** @var bool */
    protected $enabled = true;

    /** @var string */
    private static $segment = 'import-public-domain-flickr';


    /**
     * @param \SilverStripe\Control\HTTPRequest $request
     * @return \SilverStripe\Control\HTTPResponse | void
     */
    public function run($request)
    {
        $climate = new CLImate();
        // check this script is being run by admin
        $canAccess = (Director::isDev() || Director::is_cli() || (bool) Permission::check("ADMIN"));
        if (!$canAccess) {
            return Security::permissionFailure();
        }

        $tagsCSV = $request->getVar('tags');

        $per_tag = 10;
        if(!is_null($request->getVar('per_tag'))) {
            $per_tag = $request->getVar('per_tag');
        }

        $tags = explode(',', $tagsCSV);

        foreach($tags as $tag) {
            $helper = new \Suilven\Flickr\Helper\FlickrHelper();
            $photosAPI = $helper->getPhotosHelper();
            $args = [
                'content_type' => 1, #photos
                'safe_search' => 1, #avoid dodgy pics
                'tags' => $tag,
                'license' => 10,
                'per_page' => $per_tag,
                'extras' => 'license, date_upload, date_taken, owner_name, icon_server, original_format, ' .
                    ' last_update, geo, tags, machine_tags, o_dims, views, media, path_alias, url_t, url_s,' .
                    ' url_q, url_m, url_n, url, url_z, url_c, url_h, url_k, url_l, url_o, description, url_sq'
            ];
            $photos = $photosAPI->search($args);
            $count = count($photos['photo']);

            $climate->border();
            $climate->green('Importing ' . $count . ' images for tag "' . $tag . '"');
            $climate->border();



            $photoHelper = new FlickrPhotoHelper();

            $progress = $climate->progress()->total($count);
            $i = 1;

            $exifHelper = new FlickrExifHelper();
            foreach($photos['photo'] as $photoArray) {
                $progress->current($i);
                $i++;
                $flickrPhoto = $photoHelper->createFromFlickrArray($photoArray);
                if (!is_null($flickrPhoto)) {
                    try {
                        $exifHelper->loadExif($flickrPhoto);
                    } catch (\Exception $ex) {

                    }

                }
            }
        }
    }
}
