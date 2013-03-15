<?php

Director::addRules(100, array('tags/$Action/$ID' => 'FlickrTagsController'));

//Director::addRules(100, array('flickrexport/$Action/$ID' => 'FlickrExportController'));


ShortcodeParser::get('default')->register('FlickrPhoto',array('FlickrPhotoShortCodeHandler','parse_flickr'));

//define global path to Components' root folder
if(!defined('FLICKR_EDIT_TOOLS_PATH'))
{
	define('FLICKR_EDIT_TOOLS_PATH', rtrim(basename(dirname(__FILE__))));
}

Object::add_extension('SiteConfig', 'FlickrSiteConfig');
Object::add_extension('FlickrSet','MapLayerExtension');


//Object::add_extension('FlickrPhoto','MapLayerExtension');

?>