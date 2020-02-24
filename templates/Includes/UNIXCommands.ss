<h1>Unix Commands</h1>
This is a ready reckoner of UNIX commands for image processing

<p>Get Flickr oAuth tokens:</p>
<pre>vendor/bin/sake dev/tasks/get-flickr-oauth</pre>

<p>Calculate perceptive hashes for a Flickr Set: </p>
<pre>vendor/bin/sake dev/tasks/calculate-perceptive-hash id=$ID</pre>

<p>Create a video using sequences identified via perceptive hashes: </p>
<pre>vendor/bin/sake dev/tasks/create-video-from-perceptive-hash id=$ID</pre>

<p>Create buckets for a flickr set based on perceptive hash:</p>
<pre>vendor/bin/sake dev/tasks/buckets-from-perceptive-hash id=$ID</pre>

<p>Download thumbnail images of a Flickr Set:</p>
<pre>vendor/bin/sake dev/tasks/download-flickr-set-thumbs id=$ID</pre>

<p>Download visible images of a Flickr Set:</p>
<pre>vendor/bin/sake dev/tasks/download-flickr-set-for-facebook id=$ID</pre>

<p>Create a CSS sprite from a set's thumbnails:</p>
<pre>vendor/bin/sake dev/tasks/create-flickr-set-sprite id=$ID</pre>

<p>Import a Flickr gallery:</p>
<pre>vendor/bin/sake dev/tasks/import-flickr-gallery id=$ID</pre>

<p>Import a Flickr set:</p>
<pre>vendor/bin/sake dev/tasks/import-flickr-set id=$ID</pre>

<p>Update Flickr metadata:</p>
<pre>vendor/bin/sake dev/tasks/update-flickr-set-metadata id=$ID</pre>
