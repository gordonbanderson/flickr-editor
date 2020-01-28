import ReactDOM from 'react-dom';
import React from 'react';

//import FlickrPhoto from './FlickrPhoto';
import FlickrSet from './FlickrSet';
import Welcome from './Welcome';
import {Provider} from "react-redux";
import FlickrPhotoPreview from "./FlickrPhotoPreview";
import {createStore} from "redux";


function reducer(state, action) {
	console.log('reducer', state, action);
	return state;
}

const store = createStore(reducer);



var element = document.getElementById('flickrVisibleWidget');
var setID = element.getAttribute('data-id');
console.log('SET ID', setID);

// this provides for a store, see https://daveceddia.com/redux-tutorial/
const App = () => (
	<Provider store={store}>
		<FlickrSet ID={setID} />
		<FlickrPhotoPreview />
	</Provider>
);

ReactDOM.render(<App />, document.getElementById('reactTest'));

