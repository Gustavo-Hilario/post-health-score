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

// Define plugin constants
define( 'PHS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'PHS_PLUGIN_VERSION', '1.0.0' );

/**
 * Enqueue admin styles
 *
 * @param string $hook Current admin page hook
 */
function phs_enqueue_admin_styles( $hook ) {
    // Only load on the posts list page
    if ( 'edit.php' !== $hook ) {
        return;
    }

    wp_enqueue_style(
        'phs-admin-styles',
        PHS_PLUGIN_URL . 'assets/css/admin.css',
        array(),
        PHS_PLUGIN_VERSION
    );
}
add_action( 'admin_enqueue_scripts', 'phs_enqueue_admin_styles' );

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
 * Calculate the health score for a post
 *
 * @param int $post_id Post ID
 * @return array Score data including total and individual checks
 */
function phs_calculate_score( $post_id ) {
    $score = 0;
    $checks = array();

    // Check 1: Word count > 300
    $content    = get_post_field( 'post_content', $post_id );
    $word_count = str_word_count( wp_strip_all_tags( $content ) );
    $checks['word_count'] = array(
        'passed' => $word_count > 300,
        'value'  => $word_count,
        'label'  => 'Word count',
    );
    if ( $checks['word_count']['passed'] ) {
        $score++;
    }

    // Check 2: Has featured image
    $has_thumbnail = has_post_thumbnail( $post_id );
    $checks['featured_image'] = array(
        'passed' => $has_thumbnail,
        'value'  => $has_thumbnail ? 'Yes' : 'No',
        'label'  => 'Featured image',
    );
    if ( $checks['featured_image']['passed'] ) {
        $score++;
    }

    // Check 3: Title length between 30-60 characters
    $title        = get_the_title( $post_id );
    $title_length = strlen( $title );
    $checks['title_length'] = array(
        'passed' => $title_length >= 30 && $title_length <= 60,
        'value'  => $title_length,
        'label'  => 'Title length',
    );
    if ( $checks['title_length']['passed'] ) {
        $score++;
    }

    // Check 4: Has at least 1 category (excluding "Uncategorized" which has ID 1)
    $categories      = wp_get_post_categories( $post_id );
    $categories      = array_filter( $categories, function( $cat_id ) {
        return 1 !== $cat_id; // Exclude "Uncategorized"
    });
    $category_count  = count( $categories );
    $checks['categories'] = array(
        'passed' => $category_count > 0,
        'value'  => $category_count,
        'label'  => 'Categories',
    );
    if ( $checks['categories']['passed'] ) {
        $score++;
    }

    // Check 5: Has at least 1 tag
    $tags      = wp_get_post_tags( $post_id );
    $tag_count = count( $tags );
    $checks['tags'] = array(
        'passed' => $tag_count > 0,
        'value'  => $tag_count,
        'label'  => 'Tags',
    );
    if ( $checks['tags']['passed'] ) {
        $score++;
    }

    return array(
        'score'  => $score,
        'max'    => 5,
        'checks' => $checks,
    );
}

/**
 * Get grade letter and emoji based on score
 *
 * @param int $score The score (0-5)
 * @return array Grade data with letter, emoji, and label
 */
function phs_get_grade( $score ) {
    $grades = array(
        5 => array(
            'letter' => 'A+',
            'emoji'  => '🌟',
            'label'  => 'Excellent',
            'class'  => 'grade-a-plus',
        ),
        4 => array(
            'letter' => 'A',
            'emoji'  => '👍',
            'label'  => 'Great',
            'class'  => 'grade-a',
        ),
        3 => array(
            'letter' => 'B',
            'emoji'  => '😊',
            'label'  => 'Good',
            'class'  => 'grade-b',
        ),
        2 => array(
            'letter' => 'C',
            'emoji'  => '😐',
            'label'  => 'Needs Work',
            'class'  => 'grade-c',
        ),
        1 => array(
            'letter' => 'D',
            'emoji'  => '🔧',
            'label'  => 'Poor',
            'class'  => 'grade-d',
        ),
        0 => array(
            'letter' => 'F',
            'emoji'  => '❌',
            'label'  => 'Failing',
            'class'  => 'grade-f',
        ),
    );

    return $grades[ $score ] ?? $grades[0];
}

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

    $score_data = phs_calculate_score( $post_id );
    $grade      = phs_get_grade( $score_data['score'] );

    // Display grade with emoji
    printf(
        '<span class="phs-grade %s">%s %s</span>',
        esc_attr( $grade['class'] ),
        esc_html( $grade['letter'] ),
        esc_html( $grade['emoji'] )
    );
}
add_action( 'manage_posts_custom_column', 'phs_render_health_score_column', 10, 2 );
