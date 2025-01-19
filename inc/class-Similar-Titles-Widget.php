<?php

if (!defined('ABSPATH')) {
    exit; // Prevent direct access
}

class Similar_Titles_Checker_Widget {

    private $limit ; // Limit for items
    private $similarity_threshold ; // Minimum similarity threshold (50%)
    private $max_similar_items ; // Maximum number of similar items

    public function __construct() {
        add_action('wp_dashboard_setup', [$this, 'add_dashboard_widget']);

        $options = get_option('duplicate_title_validate_options', []);

        $this->limit = isset($options['limit']) ? $options['limit'] : 1000;
        $this->similarity_threshold = isset($options['similarity_threshold']) ? $options['similarity_threshold'] : 0.5;
        $this->max_similar_items = isset($options['max_similar_items']) ? $options['max_similar_items'] : 6;
    }

    // Add widget to the dashboard
    public function add_dashboard_widget() {
        wp_add_dashboard_widget(
            'global_similar_titles_widget',
            __('Global Similar Titles', 'duplicate-title-validate'),
            [$this, 'render_dashboard_widget']
        );
    }

    // Render the dashboard widget
    public function render_dashboard_widget() {
        $similar_titles = $this->find_global_similar_titles();

        if (!empty($similar_titles)) {
            echo '<h3>' . __('Similar Titles Across All Types:', 'duplicate-title-validate') . '</h3>';
            echo '<ul>';
            foreach ($similar_titles as $group) {
                echo '<li>';
                echo '<strong>' . esc_html($group['main_title']) . ' (' . esc_html($group['type']) . ')</strong>';
                echo '<ul>';
                foreach ($group['similar_items'] as $item) {
                    echo '<li>';
                    echo esc_html($item['post_title']) . ' (' . esc_html($item['type']) . ', ' . esc_html(round($item['similarity'] * 100, 2)) . '%)';
                    if ($item['edit_link']) {
                        echo ' - <a href="' . esc_url($item['edit_link']) . '">' . __('Edit', 'duplicate-title-validate') . '</a>';
                    } else {
                        error_log('No edit link found for: ' . $item['post_title'] . ' (ID: ' . $item['ID'] . ', Type: ' . $item['type'] . ')');
                        echo ' - <span style="color:red;">' . __('Edit link not available', 'duplicate-title-validate') . '</span>';
                    }
                    echo '</li>';
                }
                echo '</ul>';
                echo '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p>' . __('No similar titles found.', 'duplicate-title-validate') . '</p>';
        }

        // Advertisement section
        echo '
    <div style="background-color: #f4f4f4; padding: 20px; border-radius: 10px; text-align: center; font-family: Arial, sans-serif; max-width: 400px; margin: 0 auto;">
        <h2 style="color: #333; font-size: 20px; margin-bottom: 15px;">💡 Special Offer!</h2>
        <p style="color: #555; font-size: 12px; line-height: 1.6;">
            Get your <strong>custom WordPress plugin for just $10</strong>!<br>
            This exclusive offer is valid only for the <strong>first 10 customers</strong>. Don’t miss out!
        </p>
        <p style="color: #777; font-size: 14px; margin-top: 15px;">
            📧 Email me: <a href="mailto:hasan.mova@gmail.com" style="color: #0073e6; text-decoration: none;">hasan.mova@gmail.com</a>
        </p>
        <a href="mailto:hasan.mova@gmail.com" style="display: inline-block; background-color: #0073e6; color: #fff; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-size: 16px; margin-top: 15px;">
            🚀 Order Now
        </a>
    </div>
    ';
    }

    // Find similar titles across all posts and terms
    private function find_global_similar_titles() {
        $post_data = $this->get_all_posts();
        $term_data = $this->get_all_terms();

        $all_data = array_merge($post_data, $term_data);
        $groups = [];

        foreach ($all_data as $item) {
            $similar_items = $this->get_similar_items($item, $all_data);
            if (!empty($similar_items)) {
                $groups[] = [
                    'main_title' => $item['post_title'],
                    'type' => $item['type'],
                    'similar_items' => $similar_items
                ];
            }
        }

        return $groups;
    }

    // Get all posts with edit link check
    private function get_all_posts() {
        global $wpdb;

        $results = $wpdb->get_results($wpdb->prepare("
            SELECT ID, post_title, post_type
            FROM $wpdb->posts
            WHERE post_status = 'publish'
            AND post_type NOT LIKE 'product_variation'
            ORDER BY post_date DESC
            LIMIT %d
        ", $this->limit));

        $posts = [];
        foreach ($results as $row) {
            $edit_link = get_edit_post_link($row->ID);
            if ($edit_link) {
                $posts[] = [
                    'ID' => $row->ID,
                    'post_title' => $row->post_title,
                    'type' => 'post',
                    'edit_link' => $edit_link,
                ];
            } else {
                error_log('Could not generate edit link for post ID: ' . $row->ID);
                $posts[] = [
                    'ID' => $row->ID,
                    'post_title' => $row->post_title,
                    'type' => 'post',
                    'edit_link' => null,
                ];
            }
        }
        return $posts;
    }

    // Get all terms with edit link check
    private function get_all_terms() {
        $terms = get_terms([
            'taxonomy' => get_taxonomies(),
            'hide_empty' => false,
            'number' => $this->limit,
        ]);

        $term_data = [];
        foreach ($terms as $term) {
            $edit_link = get_edit_term_link($term->term_id, $term->taxonomy);
            if ($edit_link) {
                $term_data[] = [
                    'ID' => $term->term_id,
                    'post_title' => $term->name,
                    'type' => 'taxonomy: ' . $term->taxonomy,
                    'edit_link' => $edit_link,
                ];
            } else {
                error_log('Could not generate edit link for term ID: ' . $term->term_id . ' (Taxonomy: ' . $term->taxonomy . ')');
                $term_data[] = [
                    'ID' => $term->term_id,
                    'post_title' => $term->name,
                    'type' => 'taxonomy: ' . $term->taxonomy,
                    'edit_link' => null,
                ];
            }
        }
        return $term_data;
    }

    // Find similar items based on title
    private function get_similar_items($item, $all_items) {
        $main_vector = $this->text_to_vector($item['post_title']);
        $similar_items = [];
        $count = 0;

        foreach ($all_items as $other_item) {
            if ($item['ID'] === $other_item['ID'] && $item['type'] === $other_item['type']) {
                continue;
            }

            $other_vector = $this->text_to_vector($other_item['post_title']);
            $similarity = $this->cosine_similarity($main_vector, $other_vector);

            if ($similarity >= $this->similarity_threshold) {
                $similar_items[] = [
                    'ID' => $other_item['ID'],
                    'post_title' => $other_item['post_title'],
                    'type' => $other_item['type'],
                    'similarity' => $similarity,
                    'edit_link' => $other_item['edit_link'],
                ];
                $count++;
                if ($count >= $this->max_similar_items) {
                    break;
                }
            }
        }

        return $similar_items;
    }

    // Convert text to vector
    private function text_to_vector($text) {
        $words = $this->tokenize($text);
        return array_count_values($words);
    }

    // Tokenize text
    private function tokenize($text) {
        $text = mb_strtolower($text, 'UTF-8');
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', '', $text);
        return preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
    }

    // Calculate cosine similarity
    private function cosine_similarity($vector1, $vector2) {
        $dot_product = 0;
        $magnitude1 = 0;
        $magnitude2 = 0;

        $all_keys = array_unique(array_merge(array_keys($vector1), array_keys($vector2)));

        foreach ($all_keys as $key) {
            $value1 = isset($vector1[$key]) ? $vector1[$key] : 0;
            $value2 = isset($vector2[$key]) ? $vector2[$key] : 0;

            $dot_product += $value1 * $value2;
            $magnitude1 += $value1 ** 2;
            $magnitude2 += $value2 ** 2;
        }

        if ($magnitude1 == 0 || $magnitude2 == 0) {
            return 0;
        }

        return $dot_product / (sqrt($magnitude1) * sqrt($magnitude2));
    }
}

 