<?php

// @todo route config
//\SilverStripe\Control\Director::addRules(100, array('tags/$Action/$ID' => 'FlickrTagsController'));

//Director::addRules(100, array('flickrexport/$Action/$ID' => 'FlickrExportController'));


use SilverStripe\View\Parsers\ShortcodeParser;

ShortcodeParser::get('default')->register('FlickrPhoto',array('Suilven\Flickr\ShortCode\FlickrPhotoShortCodeHandler','parse_flickr'));
ShortcodeParser::get('default')->register('FlickrPhotoSequence',array('Suilven\Flickr\ShortCode\FlickrPhotoSequenceShortCodeHandler','parse_flickr'));



// @todo replace with new gis module
// Object::add_extension('FlickrSet','MapLayerExtension');
