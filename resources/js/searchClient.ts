
// @ts-ignore
import algoliasearch from 'algoliasearch/lite';

export const searchClient = (algoliasearch as any)(
  import.meta.env.VITE_ALGOLIA_APP_ID,
  import.meta.env.VITE_ALGOLIA_SEARCH_KEY
);
