!function(t){var e={};function r(a){if(e[a])return e[a].exports;var n=e[a]={i:a,l:!1,exports:{}};return t[a].call(n.exports,n,n.exports,r),n.l=!0,n.exports}r.m=t,r.c=e,r.d=function(t,e,a){r.o(t,e)||Object.defineProperty(t,e,{enumerable:!0,get:a})},r.r=function(t){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},r.t=function(t,e){if(1&e&&(t=r(t)),8&e)return t;if(4&e&&"object"==typeof t&&t&&t.__esModule)return t;var a=Object.create(null);if(r.r(a),Object.defineProperty(a,"default",{enumerable:!0,value:t}),2&e&&"string"!=typeof t)for(var n in t)r.d(a,n,function(e){return t[e]}.bind(null,n));return a},r.n=function(t){var e=t&&t.__esModule?function(){return t.default}:function(){return t};return r.d(e,"a",e),e},r.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},r.p="/",r(r.s=17)}({17:function(t,e,r){r(18),r(29),r(34),t.exports=r(36)},18:function(t,e){var r;(r=jQuery)(document).ready((function(){r('<div id="previewContainer"><img id="previewImage"/></div>').insertAfter("#Form_ItemEditForm_FlickrPhotos"),r(".flickrSetDraggable").draggable(),r(".flickrSetFolderDroppable").droppable({drop:function(t,e){r(t.target).attr("data-id");var a=r(e.draggable.context);a.attr("data-id"),a.fadeOut(500,(function(){r(a).remove()}))},receive:function(t,e){}}),r(".imageFlickrID").entwine({onchange:function(t){var e=r(this).val();""!=e&&r.ajax({url:"/flickr/ajaxSearchForPhoto/"+e,type:"POST",dataType:"json",success:function(e){if(e.found){var a="<h4>"+e.title+'</h4><img src="'+e.small_url+'"/>';r(t.target).parent().find(".chosenFlickrImage").first().html(a),r(t.target).parent().find(".flickrPhotoSelectionField").first().val(e.id)}},error:function(t,e,r){}})}}),r("#batchUpdatePhotographs").entwine({onclick:function(t){var e=r("#buckets").attr("data-flickr-set-id"),a=r('input[name="BatchTitle"]').val(),n=r('textarea[name="BatchDescription"]').val(),i=r('textarea[name="BatchTags"]').val();r("#batchUpdatePhotographs").val("Please wait, updating photographs..."),r.ajax({url:"/flickr/batchUpdateSet/"+e,type:"POST",dataType:"json",data:"&BatchTitle="+a+"&BatchDescription="+n+"&BatchTags="+i,success:function(t){r("#batchUpdatePhotographs").val("Batch Update"),r(t.number_of_images_updated)},error:function(t,e,r){}})}}),r(".flickrThumbnail").entwine({onmouseenter:function(t){var e=r("#previewImage"),a=r(t.target);return e.attr("src",a.attr("data-flickr-preview-url")),e.addClass("hoverLarge"),e.width(a.attr("data-flickr-preview-width")),e.height(a.attr("data-flickr-preview-height")),e.removeClass("horizontal"),e.removeClass("vertical"),e.width()>e.height()?e.addClass("horizontal"):e.addClass("vertical"),t.preventDefault(),!1},onmouseleave:function(t){var e=r("#previewImage"),a=r(t.target);return e.attr("src",a.attr("data-flickr-thumbnail-url")),e.removeClass("hoverLarge"),t.preventDefault(),!1}}),r("#bucketTimeProgressBar").entwine({onchange:function(){var t=null,e=new Array,a=new Array,n=1e3*r(this).val();r("#selectedBucketTime").html(r(this).val()+"s");var i=r("#buckets");i.html(""),r("#hiddenImageStore").find(".bucketPhoto").each((function(i){var o=new Date(r(this).attr("data-time"));null==t&&(t=o),Date.parse(o)-Date.parse(t)<=n?a.push(r(this)):(e.push(a),(a=new Array).push(r(this)),t=o)})),a.length>0&&e.push(a);for(var o=0;o<=e.length-1;o++)i.append('<tr class="bucket ss-gridfield-item  sized'+e[o].length+'" id="bucket_'+o+'"></tr>');for(o=0;o<=e.length-1;o++){var c=r("#bucket_"+o);e[o].length;for(var l="<td>",u=0;u<=e[o].length-1;u++)l+=e[o][u].html();l+='</td><td><span class="btn-icon-add btn btn-primary font-icon-folder-add  createBucket">Create</span></td>',c.append(l)}r(".imgDrag").draggable(),r("tr.bucket").droppable({drop:function(t,e){var a=r(e.draggable.context);a.detach(),a.css("left","0px"),a.css("top","0px"),r(t.target).find("td").first().append(a)},receive:function(t,e){}})}}),r(".imgDrag").entwine({onclick:function(){r(this).html("test")}}),r(".createBucket").entwine({onclick:function(){var t=new Array,e=r(this).parent().parent().find("td").first(),a=r(this).parent().parent().attr("id");a=a.replace("bucket_",""),r(e).find("img").each((function(e){t.push(r(this).attr("data-id"))}));var n=r("#buckets").attr("data-flickr-set-id");r.ajax({url:"/flickr/createBucket/"+n+"/"+t.join()+"?bucket_row="+a,type:"POST",dataType:"json",success:function(t){var e=r("#bucket_"+t.ajax_bucket_row);e.find("td").first().find("img").each((function(t,e){var a=r(this).attr("data-id");r(a="#flickrPhoto_"+a).remove()})),e.effect("highlight",{},1e3,(function(){e.addClass("hide")}))},error:function(t,e,r){}})}})}))},29:function(t,e){},34:function(t,e){},36:function(t,e){}});
//# sourceMappingURL=flickredit.js.map