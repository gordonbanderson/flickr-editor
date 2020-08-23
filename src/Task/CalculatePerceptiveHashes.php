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
 * Class CalculatePerceptiveHashes
 *
 * @package Suilven\Flickr\Task
 */
class CalculatePerceptiveHashes extends BuildTask
{

    protected $title = 'Calculate perceptive hashes for a Flickr Set';

    protected $description = 'Calculate perceptive hashes and store in the database for a given Flickr set';

    protected $enabled = true;

    /** @var string */
    private static $segment = 'calculate-perceptive-hash';


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

        /** @var string|null $sizeFromRequest */
        $sizeFromRequest = $request->getVar('size');

        $size = isset($sizeFromRequest)
            ? $sizeFromRequest
            : 'small';

        $flickrSetID = $request->getVar('id');

        $flickrSetHelper = new FlickrSetHelper();
        $flickrSet = $flickrSetHelper->getOrCreateFlickrSet($flickrSetID);

        $this->mkdirIfRequired('/tmp/flickr');
        $targetDir = '/tmp/flickr/' . $flickrSetID;
        $this->mkdirIfRequired($targetDir);
        $movieDir = $targetDir . '/movie';
        $this->mkdirIfRequired($movieDir);
        $this->calculatePerceptiveHashes($flickrSet, $targetDir, $size);
    }


    private function mkdirIfRequired(string $dir): void
    {
        if (\file_exists($dir) || \is_dir($dir)) {
            return;
        }

        \mkdir($dir);
    }


    private function calculatePerceptiveHashes(FlickrSet $flickrSet, string $targetDir, string $size): void
    {
        \error_log('---- new image ----');
        \error_log('SIZE: ' . $size);
        $counter = 0;

        $total = $flickrSet->FlickrPhotos()->count();

        foreach ($flickrSet->FlickrPhotos()->sort($flickrSet->SortOrder) as $flickrPhoto) {
            $counter++;

            if ($flickrPhoto->PerceptiveHash) {
                continue;
            }
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
                    $imageURL = $flickrPhoto->Large1600;

                    break;
                default:
                    // url already defaulted
            }

            /** @var resource $ch */
            $ch = \curl_init($imageURL);

            $filename = 'tohash.jpg';
            $complete_hash_file_path = \trim($targetDir) . '/' . \trim($filename);
            $complete_hash_file_path = \str_replace(' ', '', $complete_hash_file_path);

            //error_log('CSL: ' . $complete_hash_file_path);

            // @todo This fails if public/flickr/images/FLICKR_SET_ID is missing
            //error_log('TARGET DIR: ' . $targetDir);

            /** @var resource $fp */
            $fp = \fopen($complete_hash_file_path, 'wb');

            \curl_setopt($ch, \CURLOPT_FILE, $fp);
            \curl_setopt($ch, \CURLOPT_HEADER, 0);
            \curl_exec($ch);
            \curl_close($ch);
            \fclose($fp);

            // @todo Make this configurable
            // tool is avail from https://github.com/commonsmachinery/blockhash-python4, just clone it
            // also required is python-pil

            $hashCMD = 'python3 /var/www/blockhash-python/blockhash.py ' . $complete_hash_file_path;
            $o = \exec($hashCMD, $output);
            $splits = \explode(' ', $o);
            $hash = $splits[0];

            \error_log($counter . '/' . $total . '    [' . $hash .']');


            $flickrPhoto->PerceptiveHash = $hash;
            $flickrPhoto->write();
        }
    }
}
