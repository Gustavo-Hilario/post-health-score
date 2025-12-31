<?php
/**
 * Plugin Name: Post Health Score
 * Plugin URI: https://github.com/Gustavo-Hilario/post-health-score
 * Description: Adds a content quality health score column to the Posts admin list.
 * Version: 1.0.0
 * Author: Gustavo Hilario
 * Author URI: https://github.com/Gustavo-Hilario
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: post-health-score
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Add Health Score column to the Posts list
 *
 * @param array $columns Existing columns
 * @return array Modified columns
 */
function phs_add_health_score_column( $columns ) {
    // Insert after title column
    $new_columns = array();
    foreach ( $columns as $key => $value ) {
        $new_columns[ $key ] = $value;
        if ( 'title' === $key ) {
            $new_columns['health_score'] = __( 'Health Score', 'post-health-score' );
        }
    }
    return $new_columns;
}
add_filter( 'manage_posts_columns', 'phs_add_health_score_column' );

/**
 * Render the Health Score column content
 *
 * @param string $column  Column name
 * @param int    $post_id Post ID
 */
function phs_render_health_score_column( $column, $post_id ) {
    if ( 'health_score' !== $column ) {
        return;
    }

    // Placeholder for now - scoring logic will be added next
    echo esc_html( 'Score: --' );
}
add_action( 'manage_posts_custom_column', 'phs_render_health_score_column', 10, 2 );
