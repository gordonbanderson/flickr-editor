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
					console.log("TIME ATTR:" + $(this).attr('data-time'));
					console.log(t);
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

				console.log('**** TOTAL IMAGES ****:' + totalImages);

			}
		});

		// toggle boxes on drop down change
		$('#bucketTimeProgressBarNOT').change(function(e) {



		});
	});
})(jQuery);