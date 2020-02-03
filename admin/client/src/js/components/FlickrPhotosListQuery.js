import React from 'react';
import { Query } from 'react-apollo';
import gql from 'graphql-tag';
import { graphql} from "graphql";

import FlickrPhotosList from './FlickrPhotosList';

const PHOTO_QUERY = gql`query PhotoFeed($ID: Int!){
			  readFlickrSets(ID: $ID) {
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

const FlickrPhotosListQuery = (props) => (
	<Query
		query={PHOTO_QUERY}
		variables={{
			ID: props.FlickrSetID,
			offset: 0,
			limit: 100
		}}
		fetchPolicy="cache-and-network"
	>
    {({ data, fetchMore }) =>
      data && (
        <FlickrPhotosList
          photos={data.readFlickrSets[0].FlickrPhotos.edges || []}
          onLoadMore={() =>
            fetchMore({
              variables: {
              	ID: props.FlickrSetID,
                offset: data.photos.length
              },
              updateQuery: (prev, { fetchMoreResult }) => {
                if (!fetchMoreResult) return prev;
                return Object.assign({}, prev, {
                  photos: [...prev.photos, ...fetchMoreResult.photos]
                });
              }
            })
          }
        />
      )
    }
  </Query>
);


export default FlickrPhotosListQuery;
