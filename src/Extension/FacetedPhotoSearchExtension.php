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
    public function postProcessFacetResults( $token, &$tokenFacets) {
        print_r($tokenFacets);
        //return $tokenFacets;
    }

}
