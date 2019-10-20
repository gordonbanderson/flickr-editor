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


class CalculatePerceptiveHashes extends BuildTask
{

    protected $title = 'Calculate perceptive hashes for a Flickr Set';

    protected $description = 'Calculate perceptive hashes and store in the database for a given Flickr set';

    private static $segment = 'perceptive-hash';

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

            $fp = fopen($complete_hash_file_path, 'wb');

            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_exec($ch);
            curl_close($ch);
            fclose($fp);

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
        $hashes = [];
        error_log($flickrSet->Title);

        foreach ($flickrSet->FlickrPhotos()->sort('UploadUnixTimeStamp') as $flickrPhoto) {
            $pair = [
                'ID' => $flickrPhoto->ID,
                'pHash' => $flickrPhoto->PerceptiveHash,

                // this matters, as it depends on what size was downloaded
                // @todo, refactor
                'URL' => $flickrPhoto->LargeURL2048,

                'SmallURL' => $flickrPhoto->SmallURL,
                'Rotated' => $flickrPhoto->Orientation == 90
            ];
            $hashes[] = $pair;

            print_r($pair);
        }

        $tolerance = 34;
        $minLength = 10;

        $currentBucket = [];
        $buckets = [];
        for ($i = 1; $i < count($hashes) - 1; $i++) {
            // if the bucket is empty, start with the current image
            if (empty($currentBucket)) {
                $currentBucket[] = [
                    'url' => $hashes[$i]['URL'],
                    'rotated' => $hashes[$i]['Rotated']
                ];
            }
            $hash0 = $hashes[$i]['pHash'];
            $hash1 = $hashes[$i + 1]['pHash'];
            $id = $hashes[$i]['ID'];
            $distance = $this->hammingDist($hash0, $hash1);

            // if we are within tolerance, add to the bucket
            if ($distance < $tolerance) {
                $currentBucket[] = [
                    'url' => $hashes[$i]['URL'],
                    'rotated' => $hashes[$i]['Rotated']
                ];
            } else {
                // we need to save the current bucket if it's long enough
                if(sizeof($currentBucket) < $minLength) {
                    error_log('Bucket created but is too short');
                } else {
                    error_log('Adding bucket');
                    $buckets[] = $currentBucket;
                }
                $currentBucket = [];
            }
            error_log('H:' . $id . '    ' . $this->hammingDist($hash0, $hash1) . '     ' . $hashes[$i+1]['URL']);
        }

        // add the last bucket if it's long enough
        if(sizeof($currentBucket) < $minLength) {
            error_log('Bucket created but is too short');
        } else {
            error_log('Adding bucket');
            $buckets[] = $currentBucket;
        }

        $html = '';

        $ctr = 0;

        for($j=0; $j< sizeof($buckets); $j++) {
            error_log('BUCKET');
            $bucket = $buckets[$j];
            for ($i=0; $i<sizeof($bucket); $i++) {
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


    /*
     * Convert hex strings to binary and then calculate hamming distance
     * @param $hash1 hex string for perceptive hash
     * @param $hash2 hex string for perceptive hash
     */
    function hammingDist($hash1, $hash2)
    {
        $binaryHash1 = $this->hexHashToBinary($hash1);
        $binaryHash2 = $this->hexHashToBinary($hash2);

        $i = 0;
        $count = 0;
        while (isset($binaryHash1[$i]) != '') {
            if ($binaryHash1[$i] != $binaryHash2[$i])
                $count++;
            $i++;
        }
        return $count;
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

    /**
     * @param $hash
     * @return mixed
     */
    public function hexHashToBinary($hash)
    {
        $binaryHash = str_replace('0', '0000', $hash);
        $binaryHash = str_replace('1', '0001', $binaryHash);
        $binaryHash = str_replace('2', '0010', $binaryHash);
        $binaryHash = str_replace('3', '0011', $binaryHash);
        $binaryHash = str_replace('4', '0100', $binaryHash);
        $binaryHash = str_replace('5', '0101', $binaryHash);
        $binaryHash = str_replace('6', '0110', $binaryHash);
        $binaryHash = str_replace('7', '0111', $binaryHash);
        $binaryHash = str_replace('8', '1000', $binaryHash);
        $binaryHash = str_replace('9', '1001', $binaryHash);
        $binaryHash = str_replace('a', '1010', $binaryHash);
        $binaryHash = str_replace('b', '1011', $binaryHash);
        $binaryHash = str_replace('c', '1100', $binaryHash);
        $binaryHash = str_replace('d', '1101', $binaryHash);
        $binaryHash = str_replace('e', '1110', $binaryHash);
        $binaryHash = str_replace('f', '1111', $binaryHash);
        return $binaryHash;
    }

}
