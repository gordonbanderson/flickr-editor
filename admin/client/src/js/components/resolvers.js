import gql from "graphql-tag";

export const typeDefs = gql`
  extend type Query {
    previewURL: String!
    orientation: Int!
  }

`;

export const resolvers = {};
