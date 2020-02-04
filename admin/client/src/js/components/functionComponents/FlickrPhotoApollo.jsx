import React from "react";
import { useApolloClient, useMutation } from "@apollo/react-hooks";
import gql from "graphql-tag";

export default function FlickrPhotoApollo(props) {
	const client = useApolloClient();
	const TOGGLE_VISIBILITY =gql`
		mutation ($ID: Int!) {
  			toggleVisibility(ID: $ID) {
    			ID
    			FlickrID
    			Title
    			ThumbnailURL
    			SmallURL320
    			LargeURL
    			Visible
    			Orientation
  			}
}
	`;

	const [onToggleVisibility, { data, loading, error }] = useMutation(TOGGLE_VISIBILITY)


	var cn = 'flickrPhoto orientation' + props.Orientation ;
	if (props.Visible) {
		cn = cn + ' visible';
	}

	if (props.Selected) {
		cn = cn + ' selected';
	}


	return (


		<div className={cn} key={props.ID}
			 onMouseEnter={() => {
				 client.writeData({
					 data: {
						 ID: props.ID,
						 Title: props.Title,
						 previewURL: props.LargeURL,
						 orientation: props.Orientation,
						 TakenAt: props.TakenAt
					 }
				 });
			 }}

			 onClick={() => {
				 client.writeData({
					 data: {
						 ID: props.ID,
						 Title: props.Title,
						 previewURL: props.LargeURL,
						 orientation: props.Orientation,
						 TakenAt: props.TakenAt
					 }
				 });

				 var values = {ID: props.ID};

				 onToggleVisibility({
					 variables: {
						 ID: props.ID
					 }
				 })

			 }
			 }
		>
			<img src={props.SmallURL} title={props.Title}/>
		</div>
	);

}
