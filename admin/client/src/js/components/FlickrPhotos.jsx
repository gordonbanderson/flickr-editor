import React from "react";
import {useQuery} from "@apollo/react-hooks";
import gql from "graphql-tag";
import FlickrPhoto from "./FlickrPhoto";

const FlickrPhotos = (X) => {
	console.log('X', X.FlickrSetID);
	var flickrSetID = X.FlickrSetID;
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
	if (!data) return <p>Not found</p>

	var images = data.readFlickrSets[0].FlickrPhotos.edges;
	console.log('IMAGES', images);

	return (<div>
		{images.map(photo => (
				<FlickrPhoto key={photo.node.ID} ID={photo.node.ID} ThumbnailURL={photo.node.ThumbnailURL} Title={photo.node.Title}/>
		))
		}
		</div>
			);

}

export default FlickrPhotos;
