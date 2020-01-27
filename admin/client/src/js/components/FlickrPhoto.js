import ReactDOM from 'react-dom';
import React from 'react';

class FlickrPhoto extends React.Component {
	render() {
		return (
			<div className="flickrPhoto">
				<img src={this.props.ThumbnailURL} title={this.props.Title}/>
			</div>
		);
	}
}

export default FlickrPhoto;
