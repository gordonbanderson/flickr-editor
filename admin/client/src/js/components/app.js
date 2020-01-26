import ReactDOM from 'react-dom';
import React from 'react';

//import FlickrPhoto from './FlickrPhoto';
import FlickrSet from './FlickrSet';
import Welcome from './Welcome';

var element = document.getElementById('flickrVisibleWidget');
var setID = element.getAttribute('data-id');
console.log('SET ID', setID);
ReactDOM.render(<FlickrSet ID={setID} />, document.getElementById('reactTest'));
