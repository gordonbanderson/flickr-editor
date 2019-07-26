<?php
/**
 * Created by PhpStorm.
 * User: gordon
 * Date: 11/4/2561
 * Time: 16:22 น.
 */

namespace Suilven\Flickr\Task;

use Samwilson\PhpFlickr\PhotosetsApi;
use Samwilson\PhpFlickr\PhpFlickr;
use SilverStripe\Assets\Folder;
use SilverStripe\Assets\Image;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;
use SilverStripe\Security\Permission;
use SilverStripe\Security\Security;
use Suilven\Flickr\Helper\FlickrHelper;
use Suilven\Flickr\Helper\FlickrSetHelper;
use Suilven\Flickr\Model\Flickr\FlickrPhoto;
use Suilven\Flickr\Model\Flickr\FlickrSet;
use Suilven\Flickr\Model\Site\FlickrSetPage;


class DownloadThumbnailImages extends BuildTask
{

    protected $title = 'Download thumbnail images of a Flickr Set';

    protected $description = 'Download thumbs from a Flickr set for the purposes of either self hosting or sprite generation';

    private static $segment = 'download-flickr-set-thumbs';

    protected $enabled = true;


    private function mkdir_if_required($dir)
    {
        if (!file_exists($dir) && !is_dir($dir)) {
            mkdir($dir);
        }
    }

    private function downloadSet($flickrSet, $targetDir)
    {
        foreach ($flickrSet->FlickrPhotos() as $flickrPhoto) {
            $thumbnailURL = $flickrPhoto->SmallURL;
            $ch = curl_init($thumbnailURL);

            $filename = basename($thumbnailURL);
            $complete_save_loc = trim($targetDir) .'/' . trim($filename);
            $complete_save_loc = str_replace(' ', '', $complete_save_loc);

            error_log('CSL: ' . $complete_save_loc);

            $fp = fopen($complete_save_loc, 'wb');

            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_exec($ch);
            curl_close($ch);
            fclose($fp);
        }

    }

    public function run($request)
    {
        // check this script is being run by admin
        $canAccess = (Director::isDev() || Director::is_cli() || Permission::check("ADMIN"));
        if (!$canAccess) {
            return Security::permissionFailure($this);
        }

        $flickrSetID = $_GET['id'];

        $flickrSetHelper = new FlickrSetHelper();
        $flickrSet = $flickrSetHelper->getOrCreateFlickrSet($flickrSetID);

        $this->mkdir_if_required('public/flickr');
        $this->mkdir_if_required('public/flickr/thumbs');
        $targetDir = 'public/flickr/thumbs/' . $flickrSetID;
        $this->mkdir_if_required($targetDir);

        $this->downloadSet($flickrSet, $targetDir);

    }








}
