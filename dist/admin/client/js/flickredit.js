/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./admin/client/src/css/flickredit.scss":
/*!**********************************************!*\
  !*** ./admin/client/src/css/flickredit.scss ***!
  \**********************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ "./admin/client/src/js/flickredit.js":
/*!*******************************************!*\
  !*** ./admin/client/src/js/flickredit.js ***!
  \*******************************************/
/*! no static exports found */
/***/ (function(module, exports) {

/*jslint white: true */
console.log('flickr edit');

(function ($) {
  $(document).ready(function () {
    console.log("Flickr edit doc ready");
    $('<div id="previewContainer"><img id="previewImage"/></div>').insertAfter('#Form_ItemEditForm_FlickrPhotos');
    $('.flickrSetDraggable').draggable();
    $('.flickrSetFolderDroppable').droppable({
      drop: function drop(event, ui) {
        var parentID = $(event.target).attr('data-id');
        var flickrSet = $(ui.draggable.context);
        var sourceID = flickrSet.attr('data-id');
        console.log("update SiteTree set ParentID=" + parentID + ' where ID=' + sourceID + ';');
        console.log("update SiteTree_Live set ParentID=" + parentID + ' where ID=' + sourceID + ';');
        flickrSet.fadeOut(500, function () {
          $(flickrSet).remove();
        });
        /*
        console.log(event);
        console.log(ui);
        	var draggedImage = $(ui.draggable.context);
        console.log("Dragged image:");
        console.log(draggedImage);
        	console.log("Dropped on:");
        console.log(event.target);
        	draggedImage.detach();
        draggedImage.css('left', '0px');
        draggedImage.css('top', '0px');
        $(event.target).find('td').first().append(draggedImage);
        */
      },
      receive: function receive(event, ui) {
        console.log("RECEIVED");
      }
    });
    /*
    Search for image by ID
    */

    $('.imageFlickrID').entwine({
      onchange: function onchange(e) {
        console.log("Value changed");
        console.log($(this));
        var flickr_photo_id = $(this).val();
        console.log("FLICKR PHOTO ID T1:" + flickr_photo_id);

        if (flickr_photo_id != '') {
          $.ajax({
            url: "/flickr/ajaxSearchForPhoto/" + flickr_photo_id,
            type: 'POST',
            dataType: 'json',
            //data: '&BatchTitle='+batchTitle+'&BatchDescription='+batchDescription+'&BatchTags='+batchTags,
            //context: document.body,
            success: function success(data) {
              if (data.found) {
                var imageHtml = '<h4>' + data.title + '</h4><img src="' + data.small_url + '"/>';
                $(e.target).parent().find('.chosenFlickrImage').first().html(imageHtml);
                $(e.target).parent().find('.flickrPhotoSelectionField').first().val(data.id);
              }
            },
            error: function error(jqXHR, textStatus, errorThrown) {
              // log the error to the console
              console.log("The following error occured: " + textStatus, errorThrown);
            }
          });
        } else {
          console.log("Ignoring blank flickr photo id");
        }
      }
    });
    $('#batchUpdatePhotographs').entwine({
      onclick: function onclick(e) {
        var flickr_set_id = $('#buckets').attr('data-flickr-set-id');
        var batchTitle = $('input[name="BatchTitle"]').val();
        var batchDescription = $('textarea[name="BatchDescription"]').val();
        var batchTags = $('textarea[name="BatchTags"]').val();
        console.log(batchTitle);
        console.log(batchDescription);
        console.log(batchTags);
        $('#batchUpdatePhotographs').val('Please wait, updating photographs...'); //statusMessage('Batch updating photographs in this set');

        $.ajax({
          url: "/flickr/batchUpdateSet/" + flickr_set_id,
          type: 'POST',
          dataType: 'json',
          data: '&BatchTitle=' + batchTitle + '&BatchDescription=' + batchDescription + '&BatchTags=' + batchTags,
          //context: document.body,
          success: function success(data) {
            console.log(data);
            $('#batchUpdatePhotographs').val('Batch Update');
            var numberOfImages = $(data.number_of_images_updated); //statusMessage('Batch update completed ' + numberOfImages + ' updated');
          },
          error: function error(jqXHR, textStatus, errorThrown) {
            // log the error to the console
            console.log("The following error occured: " + textStatus, errorThrown);
          }
        });
      }
    });
    $('.flickrThumbnail').entwine({
      onmouseenter: function onmouseenter(e) {
        var image = $('#previewImage');
        var srcImage = $(e.target);
        console.log(image.width(), image.height());
        image.attr('src', srcImage.attr('data-flickr-preview-url'));
        image.addClass('hoverLarge');
        image.width(srcImage.attr('data-flickr-preview-width'));
        image.height(srcImage.attr('data-flickr-preview-height'));
        image.removeClass('horizontal');
        image.removeClass('vertical');

        if (image.width() > image.height()) {
          image.addClass('horizontal');
        } else {
          image.addClass('vertical');
        }

        e.preventDefault();
        return false;
      },
      onmouseleave: function onmouseleave(e) {
        var image = $('#previewImage');
        var srcImage = $(e.target);
        image.attr('src', srcImage.attr('data-flickr-thumbnail-url'));
        image.removeClass('hoverLarge');
        e.preventDefault();
        return false;
      }
    });
    $('#bucketTimeProgressBar').entwine({
      onchange: function onchange() {
        var groupTime = null;
        var buckets = new Array();
        var currentBucket = new Array();
        var bucketDelta = $(this).val() * 1000;
        $('#selectedBucketTime').html($(this).val() + 's');
        var bucketsDOM = $('#buckets');
        bucketsDOM.html('');
        $('#hiddenImageStore').find('.bucketPhoto').each(function (index) {
          var t = new Date($(this).attr('data-time')); //console.log("TIME ATTR:" + $(this).attr('data-time'));
          //console.log(t);

          if (groupTime == null) {
            groupTime = t;
          }

          var delta = Date.parse(t) - Date.parse(groupTime);

          if (delta <= bucketDelta) {
            currentBucket.push($(this)); //$(this).detach();
          } else {
            // this bucket is completed
            // save it first
            buckets.push(currentBucket);
            currentBucket = new Array();
            currentBucket.push($(this));
            groupTime = t;
          }
        });

        if (currentBucket.length > 0) {
          buckets.push(currentBucket);
        }

        var totalImages = 0;

        for (var i = 0; i <= buckets.length - 1; i++) {
          //bucketsDOM.append('WIBBLE');
          bucketsDOM.append('<tr class="bucket ss-gridfield-item  sized' + buckets[i].length + '" id="bucket_' + i + '"></tr>');
        }

        ;

        for (var i = 0; i <= buckets.length - 1; i++) {
          var currentBucketDOM = $('#bucket_' + i);
          totalImages = totalImages + buckets[i].length;
          console.log("HTML"); //currentBucketDOM.append('<td>');

          var html = "<td>";

          for (var j = 0; j <= buckets[i].length - 1; j++) {
            html = html + buckets[i][j].html();
          }

          ;
          html = html + '</td><td><span class="btn-icon-add btn btn-primary font-icon-folder-add  createBucket">Create</span></td>';
          currentBucketDOM.append(html);
        }

        ;
        $('.imgDrag').draggable();
        $('tr.bucket').droppable({
          drop: function drop(event, ui) {
            console.log("Dropped");
            console.log(event);
            console.log(ui);
            var draggedImage = $(ui.draggable.context);
            console.log("Dragged image:");
            console.log(draggedImage);
            console.log("Dropped on:");
            console.log(event.target);
            draggedImage.detach();
            draggedImage.css('left', '0px');
            draggedImage.css('top', '0px');
            $(event.target).find('td').first().append(draggedImage);
          },
          receive: function receive(event, ui) {
            console.log("RECEIVED");
          }
        });
        console.log('**** TOTAL IMAGES ****:' + totalImages);
      }
    });
    $('.imgDrag').entwine({
      onclick: function onclick() {
        console.log('bucket img entwine');
        $(this).html('test');
      }
    });
    $('#changeMainPictureButton').entwine({
      onclick: function onclick() {
        var button = $(this);
        var flickr_set_id = button.attr('data-flickr-set-id');
        var fpid = button.attr('data-flickr-photo-id');
        $.ajax({
          url: "/flickr/changeFlickrSetMainImage/" + flickr_set_id + "/" + fpid,
          type: 'POST',
          dataType: 'json',
          //context: document.body,
          success: function success(data) {//statusMessage('Main picture successfully updated');
          },
          error: function error(jqXHR, textStatus, errorThrown) {
            // log the error to the console
            console.log("The following error occured: " + textStatus, errorThrown); //statusMessage('An error occurred - '+ textStatus);
          }
        });
      }
    });
    $('.createBucket').entwine({
      onclick: function onclick() {
        var flickrPhotoIDS = new Array();
        var photoDOM = $(this).parent().parent().find('td').first();
        var ajax_bucket_id = $(this).parent().parent().attr('id');
        ajax_bucket_id = ajax_bucket_id.replace('bucket_', '');
        console.log('AJAX BUCKET ID:' + ajax_bucket_id);
        $(photoDOM).find('img').each(function (index) {
          flickrPhotoIDS.push($(this).attr('data-id'));
        });
        console.log(flickrPhotoIDS);
        var flickr_set_id = $('#buckets').attr('data-flickr-set-id');
        $.ajax({
          url: "/flickr/createBucket/" + flickr_set_id + "/" + flickrPhotoIDS.join() + '?bucket_row=' + ajax_bucket_id,
          type: 'POST',
          dataType: 'json',
          //context: document.body,
          success: function success(data) {
            console.log(data);
            var bucketRow = $('#bucket_' + data.ajax_bucket_row);
            bucketRow.find('td').first().find('img').each(function (index, element) {
              var imageID = $(this).attr('data-id');
              imageID = '#flickrPhoto_' + imageID;
              console.log("Removing " + imageID);
              $(imageID).remove();
            }); //bucketRow.html('Bucket saved');

            bucketRow.effect("highlight", {}, 1000, function () {
              bucketRow.addClass('hide');
            });
          },
          error: function error(jqXHR, textStatus, errorThrown) {
            // log the error to the console
            console.log("The following error occured: " + textStatus, errorThrown);
          }
        }); //console.log(photoDOM);
      }
    });
  });
})(jQuery);

/***/ }),

/***/ "./client/src/css/flickr.scss":
/*!************************************!*\
  !*** ./client/src/css/flickr.scss ***!
  \************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ 0:
/*!*********************************************************************************************************************!*\
  !*** multi ./admin/client/src/js/flickredit.js ./client/src/css/flickr.scss ./admin/client/src/css/flickredit.scss ***!
  \*********************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(/*! /var/www/flickr/admin/client/src/js/flickredit.js */"./admin/client/src/js/flickredit.js");
__webpack_require__(/*! /var/www/flickr/client/src/css/flickr.scss */"./client/src/css/flickr.scss");
module.exports = __webpack_require__(/*! /var/www/flickr/admin/client/src/css/flickredit.scss */"./admin/client/src/css/flickredit.scss");


/***/ })

/******/ });