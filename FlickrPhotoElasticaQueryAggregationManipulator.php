<?php
use Elastica\Aggregation\Terms;
use Elastica\Query;

class FlickrPhotoElasticaQueryAggregationManipulator implements ElasticaQueryAggregationManipulator {

	public function manipulateAggregation(&$aggs) {
		// the shutter speeds are of the form decimal number | fraction, keep the latter half
		$shutterSpeeds = $aggs['ShutterSpeed']['buckets'];
		$ctr = 0;
		foreach ($shutterSpeeds as $bucket) {
			$key = $bucket['key'];
			$splits = explode('|', $key);
			$shutterSpeeds[$ctr]['key'] = end($splits);
			$ctr++;
		}

		$aggs['ShutterSpeed']['buckets'] = $shutterSpeeds;
	}


	public function augmentQuery(&$query) {

		// set the order to be taken at in reverse if query is blank other than aggs
		$params = $query->getParams();
		if (!isset($params['query']['filtered']['query']['query_string'])) {
			$query->setSort(array('TakenAt'=> 'desc'));
		}

		// add Aperture aggregate
		$agg1 = new Terms("Aperture");
		$agg1->setField("Aperture");
		$agg1->setSize(0);
		$agg1->setOrder('_term', 'asc');
		$query->addAggregation($agg1);

		// add shutter speed aggregate
		$agg2 = new Terms("ShutterSpeed");
		$agg2->setField("ShutterSpeed");
		$agg2->setSize(0);
		$agg2->setOrder('_term', 'asc');
		$query->addAggregation($agg2);

		// this currently needs to be same as the field name
		// needs fixed
		// Add focal length aggregate, may range this
		$agg3 = new Terms("FocalLength35mm");
		$agg3->setField("FocalLength35mm");
		$agg3->setSize(0);
		$agg3->setOrder('_term', 'asc');
		$query->addAggregation($agg3);

		// add film speed
		$agg4 = new Terms("ISO");
		$agg4->setField("ISO");
		$agg4->setSize(0);
		$agg4->setOrder('_term', 'asc');
		$query->addAggregation($agg4);
	}



}
