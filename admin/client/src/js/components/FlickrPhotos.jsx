import React from "react";
import {useQuery} from "@apollo/react-hooks";
import gql from "graphql-tag";
import FlickrPhotoApollo from "./functionComponents/FlickrPhotoApollo";

const FlickrPhotos = (props) => {
	console.log('FP PROPS', props);
	var flickrSetID = props.FlickrSetID;
	const PHOTO_QUERY = gql`query PhotoFeed($FlickrSetID: Int!, $limit: Int!, $offset: Int!) {
			  readFlickrSets(ID: $FlickrSetID) {
				ID
				Title
				FlickrID
				FlickrPhotos(limit: $limit, offset: $offset) {
				  edges {
					node {
					  ID
					  Title
					  FlickrID
					  ThumbnailURL
					  LargeURL
					  Orientation
					  Visible
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
				`;
	const { loading, error, data } = useQuery(PHOTO_QUERY, {
		variables: {
			FlickrSetID: props.FlickrSetID,
			limit: props.Limit,
			offset: props.Offset
		}
	});

	if (loading) return <p>Loading...</p>;
	if (error) return <p>Error :(</p>;
	if (!data) return <p>Not found</p>

/*
still not sussed out pagination

	const FeedData = ({ match }) => (
		<Query
			query={PHOTO_QUERY}
			variables={{
				FlickrSetID: props.FlickrSetID
			}}
			fetchPolicy="cache-and-network"
		>
			{({ data, fetchMore }) => (
				<Feed
					entries={data.feed || []}
					onLoadMore={() =>
						fetchMore({
							variables: {
								offset: data.feed.length,
								FlickrSetID: props.FlickrSetID
							},
							updateQuery: (prev, { fetchMoreResult }) => {
								if (!fetchMoreResult) return prev;
								return Object.assign({}, prev, {
									feed: [...prev.feed, ...fetchMoreResult.feed]
								});
							}
						})
					}
				/>
			)}
		</Query>
	);

*/

	var images = data.readFlickrSets[0].FlickrPhotos.edges;
    var pageInfo = data.readFlickrSets[0].FlickrPhotos.pageInfo;
    console.log('Images', images);
	console.log('Page info', pageInfo);

	return (
		<div>
			<div>
			{images.map(photo => (
					<FlickrPhotoApollo Visible={photo.node.Visible} key={photo.node.ID} ID={photo.node.ID} LargeURL={photo.node.LargeURL}
									   Orientation={photo.node.Orientation} ThumbnailURL={photo.node.ThumbnailURL} Title={photo.node.Title}/>
			))
			}
			</div>
			<div className="visiblePictureButtons">
				<button className="primary" onClick={props.onPrevPage}>Prev</button>
				<button className="primary" onClick={props.onNextPage}>Next</button>
			</div>
		</div>
			);

}

export default FlickrPhotos;
