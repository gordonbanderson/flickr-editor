import React from 'react';
import gql from "graphql-tag";
import {useQuery} from "@apollo/react-hooks";
import MainImageForSetButton from "./functionComponents/MainImageForSetButton";

const GET_PREVIEW_URL = gql`  query GetPreviewURL {    previewURL orientation ID Title @client  }`;

const FlickrPhotoPreview = (props) => {
	// @todo Initial case needs fixed
	const { loading, error, data } = useQuery(GET_PREVIEW_URL);

	console.log('FPP"' , data);
	console.log('FPP PROPS', props);

	if (loading) return <p>Loading...</p>;
	// @todo This does not deal gracefully with the initial case
	if (error) return <p>Click on a thumbnail to preview it here, and also toggle it's visibility.
	Thubmnails with a red border will be visible</p>;
	if (!data) return <p>Not found</p>

	var cn='previewFlickrImage orientation'+data.orientation;

	return (
		<div className="col col-8">
			<div>
				<div> <img className={cn} src={data.previewURL} title={data.Title}/></div>
				<MainImageForSetButton FlickrSetID={props.FlickrSetID} FlickrPhotoID={data.ID}/>
			</div>

		</div>
	)
}

export default FlickrPhotoPreview;


