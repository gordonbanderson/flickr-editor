import {InMemoryCache} from "apollo-cache-inmemory";
import {HttpLink} from "apollo-link-http";
import {ApolloClient} from "apollo-client";
import { resolvers, typeDefs } from "./resolvers";
const cache = new InMemoryCache();
const link = new HttpLink({
	uri: 'http://localhost/admin/flickr/graphql'
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
