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
					  SmallURL
					  LargeURL
					  Orientation
					  Visible
					  TakenAt
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


	var images = data.readFlickrSets[0].FlickrPhotos.edges;
    var pageInfo = data.readFlickrSets[0].FlickrPhotos.pageInfo;
    console.log('Images', images);
	console.log('Page info', pageInfo);

	var pageNumber = 1 + props.Offset / props.Limit;
	var pages = pageInfo.totalCount / props.Limit;
	var pageDescription = pageNumber + ' / ' + Math.ceil(pages);

	return (
			<div className="col col-4 flickrSet visibility">
				<div className="row">
					<div className="col col-12">
						{images.map(photo => (
							<FlickrPhotoApollo Visible={photo.node.Visible} key={photo.node.ID} ID={photo.node.ID} LargeURL={photo.node.LargeURL}
											   Orientation={photo.node.Orientation} ThumbnailURL={photo.node.ThumbnailURL} Title={photo.node.Title}
												SmallURL={photo.node.SmallURL} TakenAt={photo.node.TakenAt}
							/>
						))
						}
					</div>
				</div>

				<div className="row">
					<div className="col col-12 mt-8">
						<button className="btn btn-primary" disabled={!pageInfo.hasPreviousPage} onClick={props.onPrevPage}>Previous</button>
						{pageDescription}
						<button className="btn btn-primary ml-1" disabled={!pageInfo.hasNextPage} onClick={props.onNextPage}>Next</button>
					</div>
				</div>
			</div>
			);

}

export default FlickrPhotos;
