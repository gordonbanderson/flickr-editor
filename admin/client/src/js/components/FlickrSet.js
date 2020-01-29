import React from 'react';
import '../../css/flickrreact.scss';


import FlickrPhotos from "./FlickrPhotos";


class FlickrSet extends React.Component {
	state = { count: 0 };
	constructor(props) {
		super(props);
		console.log(this.props);
		this.state = {};

		//this.loadImages = this.loadImages.bind(this);

		//console.log('About to call load images....')

		//this.fetchPhotos(this.props.ID);


		// this does not work when navigating between tabs


	}




	render() {
		console.log('Set id', this.props.ID);
		return (
			<div className="visibility flickrSet">
				<FlickrPhotos FlickrSetID={this.props.ID}/>
			</div>

		);
	}





}


export default FlickrSet;
