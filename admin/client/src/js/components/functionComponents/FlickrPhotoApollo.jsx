import React from "react";
import {useApolloClient} from "@apollo/react-hooks";

export default function FlickrPhotoApollo(props) {
	const client = useApolloClient();
	var cn = 'flickrPhoto orientation' + props.Orientation;

	return (

		<div className={cn} key={props.ID}
			 onClick={() => {
				 client.writeData({ data: { previewURL: props.LargeURL, orientation: props.Orientation } });
			 }}
		>
			<img src={props.ThumbnailURL} title={props.Title}/>
		</div>
	);

}
