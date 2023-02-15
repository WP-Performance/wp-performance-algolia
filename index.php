<?php

namespace WPPerfomance\Algolia;

use WPPerfomance\Algolia\Inc\SearchAlgoliaClient;

/**
 * Plugin Name:     WP Performance Algolia
 * Description:     Add Algolia Search feature
 * Text Domain:     wp-performance-algolia
 * Version:         1.0.0
 *
 * @package         WP_Performance_Algolia
 */

require_once __DIR__ . '/vendor/autoload.php';

// singleton class
require_once __DIR__ . '/inc/SearchAlgoliaClient.php';
// cli command
require_once __DIR__ . '/inc/wp-cli.php';


if (!defined('WP_ENV')) {
    define('WP_ENV', 'development');
}
/**
 * get current post types
 */
function getPostTypes()
{
    return ['post', 'page', 'snippet'];
}

/**
 * get meta keys autorized for algolia
 */
function getMetaKeys()
{
    return ['seo_description', 'seo_title'];
}

/**
 * get index name for algolia
 * add prefix env
 */
function wp_perf_algolia_index_name($defaultName = 'content')
{
    return WP_ENV . '_' . $defaultName;
}


// init keys for algolia
SearchAlgoliaClient::initKeys(ALGOLIA_APP_ID, ALGOLIA_APP_SECRET);


/**
 * map data
 */
function wp_perf_post_to_record(\WP_Post $post)
{
    /** add tags */
    $tags = array_map(function (\WP_Term $term) {
        return $term->name;
    }, wp_get_post_terms($post->ID, 'post_tag'));

    /** add cat */
    $cat = array_map(function (\WP_Term $term) {
        return $term->name;
    }, wp_get_post_terms($post->ID, 'category'));

    return [
        'objectID' => implode('#', [$post->post_type, $post->ID]),
        'title' => $post->post_title,
        'author' => [
            'id' => $post->post_author,
            'name' => get_user_by('ID', $post->post_author)->display_name,
        ],
        'excerpt' => html_entity_decode(get_the_excerpt($post)),
        'content' => html_entity_decode(strip_tags($post->post_content)),
        'tags' => $tags,
        'categories' => $cat,
        'url' => get_post_permalink($post->ID),
        'custom_field' => get_post_meta($post->id, $post->custom_type),
    ];
}


/** same for all but you can change by post type */
add_filter('post_to_record', __NAMESPACE__ . '\wp_perf_post_to_record');
add_filter('snippet_to_record', __NAMESPACE__ . '\wp_perf_post_to_record');
add_filter('page_to_record', __NAMESPACE__ . '\wp_perf_post_to_record');



/**
 * hook meta update
 */
function wp_perf_update_post_meta($meta_id, $object_id, $meta_key, $_meta_value)
{
    $algolia = SearchAlgoliaClient::getInstance();

    if (in_array($meta_key, namespace\getMetaKeys())) {
        $index = $algolia->initIndex(
            namespace\wp_perf_algolia_index_name()
        );

        $index->partialUpdateObject([
            'objectID' => 'post#' . $object_id,
            $meta_key => $_meta_value,
        ]);
    }
}

add_action('update_post_meta', __NAMESPACE__ . '\wp_perf_update_post_meta', 10, 4);



/**
 * hook post update or create
 */
function wp_perf_update_post($id, \WP_Post $post, $update)
{
    if (wp_is_post_revision($id) || wp_is_post_autosave($id)) {
        return $post;
    }

    if (!in_array($post->post_type, namespace\getPostTypes())) {
        return $post;
    }

    $algolia = SearchAlgoliaClient::getInstance();

    $record = (array) apply_filters($post->post_type . '_to_record', $post);

    if (!isset($record['objectID'])) {
        $record['objectID'] = implode('#', [$post->post_type, $post->ID]);
    }

    $index = $algolia->initIndex(
        namespace\wp_perf_algolia_index_name()
    );

    if ('trash' == $post->post_status) {
        $index->deleteObject($record['objectID']);
    } else {
        $index->saveObject($record);
    }

    return $post;
}

add_action('save_post', __NAMESPACE__ . '\wp_perf_update_post', 10, 3);
