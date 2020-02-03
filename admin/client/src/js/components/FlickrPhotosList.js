import React from 'react';

const handleScroll = ({ currentTarget }, onLoadMore) => {
  if (
    currentTarget.scrollTop + currentTarget.clientHeight >=
    currentTarget.scrollHeight
  ) {
    onLoadMore();
  }
};

const FlickrPhotosList = ({ photos, onLoadMore }) => (
	<div>
    <h2>Photo list</h2>
    <ul
      className="list-group chapter-list"
      onScroll={e => handleScroll(e, onLoadMore)}
    >
      {photos.map(({ ID, Title }) => (
        <li key={ID} className="list-group-item">
          {Title}
        </li>
      ))}
    </ul>
  </div>
);

export default FlickrPhotosList;
