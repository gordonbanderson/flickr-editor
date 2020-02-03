import React from 'react';
import '../../css/flickrreact.scss';

import FlickrPhotos from "./FlickrPhotos";


class FlickrSet extends React.Component {
	state = { limit:10, offset: 0 };

	constructor(props) {
		super(props);
		console.log(this.props);
		this.prevPage = this.prevPage.bind(this);
		this.nextPage = this.nextPage.bind(this);
	}

	nextPage(e) {
		this.setState({
			offset: this.state.offset+this.state.limit
		});
	}

	prevPage(e) {
		this.setState({
			offset: this.state.offset-this.state.limit
		});
	}

	render() {
		console.log('Set id', this.props.ID);
		return (
				<FlickrPhotos FlickrSetID={this.props.ID} Limit={this.state.limit} Offset={this.state.offset}
							  onNextPage={this.nextPage} onPrevPage={this.prevPage}/>


		);
	}

}


export default FlickrSet;
