<?php
class FlickrTagElasticaIndexingExtension extends Extension implements ElasticaIndexingHelperInterface {

	//private static $ctr = 0;

		/**
		 * Add a mapping for the location of the photograph
		 */
		public static function updateElasticsearchMapping(\Elastica\Type\Mapping $mapping)
        {
        	// get the properties of the individual fields as an array
        	$properties = $mapping->getProperties();

        	// enable tags to be faceted
        	$properties['RawValue'] = array(
        		'type' => 'string',
        		'index' => 'not_analyzed'
			);

        	// set the new properties on the mapping
        	$mapping->setProperties($properties);

            return $mapping;
        }


		/**
		 * Populate elastica with the location of the photograph
		 * @param  \Elastica\Document $document Representation of an Elastic Search document
		 * @return \Elastica\Document modified version of the document
		 */
		public function updateElasticsearchDocument(\Elastica\Document $document)
		{
		    return $document;
		}
}
