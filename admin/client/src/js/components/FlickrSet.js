import React from 'react';
import '../../css/flickrreact.scss';

import FlickrPhotosListQuery from "./FlickrPhotosListQuery";


class FlickrSet extends React.Component {
	state = { count: 0 };
	constructor(props) {
		super(props);
		console.log(this.props);
		this.state = {};
	}

	render() {
		console.log('Set id', this.props.ID);
		return (
			<div className="visibility flickrSet">
				<FlickrPhotosListQuery FlickrSetID={this.props.ID}/>
			</div>

		);
	}

}


export default FlickrSet;
