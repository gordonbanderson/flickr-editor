import React from "react";
import {useQuery} from "@apollo/react-hooks";
import gql from "graphql-tag";


export const FETCH_PHOTOS_BEGIN   = 'FETCH_PHOTOS_BEGIN';
export const FETCH_PHOTOS_SUCCESS = 'FETCH_PHOTOS_SUCCESS';
export const FETCH_PHOTOS_FAILURE = 'FETCH_PHOTOS_FAILURE';

export const fetchPhotosBegin = () => ({
	type: FETCH_PHOTOS_BEGIN
});

export const fetchPhotosSuccess = photos => ({
	type: FETCH_PHOTOS_SUCCESS,
	payload: { photos }
});

export const fetchPhotosFailure = error => ({
	type: FETCH_PHOTOS_FAILURE,
	payload: { error }
});



export function fetchPhotos( flickrSetID) {

	console.log('Fetch photos');

		const { loading, error, data } = useQuery(gql`query {
			  readFlickrSets(ID: ${flickrSetID}) {
				ID
				Title
				FlickrID
				FlickrPhotos(limit: 100) {
				  edges {
					node {
					  ID
					  Title
					  FlickrID
					  ThumbnailURL
					}
				  }
				  pageInfo {
						hasNextPage
						hasPreviousPage
						totalCount
					  }
				}
			  }
			  }
				`);

		if (loading) return <p>Loading...</p>;
		if (error) return <p>Error :(</p>;


		return data;
}


export function fetchPhotosORIG() {
	return dispatch => {
		dispatch(fetchPhotosBegin());
		return fetch("/products")
			.then(res => res.json())
			.then(json => {
				dispatch(fetchPhotosSuccess(json.products));
				return json.products;
			})
			.catch(error => dispatch(fetchPhotosFailure(error)));
	};
}



