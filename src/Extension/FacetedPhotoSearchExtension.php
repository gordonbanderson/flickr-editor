<?php
namespace Suilven\Flickr\Extension;

use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Core\Extension;
use Suilven\Flickr\Model\Flickr\FlickrAuthor;
use Suilven\Flickr\Model\Flickr\FlickrPhoto;

class FacetedPhotoSearchExtension extends Extension
{

    public function postProcessFacetTitle(&$facetTitle) {

        $titles = [
          'aperture' => 'Aperture',
          'shutterspeed' => 'Shutter Speed',
          'iso' => 'ISO',
          'flickrtagid' => 'Tags'
        ];

       if (in_array($facetTitle, array_keys($titles))) {
           $facetTitle = $titles[$facetTitle];
       }

    }


    /**
     * @param $token - the name of the facet, e.g. aperture
     * @param $tokenFacets - the facets for this token
     * @return array - massaged title and facets
     */
    public function postProcessFacetResults( $token, $tokenFacets) {
        $result = [];

        foreach ($tokenFacets as $facet) {
            $value = $facet['Value'];

            switch($token)
            {
                case 'Aperture':
                    //$value = $facet['Value'];

                    $value = str_replace('.0', '', $value);
                    //$aperture = str_replace('000000', '', $aperture);
                    $value = str_replace('00000', '', $value);

                    if (empty($value)) {
                        $facet['Value'] = 'Unknown';
                    } else {
                        $facet['Value'] = 'f' . $value;
                    }

                    break;
                case 'Shutter Speed':
                    if (empty($value)) {
                        $facet['Value'] = 'Unknown';

                    }

                case 'ISO':
                    if (empty($value)) {
                        $facet['Value'] = 'Unknown';

                    }
                default:
                    // do nothing
                    break;
            }
            array_push($result, $facet);
        }





        return $result;
    }

}
