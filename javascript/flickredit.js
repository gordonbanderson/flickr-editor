console.log("FLICKR EDIT");



/*jslint white: true */
(function($) {
	$(document).ready(function() {

		$('#bucketTimeProgressBar').entwine({
			onchange: function() {
				var groupTime = null;

				var buckets = new Array();
				var currentBucket = new Array();
				var bucketDelta = $(this).val() * 1000;
				$('#selectedBucketTime').html($(this).val() + 's');
				var bucketsDOM = $('#buckets');
				bucketsDOM.html('');



				console.log("BUCKET DELTA:" + bucketDelta);

				$('#hiddenImageStore').find('.bucketPhoto').each(function(index) {
					var t = new Date($(this).attr('data-time'));
					//console.log("TIME ATTR:" + $(this).attr('data-time'));
					//console.log(t);
					if(groupTime == null) {
						groupTime = t;
					}


					var delta = Date.parse(t) - Date.parse(groupTime);

					if(delta <= bucketDelta) {
						currentBucket.push($(this));
						//$(this).detach();
					} else {
						// this bucket is completed
						// save it first
						buckets.push(currentBucket);
						currentBucket = new Array();
						currentBucket.push($(this));

						groupTime = t;
					}



				});

				if(currentBucket.length > 0) {
					buckets.push(currentBucket);
				}



				var totalImages = 0;

				for(var i = 0; i <= buckets.length - 1; i++) {
					//bucketsDOM.append('WIBBLE');
					bucketsDOM.append('<tr class="bucket ss-gridfield-item  sized' + buckets[i].length + '" id="bucket_' + i + '"></tr>');
				};

				for(var i = 0; i <= buckets.length - 1; i++) {
					var currentBucketDOM = $('#bucket_' + i);
					totalImages = totalImages + buckets[i].length;
					console.log("HTML");

					//currentBucketDOM.append('<td>');
					var html = "<td>";

					for(var j = 0; j <= buckets[i].length - 1; j++) {
						html = html + buckets[i][j].html();
					};
					html = html + '</td><td><span class="btn-icon-add createBucket">Create</span></td>';
					currentBucketDOM.append(html);

				};

				$('.imgDrag').draggable();

				$('tr.bucket').droppable({
					drop: function(event,ui) {
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
					receive: function(event,ui) {
						console.log("RECEIVED");
					}
				})

				console.log('**** TOTAL IMAGES ****:' + totalImages);

			}
		});

		$('.imgDrag').entwine({
			onclick: function() {
				console.log('bucket img entwine');
				$(this).html('test');
			}
		});

		$('.createBucket').entwine({
			onclick: function() {
				var flickrPhotoIDS = new Array();
				var photoDOM = $(this).parent().parent().find('td').first();
				var ajax_bucket_id = $(this).parent().parent().attr('id');
				ajax_bucket_id = ajax_bucket_id.replace('bucket_', '');
				console.log('AJAX BUCKET ID:'+ajax_bucket_id);

				$(photoDOM).find('img').each(function(index) {
					flickrPhotoIDS.push($(this).attr('data-id'));
				});

				console.log(flickrPhotoIDS);

				var flickr_set_id = $('#buckets').attr('data-flickr-set-id');
				$.ajax({
					url: "/flickr/createBucket/" + flickr_set_id + "/" + flickrPhotoIDS.join()+'?bucket_row='+ajax_bucket_id,
					type: 'POST',
					dataType: 'json',
					//context: document.body,
					success: function(data) {
						console.log(data);

						var bucketRow = $('#bucket_'+data.ajax_bucket_row);
						bucketRow.find('td').first().find('img').each(function(index,element) {
							var imageID  = $(this).attr('data-id');
							imageID = '#flickrPhoto_'+imageID;
							console.log("Removing "+imageID);
							$(imageID).remove();
						});
						//bucketRow.html('Bucket saved');						
						bucketRow.effect("highlight", {}, 1000, function() {
							bucketRow.addClass('hide');
						});

						
					},
					error: function(jqXHR, textStatus, errorThrown) {
						// log the error to the console
						console.log("The following error occured: " + textStatus, errorThrown);
					}
				})



				//console.log(photoDOM);
			}
		});
	});
})(jQuery);