import ReactDOM from 'react-dom';
import React from 'react';

class FlickrSet extends React.Component {
	constructor(props) {
		super(props);
		console.log(this.props);
	}

	render() {
		return (
			<div className="flickrSet">
				<h1>TITLE: {this.props.Title}</h1>
			</div>
		);
	}
}

export default FlickrSet;
