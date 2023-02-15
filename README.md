# Algolia implementation for WordPress

## WP-CLI

This plugin add a wp-cli command for save all post

```
wp algolia reindex_post
```

## Hook

Update index of algolia when a post has added, updated or deleted


## Config

Add in wp-config.php :

```
define('ALGOLIA_APP_ID', 'XXX');
define('ALGOLIA_APP_PUBLIC', "XXX");
define('ALGOLIA_APP_SECRET', "XXX");
```

## JS

The vars ```ALGOLIA_APP_ID```,  ```ALGOLIA_APP_PUBLIC``` and ```ALGOLIA_APP_INDEX``` are shared across the page for use in javascript script.

Example of use :

```html
<div id="searchbox"></div>
<div id="hits"></div>
<div id="tags-list"></div>
```


```js
import algoliasearch from 'algoliasearch/lite'
import instantsearch from 'instantsearch.js'
import { searchBox, hits, refinementList } from 'instantsearch.js/es/widgets'

const search = instantsearch({
indexName: ALGOLIA_APP_INDEX,
searchClient: algoliasearch(ALGOLIA_APP_ID, ALGOLIA_APP_PUBLIC),
searchFunction(helper) {
    // Ensure we only trigger a search when there's a query
    if (helper.state.query && helper.state.query !== '') {
    document.getElementById('hits').removeAttribute('hidden')
    helper.search()
    } else {
    document.getElementById('hits').setAttribute('hidden', true)
    }
},
})

search.addWidgets([
searchBox({
    container: '#searchbox',
}),
refinementList({
    container: '#tag-list',
    attribute: 'tags',
    limit: 5,
    showMore: true,
}),
hits({
    container: '#hits',
    templates: {
    item: `
    <article>
    <a href="{{ url }}">
        <strong>
        {{#helpers.highlight}}
            { "attribute": "title", "highlightedTagName": "mark" }
        {{/helpers.highlight}}
        </strong>
    </a>
    {{#content}}
        <p>{{#helpers.highlight}}{ "attribute": "excerpt", "highlightedTagName": "mark" }{{/helpers.highlight}}</p>
    {{/content}}
    </article>
`,
    },
}),
])

search.start()

```

## Doc

- [https://www.algolia.com/doc/integration/wordpress/search/building-search-ui/?client=php](https://www.algolia.com/doc/integration/wordpress/search/building-search-ui/?client=php)
