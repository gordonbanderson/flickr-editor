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
use Suilven\Flickr\Helper\FlickrSetHelper;


class DownloadImagesTask extends BuildTask
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

    private function downloadSet($flickrSet, $targetDir, $size)
    {
        error_log('SIZE: ' . $size);
        foreach ($flickrSet->FlickrPhotos() as $flickrPhoto) {
            $imageURL = $flickrPhoto->SmallURL;
            switch($size) {
                case 'original':
                    $imageURL = $flickrPhoto->OriginalURL;
                    break;
                case 'small':
                    $imageURL = $flickrPhoto->SmallURL;
                    break;
                case 'medium':
                    $imageURL = $flickrPhoto->MediumURL;
                    break;
                case 'large':
                    $imageURL = $flickrPhoto->LargeURL;
                    break;
                case 'large1600':
                    $imageURL = $flickrPhoto->LargeURL1600;
                    break;
                case 'large2048':
                    $imageURL = $flickrPhoto->LargeURL2048;
                    break;
                default:
                    // url already defaulted
            }
            error_log('Downloading ' . $imageURL);
            $ch = curl_init($imageURL);

            $filename = basename($imageURL);
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

        $size = $_GET['size'] ? $_GET['size'] : 'small';

        $flickrSetID = $_GET['id'];

        $flickrSetHelper = new FlickrSetHelper();
        $flickrSet = $flickrSetHelper->getOrCreateFlickrSet($flickrSetID);

        $this->mkdir_if_required('public/flickr');
        $this->mkdir_if_required('public/flickr/images');
        $targetDir = 'public/flickr/images/' . $flickrSetID;
        $this->mkdir_if_required($targetDir);

        $this->downloadSet($flickrSet, $targetDir, $size);

    }








}
