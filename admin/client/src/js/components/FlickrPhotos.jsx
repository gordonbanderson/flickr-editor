import React from "react";
import {useQuery} from "@apollo/react-hooks";
import gql from "graphql-tag";
import FlickrPhotoApollo from "./functionComponents/FlickrPhotoApollo";

const FlickrPhotos = (params) => {
	var flickrSetID = params.FlickrSetID;
	const PHOTO_QUERY = gql`query PhotoFeed{
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
	const { loading, error, data } = useQuery(PHOTO_QUERY);

	if (loading) return <p>Loading...</p>;
	if (error) return <p>Error :(</p>;
	if (!data) return <p>Not found</p>

	var images = data.readFlickrSets[0].FlickrPhotos.edges;

	console.log(images);

	return (<div>
		{images.map(photo => (
				<FlickrPhotoApollo Visible={photo.node.Visible} key={photo.node.ID} ID={photo.node.ID} LargeURL={photo.node.LargeURL}
								   Orientation={photo.node.Orientation} ThumbnailURL={photo.node.ThumbnailURL} Title={photo.node.Title}/>
		))
		}
		</div>
			);

}

export default FlickrPhotos;
