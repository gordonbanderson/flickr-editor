!function(e){var t={};function o(r){if(t[r])return t[r].exports;var n=t[r]={i:r,l:!1,exports:{}};return e[r].call(n.exports,n,n.exports,o),n.l=!0,n.exports}o.m=e,o.c=t,o.d=function(e,t,r){o.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:r})},o.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},o.t=function(e,t){if(1&t&&(e=o(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var r=Object.create(null);if(o.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var n in e)o.d(r,n,function(t){return e[t]}.bind(null,n));return r},o.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return o.d(t,"a",t),t},o.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},o.p="/",o(o.s=17)}({17:function(e,t,o){o(18),o(29),o(34),e.exports=o(36)},18:function(e,t){var o;console.log("flickr edit"),(o=jQuery)(document).ready((function(){console.log("Flickr edit doc ready"),o('<div id="previewContainer"><img id="previewImage"/></div>').insertAfter("#Form_ItemEditForm_FlickrPhotos"),o(".flickrSetDraggable").draggable(),o(".flickrSetFolderDroppable").droppable({drop:function(e,t){var r=o(e.target).attr("data-id"),n=o(t.draggable.context),a=n.attr("data-id");console.log("update SiteTree set ParentID="+r+" where ID="+a+";"),console.log("update SiteTree_Live set ParentID="+r+" where ID="+a+";"),n.fadeOut(500,(function(){o(n).remove()}))},receive:function(e,t){console.log("RECEIVED")}}),o(".imageFlickrID").entwine({onchange:function(e){console.log("Value changed"),console.log(o(this));var t=o(this).val();console.log("FLICKR PHOTO ID T1:"+t),""!=t?o.ajax({url:"/flickr/ajaxSearchForPhoto/"+t,type:"POST",dataType:"json",success:function(t){if(t.found){var r="<h4>"+t.title+'</h4><img src="'+t.small_url+'"/>';o(e.target).parent().find(".chosenFlickrImage").first().html(r),o(e.target).parent().find(".flickrPhotoSelectionField").first().val(t.id)}},error:function(e,t,o){console.log("The following error occured: "+t,o)}}):console.log("Ignoring blank flickr photo id")}}),o("#batchUpdatePhotographs").entwine({onclick:function(e){var t=o("#buckets").attr("data-flickr-set-id"),r=o('input[name="BatchTitle"]').val(),n=o('textarea[name="BatchDescription"]').val(),a=o('textarea[name="BatchTags"]').val();console.log(r),console.log(n),console.log(a),o("#batchUpdatePhotographs").val("Please wait, updating photographs..."),o.ajax({url:"/flickr/batchUpdateSet/"+t,type:"POST",dataType:"json",data:"&BatchTitle="+r+"&BatchDescription="+n+"&BatchTags="+a,success:function(e){console.log(e),o("#batchUpdatePhotographs").val("Batch Update"),o(e.number_of_images_updated)},error:function(e,t,o){console.log("The following error occured: "+t,o)}})}}),o(".flickrThumbnail").entwine({onmouseenter:function(e){var t=o("#previewImage"),r=o(e.target);return console.log(t.width(),t.height()),t.attr("src",r.attr("data-flickr-preview-url")),t.addClass("hoverLarge"),t.width(r.attr("data-flickr-preview-width")),t.height(r.attr("data-flickr-preview-height")),t.removeClass("horizontal"),t.removeClass("vertical"),t.width()>t.height()?t.addClass("horizontal"):t.addClass("vertical"),e.preventDefault(),!1},onmouseleave:function(e){var t=o("#previewImage"),r=o(e.target);return t.attr("src",r.attr("data-flickr-thumbnail-url")),t.removeClass("hoverLarge"),e.preventDefault(),!1}}),o("#bucketTimeProgressBar").entwine({onchange:function(){var e=null,t=new Array,r=new Array,n=1e3*o(this).val();o("#selectedBucketTime").html(o(this).val()+"s");var a=o("#buckets");a.html(""),o("#hiddenImageStore").find(".bucketPhoto").each((function(a){var i=new Date(o(this).attr("data-time"));null==e&&(e=i),Date.parse(i)-Date.parse(e)<=n?r.push(o(this)):(t.push(r),(r=new Array).push(o(this)),e=i)})),r.length>0&&t.push(r);for(var i=0,c=0;c<=t.length-1;c++)a.append('<tr class="bucket ss-gridfield-item  sized'+t[c].length+'" id="bucket_'+c+'"></tr>');for(c=0;c<=t.length-1;c++){var l=o("#bucket_"+c);i+=t[c].length,console.log("HTML");for(var s="<td>",d=0;d<=t[c].length-1;d++)s+=t[c][d].html();s+='</td><td><span class="btn-icon-add btn btn-primary font-icon-folder-add  createBucket">Create</span></td>',l.append(s)}o(".imgDrag").draggable(),o("tr.bucket").droppable({drop:function(e,t){console.log("Dropped"),console.log(e),console.log(t);var r=o(t.draggable.context);console.log("Dragged image:"),console.log(r),console.log("Dropped on:"),console.log(e.target),r.detach(),r.css("left","0px"),r.css("top","0px"),o(e.target).find("td").first().append(r)},receive:function(e,t){console.log("RECEIVED")}}),console.log("**** TOTAL IMAGES ****:"+i)}}),o(".imgDrag").entwine({onclick:function(){console.log("bucket img entwine"),o(this).html("test")}}),o("#changeMainPictureButton").entwine({onclick:function(){var e=o(this),t=e.attr("data-flickr-set-id"),r=e.attr("data-flickr-photo-id");o.ajax({url:"/flickr/changeFlickrSetMainImage/"+t+"/"+r,type:"POST",dataType:"json",success:function(e){},error:function(e,t,o){console.log("The following error occured: "+t,o)}})}}),o(".createBucket").entwine({onclick:function(){var e=new Array,t=o(this).parent().parent().find("td").first(),r=o(this).parent().parent().attr("id");r=r.replace("bucket_",""),console.log("AJAX BUCKET ID:"+r),o(t).find("img").each((function(t){e.push(o(this).attr("data-id"))})),console.log(e);var n=o("#buckets").attr("data-flickr-set-id");o.ajax({url:"/flickr/createBucket/"+n+"/"+e.join()+"?bucket_row="+r,type:"POST",dataType:"json",success:function(e){console.log(e);var t=o("#bucket_"+e.ajax_bucket_row);t.find("td").first().find("img").each((function(e,t){var r=o(this).attr("data-id");r="#flickrPhoto_"+r,console.log("Removing "+r),o(r).remove()})),t.effect("highlight",{},1e3,(function(){t.addClass("hide")}))},error:function(e,t,o){console.log("The following error occured: "+t,o)}})}})}))},29:function(e,t){},34:function(e,t){},36:function(e,t){}});
//# sourceMappingURL=flickredit.js.map