<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Duplicate_Title_Validate_Title_Checker {

    /**
     * Check for duplicate titles in posts and taxonomies
     * 
     */
    public function check_duplicates($title, $post_id = 0) {
        global $wpdb;

        // Check for duplicate posts
        $post_results = $wpdb->get_results($wpdb->prepare(
            "SELECT post_title, post_type FROM $wpdb->posts 
             WHERE post_status = 'publish' AND post_title = %s AND ID != %d",
            $title, $post_id
        ));

        // Check for duplicate terms across all taxonomies
        $taxonomy_results = $wpdb->get_results($wpdb->prepare(
            "SELECT DISTINCT tt.taxonomy 
             FROM $wpdb->terms t
             INNER JOIN $wpdb->term_taxonomy tt ON t.term_id = tt.term_id
             WHERE t.name = %s",
            $title
        ));

        // If no duplicates found, return false
        if (empty($post_results) && empty($taxonomy_results)) {
            return false;
        }

        // Collect sources of duplicates
        $sources = [];

        // If there are duplicate posts
        if (!empty($post_results)) {
            $post_types_list = $this->get_post_type_labels($post_results);
            $sources[] = $post_types_list;
        }

        // If there are duplicate taxonomies
        if (!empty($taxonomy_results)) {
            $taxonomy_names = [];
            foreach ($taxonomy_results as $taxonomy) {
                $tax_obj = get_taxonomy($taxonomy->taxonomy);
                $tax_label = ($tax_obj && !empty($tax_obj->labels->singular_name)) ? $tax_obj->labels->singular_name : $taxonomy->taxonomy;
                $taxonomy_names[] = $tax_label;
            }
            $taxonomies_list = implode(', ', array_unique($taxonomy_names));
            $sources[] = $taxonomies_list;
        }

        return $sources;
    }

    /**
     * Get post type labels for duplicate posts
     * 
     */
    private function get_post_type_labels($post_results) {
        $post_types = wp_list_pluck($post_results, 'post_type');
        $post_type_labels = [];

        foreach ($post_types as $post_type) {
            $post_type_obj = get_post_type_object($post_type);
            if ($post_type_obj) {
                $post_type_labels[] = $post_type_obj->labels->singular_name;
            }
        }

        return implode(', ', array_unique($post_type_labels));
    }


/**
     * Get all titles that are the same as the given title.
     *
     */
    public function get_matching_titles($title) {
        global $wpdb;
    
        
        $search_term = '%' . $wpdb->esc_like($title) . '%';
    
        
        $query = $wpdb->prepare(
            "SELECT post_title AS title, 'post' AS type
             FROM $wpdb->posts
             WHERE post_status = 'publish' AND post_title LIKE %s
             UNION
             SELECT name AS title, 'term' AS type
             FROM $wpdb->terms
             WHERE name LIKE %s",
            $search_term,
            $search_term
        );
    
    
        $results = $wpdb->get_results($query);
     
        $matching_titles = [];
        foreach ($results as $result) {
            $type = ($result->type === 'post') ? __('Post', 'textdomain') : __('Term', 'textdomain');
            $matching_titles[] = $result->title . ' (' . $type . ')';
        }
   
        $matching_titles = array_filter($matching_titles, function ($item) use ($title) {
            return strpos($item, $title . ' (') === false;
        });
    
        // Remove duplicates
        $matching_titles = array_unique($matching_titles);
    
        return $matching_titles;
    }

}