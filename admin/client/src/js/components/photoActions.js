import FlickrPhoto from "./FlickrPhoto";
import React from "react";
import {client} from "./transport";

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

export function fetchPhotos() {
	return dispatch => {
		dispatch(fetchPhotosBegin());
		return client
			.query({
				query: gql`
					  query {
				  readFlickrSets(ID: ${this.props.ID}) {
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
					`
			})

			.then(
				payload => {
					var photos = payload.data.readFlickrSets[0].FlickrPhotos.edges
					dispatch(fetchPhotosSuccess(photos));
					return photos;
				}
				
			)
			.then(json => {
				dispatch(fetchPhotosSuccess(json.products));
				return json.products;
			})
			.catch(error => dispatch(fetchPhotosFailure(error)));
	};
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



