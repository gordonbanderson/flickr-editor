import React from 'react';

class FlickrPhotoPreview extends React.Component {

	constructor(props) {
		super(props);
		console.log(this.props);
		this.state = {photo: null};
	}



	render() {
		return (
			<div className="flickrPhotoPreview">
				Preview ++++++!
				title = 'test'
			</div>
		);
	}
}

export default FlickrPhotoPreview;
