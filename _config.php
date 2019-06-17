<?php

// @todo route config
//\SilverStripe\Control\Director::addRules(100, array('tags/$Action/$ID' => 'FlickrTagsController'));

//Director::addRules(100, array('flickrexport/$Action/$ID' => 'FlickrExportController'));


use SilverStripe\View\Parsers\ShortcodeParser;

ShortcodeParser::get('default')->register('FlickrPhoto',array('Suilven\Flickr\ShortCode\FlickrPhotoShortCodeHandler','parse_flickr'));

//define global path to Components' root folder
if(!defined('FLICKR_EDIT_TOOLS_PATH'))
{
	define('FLICKR_EDIT_TOOLS_PATH', rtrim(basename(dirname(__FILE__))));
}

// @todo replace with new gis module
// Object::add_extension('FlickrSet','MapLayerExtension');
