import ReactDOM from 'react-dom';
import React from 'react';
//import FlickrPhoto from './FlickrPhoto';
import FlickrSet from './FlickrSet';
import {Provider} from "react-redux";
import FlickrPhotoPreview from "./FlickrPhotoPreview";
import thunk from 'redux-thunk';
import {applyMiddleware, createStore} from 'redux';
import {ApolloProvider} from '@apollo/react-hooks';
import {client} from "./transport";

const reducer = (state, action) => {
	state === undefined ? (state = { count: 0 }) : null; //Definition for beginning state value and its structure

	switch (action.type) {
		case "INCREMENT":
			return Object.assign({}, state, { count: state.count + 1 }); //Using non-mutating method
		case "DECREMENT":
			return Object.assign({}, state, { count: state.count - 1 });
		default:
			return state;
	}
};


const store = createStore(reducer, applyMiddleware(thunk));



var element = document.getElementById('flickrVisibleWidget');
var setID = element.getAttribute('data-id');
console.log('SET ID', setID);

// this provides for a store, see https://daveceddia.com/redux-tutorial/
const App = () => (
	<ApolloProvider client={client}>
	<Provider store={store}>
		<FlickrSet ID={setID} />
		<FlickrPhotoPreview />
	</Provider>
	</ApolloProvider>
);

ReactDOM.render(<App />, document.getElementById('reactTest'));

