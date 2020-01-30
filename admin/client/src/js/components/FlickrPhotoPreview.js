import React from 'react';
import gql from "graphql-tag";
import {useQuery} from "@apollo/react-hooks";

const GET_PREVIEW_URL = gql`  query GetPreviewURL {    previewURL orientation @client  }`;

const FlickrPhotoPreview = () => {
	const { loading, error, data } = useQuery(GET_PREVIEW_URL);

	if (loading) return <p>Loading...</p>;
	if (error) return <p>Error :(</p>;
	if (!data) return <p>Not found</p>

	var cn='previewFlickrImage orientation'+data.orientation;

	return (
		<img className={cn} src={data.previewURL}/>
	)
}

export default FlickrPhotoPreview;


