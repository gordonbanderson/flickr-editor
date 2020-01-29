import React from 'react';


class FlickrPhoto extends React.Component {

	handleClick(ssID) {
		console.log('Click happened ', ssID);
	}


	render() {
		return (
			<div className="flickrPhoto" key={this.props.ID} onClick={() => this.handleClick(this.props.ID)}>
				<img src={this.props.ThumbnailURL} title={this.props.Title}/>
			</div>
		);
	}
}

export default FlickrPhoto;
