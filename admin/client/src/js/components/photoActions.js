import FlickrPhoto from "./FlickrPhoto";
import React from "react";

export const FETCH_PHOTOS_BEGIN   = 'FETCH_PHOTOS_BEGIN';
export const FETCH_PHOTOS_SUCCESS = 'FETCH_PHOTOS_SUCCESS';
export const FETCH_PHOTOS_FAILURE = 'FETCH_PHOTOS_FAILURE';

export const fetchProductsBegin = () => ({
	type: FETCH_PHOTOS_BEGIN
});

export const fetchProductsSuccess = photos => ({
	type: FETCH_PHOTOS_SUCCESS,
	payload: { photos }
});

export const fetchProductsFailure = error => ({
	type: FETCH_PHOTOS_FAILURE,
	payload: { error }
});

export function fetchPhotos() {
	return dispatch => {
		dispatch(fetchProductsBegin());
		return fetch("/products")
			.then(res => res.json())
			.then(json => {
				dispatch(fetchProductsSuccess(json.products));
				return json.products;
			})
			.catch(error => dispatch(fetchProductsFailure(error)));
	};
}


export function fetchPhotosORIG() {
	return dispatch => {
		dispatch(fetchProductsBegin());
		return fetch("/products")
			.then(res => res.json())
			.then(json => {
				dispatch(fetchProductsSuccess(json.products));
				return json.products;
			})
			.catch(error => dispatch(fetchProductsFailure(error)));
	};
}



