import {InMemoryCache} from "apollo-cache-inmemory";
import {HttpLink} from "apollo-link-http";
import {ApolloClient} from "apollo-client";
import { resolvers, typeDefs } from "./resolvers";
const cache = new InMemoryCache({
	dataIdFromObject: o => o.ID
});

const link = new HttpLink({
	uri: window.location.origin + '/admin/flickr/graphql',
	headers: {
		'X-CSRF-TOKEN': document.getElementById('flickrVisibleWidget') .
				getAttribute('data-security-token')
	}
});

export const client = new ApolloClient({
	cache,
	link,
	typeDefs,
	resolvers
});

cache.writeData({
	data: {
		previewURL: 'Image will appear here',
		orientation: 0,
		networkStatus: {
			__typename: 'NetworkStatus',
			isConnected: false,
		},
	},
});
