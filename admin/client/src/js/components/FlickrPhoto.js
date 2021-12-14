import React from 'react';
import { useApolloClient } from "@apollo/react-hooks";


const handleClick2 = function(ID) {
	//const client = useApolloClient();
	console.log('Handle click 2 ' , ID);

	/*
	client.writeData({
		data: {
			previewURL: 'Image clicked ' + ssID,
		},
	});

	 */
}

class FlickrPhoto extends React.Component {

	handleClick(ssID) {
		console.log('Click happened ', ssID);

/*

*/

	}


	render() {
		return (
			<div className="flickrPhoto" key={this.props.ID} onClick={() => handleClick2(this.props.ID)}>
				<img src={this.props.ThumbnailURL} title={this.props.Title}/>
			</div>
		);
	}
}

export default FlickrPhoto;
