<?php

Director::addRules(100, array('flickr/$Action/$ID' => 'FlickrController'));
Director::addRules(100, array('flickr/$Action/$ID' => 'FlickrController'));
Director::addRules(100, array('flickr/$Action/$ID/$OtherID' => 'FlickrController'));
Director::addRules(100, array('tags/$Action/$ID' => 'FlickrTagsController'));

//define global path to Components' root folder
if(!defined('FLICKR_EDIT_TOOLS_PATH'))
{
	define('FLICKR_EDIT_TOOLS_PATH', rtrim(basename(dirname(__FILE__))));
}

Object::add_extension('SiteConfig', 'FlickrSiteConfig');
?>