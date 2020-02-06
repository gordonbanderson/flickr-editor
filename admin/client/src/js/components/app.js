import ReactDOM from 'react-dom';
import React from 'react';
import FlickrSet from './FlickrSet';
import FlickrPhotoPreview from "./FlickrPhotoPreview";
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




var element = document.getElementById('flickrVisibleWidget');
var setID = element.getAttribute('data-id');
var securityToken = element.getAttribute('data-security-token');
console.log(securityToken);
console.log('SET ID', setID);

const VisibleImagesApp = () => (
	<ApolloProvider client={client}>
		<div className="row">
				<FlickrSet ID={setID} />
				<FlickrPhotoPreview FlickrSetID={setID}/>
		</div>
	</ApolloProvider>
);

ReactDOM.render(<VisibleImagesApp />, document.getElementById('visibleImageSelectorApp'));
