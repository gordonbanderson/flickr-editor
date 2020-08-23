<?php declare(strict_types = 1);

namespace Suilven\Flickr\Task;

use SilverStripe\Control\Director;
use SilverStripe\Dev\BuildTask;
use SilverStripe\Security\Permission;
use SilverStripe\Security\Security;
use Suilven\Flickr\Helper\FlickrSetHelper;
use Suilven\Flickr\Model\Flickr\FlickrSet;

// @phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint

/**
 * Class DownloadVisibleImagesTask
 *
 * @package Suilven\Flickr\Task
 */
class DownloadVisibleImagesTask extends BuildTask
{

    protected $title = 'Download visible images of a Flickr Set';

    protected $description = 'Download selected gallery, in numerical order, and create a zip file';

    protected $enabled = true;

    /** @var string */
    private static $segment = 'download-flickr-set-for-facebook';


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

        $sizeStr = $request->getVar('size');

        $size = isset($sizeStr)
            ? $sizeStr
            : 'large2048';

        $flickrSetID = $request->getVar('id');

        $flickrSetHelper = new FlickrSetHelper();
        $flickrSet = $flickrSetHelper->getOrCreateFlickrSet($flickrSetID);

        $this->mkdirIfRequired('public/flickr');
        $this->mkdirIfRequired('public/flickr/images');
        $targetDir = 'public/flickr/images/' . $flickrSetID;
        $this->mkdirIfRequired($targetDir);

        $this->downloadSet($flickrSet, $targetDir, $size);
    }


    private function mkdirIfRequired(string $dir): void
    {
        if (\file_exists($dir) || \is_dir($dir)) {
            return;
        }

        \mkdir($dir);
    }

    // @phpstan-ignore-next-line
    private function downloadSet(FlickrSet $flickrSet, string $targetDir, string $size): void
    {
        $counter = 0;

        // @phpstan-ignore-next-line
        $photos = $flickrSet->FlickrPhotos()->filter('Visible', true)->sort($flickrSet->SortOrder);
        foreach ($photos as $flickrPhoto) {
            $counter++;
            $paddedCounter = \sprintf('%04d', $counter);
            $imageURL = $flickrPhoto->SmallURL;
            switch ($size) {
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
            \error_log('Downloading ' . $imageURL);

            /** @var resource $ch */
            $ch = \curl_init($imageURL);

            $complete_save_loc = \trim($targetDir) .'/' . $paddedCounter . '.JPG';
            $complete_save_loc = \str_replace(' ', '', $complete_save_loc);

            \error_log('CSL: ' . $complete_save_loc);

            /** @var resource $fp */
            $fp = \fopen($complete_save_loc, 'wb');

            \curl_setopt($ch, \CURLOPT_FILE, $fp);
            \curl_setopt($ch, \CURLOPT_HEADER, 0);
            \curl_exec($ch);
            \curl_close($ch);
            \fclose($fp);
        }

        $cmd = "cd public/flickr/images/ && zip -r ../../{$flickrSet->FlickrID}.zip {$flickrSet->ID}";
        \error_log($cmd);
    }
}
