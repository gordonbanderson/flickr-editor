import React from "react";
import {useApolloClient, useMutation} from "@apollo/react-hooks";
import gql from "graphql-tag";

export default function MainImageForSetButton(props) {
	const client = useApolloClient();
	const CHANGE_MAIN_IMAGE_QUERY = gql`
		mutation changeMainImageMutation($FlickrSetID: Int!, $FlickrPhotoID: Int!) {
  			changeMainImage(FlickrSetID: $FlickrSetID, FlickrPhotoID: $FlickrPhotoID) {
    			ID
    			Title
  			}
}
	`;

	const [changeSetMainImage, {data, loading, error}] = useMutation(CHANGE_MAIN_IMAGE_QUERY);

	console.log('Main image for set button', props);


	return (


		<div key={props.FlickrPhotoID}
			 onClick={() => {
				 console.log('Calling mutate?  Main set image');
				 console.log('PROPS', props);
				 changeSetMainImage({
					 variables: {
						 FlickrSetID: props.FlickrSetID,
						 FlickrPhotoID: props.FlickrPhotoID
					 }
				 })

			 	}
			 }
		>

			<button className="changeMainImageButton btn action btn-primary font-icon-camera mt-1 mb-4">
				Make this the main image {props.FlickrPhotoID}
			</button>

		</div>
	);

}
