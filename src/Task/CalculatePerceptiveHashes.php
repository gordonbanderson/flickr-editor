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
use Suilven\Flickr\Helper\FlickrPerceptiveHashHelper;
use Suilven\Flickr\Helper\FlickrSetHelper;
use Suilven\Flickr\Model\Flickr\FlickrPhoto;
use Suilven\Flickr\Model\Flickr\FlickrSet;
use Suilven\Flickr\Model\Site\FlickrSetPage;


class CalculatePerceptiveHashes extends BuildTask
{

    protected $title = 'Calculate perceptive hashes for a Flickr Set';

    protected $description = 'Calculate perceptive hashes and store in the database for a given Flickr set';

    private static $segment = 'calculate-perceptive-hash';

    protected $enabled = true;


    private function mkdir_if_required($dir)
    {
        if (!file_exists($dir) && !is_dir($dir)) {
            mkdir($dir);
        }
    }

    private function calculatePerceptiveHashes($flickrSet, $targetDir, $size)
    {
        error_log('---- new image ----');
        error_log('SIZE: ' . $size);
        foreach ($flickrSet->FlickrPhotos()->sort('UploadUnixTimeStamp') as $flickrPhoto) {
            $oldHash = $flickrPhoto->PerceptiveHash;


            error_log('START: hash = ' . $oldHash);
            error_log('ID: ' . $flickrPhoto->ID);
            if ($flickrPhoto->PerceptiveHash) {
                echo '>>>>> Already calculated hash';
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
            error_log('Downloading ' . $imageURL . ' of size ' . $size);
            $ch = curl_init($imageURL);

            $filename = 'tohash.jpg';
            $complete_hash_file_path = trim($targetDir) . '/' . trim($filename);
            $complete_hash_file_path = str_replace(' ', '', $complete_hash_file_path);

            error_log('CSL: ' . $complete_hash_file_path);

            // @todo This fails if public/flickr/images/FLICKR_SET_ID is missing
            error_log('TARGET DIR: ' . $targetDir);
            $fp = fopen($complete_hash_file_path, 'wb');

            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_exec($ch);
            curl_close($ch);
            fclose($fp);

            // @todo Make this configurable
            // tool is avail from https://github.com/commonsmachinery/blockhash-python4, just clone it
            // also required is python-pil
            $hashCMD = '/var/www/blockhash-python/blockhash.py ' . $complete_hash_file_path;
            $o = exec($hashCMD, $output);
            $splits = explode(' ', $o);
            $hash = $splits[0];
            error_log('Saving hash ' . $hash);
            $flickrPhoto->PerceptiveHash = $hash;
            $flickrPhoto->write();

        }

    }

    public function findSequences($flickrSet, $srcDir, $targetDir)
    {
        $helper = new FlickrPerceptiveHashHelper();
        $buckets = $helper->findSequences($flickrSet);

        $html = '';

        $ctr = 0;

        $bucketSize = count($buckets);

        error_log('BS: ' . $bucketSize);

        for($j=0; $j< $bucketSize; $j++) {
            error_log('BUCKET');
            $bucket = $buckets[$j];
            for ($i=0; $i<$bucketSize; $i++) {
                $html .= "\n<img src='". $bucket[$i]['url']."'/>";

                $filename = basename($bucket[$i]['url']);
                $from = trim($srcDir) .'/' . trim($filename);

                $paddedCtr = str_pad($ctr, 8, '0', STR_PAD_LEFT);
                $to  = trim($targetDir) .'/' . $paddedCtr . '.JPG';
                error_log($from . ' --> ' . $to);
                copy($from, $to);
                $rotated = $bucket[$i]['rotated'];
                error_log('>>>> ROTATED: ' . $rotated);
                if ($rotated) {
                    $dimensions = '2048x1365';
                    $cmd = ('/usr/bin/convert ' . $to .' -gravity center -background black -extent ' . $dimensions .' ' . $to);
                    error_log('CMD:' . $cmd);
                    exec($cmd);
                }

                $ctr++;
            }


            $html .= '<br/><hr/><br/>';
        }

        file_put_contents('/var/www/buckets.html', $html);
    }






    public function run($request)
    {
        // check this script is being run by admin
        $canAccess = (Director::isDev() || Director::is_cli() || Permission::check("ADMIN"));
        if (!$canAccess) {
            return Security::permissionFailure($this);
        }

        $size = 'small';

        $flickrSetID = $_GET['id'];

        $flickrSetHelper = new FlickrSetHelper();
        $flickrSet = $flickrSetHelper->getOrCreateFlickrSet($flickrSetID);

        $this->mkdir_if_required('/tmp/flickr');
        $targetDir = '/tmp/flickr/' . $flickrSetID;
        $this->mkdir_if_required($targetDir);
        $movieDir = $targetDir . '/movie';
        $this->mkdir_if_required($movieDir);

        $srcDir = 'public/flickr/images/' . $flickrSetID;

        $this->calculatePerceptiveHashes($flickrSet, $srcDir, $targetDir, $size);
        $this->findSequences($flickrSet, $srcDir, $movieDir);
    }



}
