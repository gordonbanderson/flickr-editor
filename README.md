# Flickr Editor
[![Build Status](https://travis-ci.org/gordonbanderson/flickr-editor.svg?branch=upgradess4)](https://travis-ci.org/gordonbanderson/flickr-editor)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/gordonbanderson/flickr-editor/badges/quality-score.png?b=upgradess4)](https://scrutinizer-ci.com/g/gordonbanderson/flickr-editor/?branch=upgradess4)
[![Code Coverage](https://scrutinizer-ci.com/g/gordonbanderson/flickr-editor/badges/coverage.png?b=upgradess4)](https://scrutinizer-ci.com/g/gordonbanderson/flickr-editor/?branch=upgradess4)
[![Build Status](https://scrutinizer-ci.com/g/gordonbanderson/flickr-editor/badges/build.png?b=upgradess4)](https://scrutinizer-ci.com/g/gordonbanderson/flickr-editor/build-status/master)
[![codecov.io](https://codecov.io/github/gordonbanderson/flickr-editor/coverage.svg?branch=upgradess4)](https://codecov.io/github/gordonbanderson/flickr-editor?branch=upgradess4)

[![Latest Stable Version](https://poser.pugx.org/weboftalent/flickr/version)](https://packagist.org/packages/weboftalent/flickr)
[![Latest Unstable Version](https://poser.pugx.org/weboftalent/flickr/v/unstable)](//packagist.org/packages/weboftalent/flickr)
[![Total Downloads](https://poser.pugx.org/weboftalent/flickr/downloads)](https://packagist.org/packages/weboftalent/flickr)
[![License](https://poser.pugx.org/weboftalent/flickr/license)](https://packagist.org/packages/weboftalent/flickr)
[![Monthly Downloads](https://poser.pugx.org/weboftalent/flickr/d/monthly)](https://packagist.org/packages/weboftalent/flickr)
[![Daily Downloads](https://poser.pugx.org/weboftalent/flickr/d/daily)](https://packagist.org/packages/weboftalent/flickr)

[![Dependency Status](https://www.versioneye.com/php/weboftalent:flickr/badge.svg)](https://www.versioneye.com/php/weboftalent:flickr)
[![Reference Status](https://www.versioneye.com/php/weboftalent:flickr/reference_badge.svg?style=flat)](https://www.versioneye.com/php/weboftalent:flickr/references)

![codecov.io](https://codecov.io/github/gordonbanderson/flickr-editor/branch.svg?branch=upgradess4)

## Maintainers

* Gordon Anderson (Nickname: nontgor)
	<gordon.b.anderson@gmail.com>

##NOTE!!!!
Module has been used by myself for some time but needs love, testing, code layout fixing.

##Introduction

* Import Flickr photos into a SilverStripe site
* Provides interface to edit Flickr photos in bulk, with maps in addition
* Command line tool to update edited images via the Flickr API (after authentication)


# Get Your Flickr ID
https://www.webfx.com/tools/idgettr/

# Get Oauth

# Add a FlickrSetFolder

# Import a Set
```bash
vendor/bin/sake dev/tasks/import-flickr-set id=72157709345351706 path='photos'
```

# Update Flickr Metadata from SilverStripe Edits
```bash
vendor/bin/sake dev/tasks/update-flickr-set-metadata id=72157709345351706
```

 210  vendor/bin/sake dev/tasks/import-flickr-set id=72157709827062467 path='photos'
  211  vendor/bin/sake dev/tasks
  212  vendor/bin/sake dev/tasks/download-flickr-set-thumbs id=72157709827062467
  213  vendor/bin/sake dev/tasks
  214  vendor/bin/sake dev/tasks/create-flickr-set-sprite  id=72157709827062467

pngquant --force --ext .png  public/flickr/sprites/72157709827062467/icon-sprite@2x.png 


##Requirements
* SilverStripe 4

##TODO
* Populate tests
