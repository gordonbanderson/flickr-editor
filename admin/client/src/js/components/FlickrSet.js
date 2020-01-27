import ReactDOM from 'react-dom';
import React from 'react';
import {ApolloClient} from 'apollo-client';
import {InMemoryCache, NormalizedCacheObject} from 'apollo-cache-inmemory';
import {HttpLink} from 'apollo-link-http';
import gql from "graphql-tag";
import FlickrPhoto from "./FlickrPhoto";
import '../../css/flickrreact.scss';



const cache = new InMemoryCache();
const link = new HttpLink({
	uri: 'http://localhost/admin/flickr/graphql'
});

const client = new ApolloClient({
	cache,
	link
});

class FlickrSet extends React.Component {


	constructor(props) {
		super(props);
		console.log(this.props);
		this.state = {photos: []};

		this.loadImages = this.loadImages.bind(this);

		console.log('About to call load images....')

		this.loadImages(0);


		// this does not work when navigating between tabs


	}


	render() {
		return (
			<div className="visibility flickrSet">
				<h1> {this.props.Title}</h1>
				{this.state.photos}
			</div>
		);
	}


	/**
	 * Make a GraphQL call to load images
	 * @param offset
	 */
	async loadImages(offset) {
		console.log('Loading images with offset', offset);
		console.log('T1', this);

		var result = await client
			.query({
				query: gql`
					  query {
				  readFlickrSets(ID: ${this.props.ID}) {
					ID
					Title
					FlickrID
					FlickrPhotos {
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
				function (payload) {
					console.log('PAYLOAD', payload);
					var set = payload.data.readFlickrSets[0];
					var photos = set.FlickrPhotos.edges;
					console.log(photos);

					var newPhotos = [];

					photos.forEach((value) => {
						var fp = <FlickrPhoto ID={value.node.id}
											  ThumbnailURL={value.node.ThumbnailURL}/>;
						console.log(fp);
						newPhotos.push(fp);
					})

					return newPhotos;
				}
			);

		console.log('RESULT', result)

		this.setState({
			photos: result
		})


	}

}

export default FlickrSet;
