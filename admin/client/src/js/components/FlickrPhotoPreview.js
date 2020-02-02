import React from 'react';
import gql from "graphql-tag";
import {useQuery} from "@apollo/react-hooks";

const GET_PREVIEW_URL = gql`  query GetPreviewURL {    previewURL orientation ID Title @client  }`;

const FlickrPhotoPreview = () => {
	// @todo Initial case needs fixed
	const { loading, error, data } = useQuery(GET_PREVIEW_URL);

	console.log('FPP"' , data);

	if (loading) return <p>Loading...</p>;
	if (error) return <p>Error :(</p>;
	if (!data) return <p>Not found</p>

	var cn='previewFlickrImage orientation'+data.orientation;

	return (
		<div> <img className={cn} src={data.previewURL} title={data.Title}/></div>
	)
}

export default FlickrPhotoPreview;


