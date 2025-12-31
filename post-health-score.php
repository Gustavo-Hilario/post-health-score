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
 * Load plugin text domain for translations
 */
function phs_load_textdomain() {
    load_plugin_textdomain( 'post-health-score', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'phs_load_textdomain' );

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

    // Only load for 'post' post type, not pages or custom post types
    $screen = get_current_screen();
    if ( ! $screen || 'post' !== $screen->post_type ) {
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
    // Use preg_split for better multibyte/Unicode support
    $content      = get_post_field( 'post_content', $post_id );
    $stripped     = wp_strip_all_tags( $content );
    $words        = preg_split( '/\s+/', $stripped, -1, PREG_SPLIT_NO_EMPTY );
    $word_count   = count( $words );
    $checks['word_count'] = array(
        'passed' => $word_count > 300,
        'value'  => $word_count,
        'label'  => __( 'Word count', 'post-health-score' ),
    );
    if ( $checks['word_count']['passed'] ) {
        $score++;
    }

    // Check 2: Has featured image
    $has_thumbnail = has_post_thumbnail( $post_id );
    $checks['featured_image'] = array(
        'passed' => $has_thumbnail,
        'value'  => $has_thumbnail ? __( 'Yes', 'post-health-score' ) : __( 'No', 'post-health-score' ),
        'label'  => __( 'Featured image', 'post-health-score' ),
    );
    if ( $checks['featured_image']['passed'] ) {
        $score++;
    }

    // Check 3: Title length between 30-60 characters
    // Use mb_strlen for proper multibyte character counting
    $title        = get_the_title( $post_id );
    $title_length = mb_strlen( $title );
    $checks['title_length'] = array(
        'passed' => $title_length >= 30 && $title_length <= 60,
        'value'  => $title_length,
        'label'  => __( 'Title length', 'post-health-score' ),
    );
    if ( $checks['title_length']['passed'] ) {
        $score++;
    }

    // Check 4: Has at least 1 category (excluding the default category)
    // Use get_option to get the actual default category ID instead of assuming ID 1
    $default_category = (int) get_option( 'default_category' );
    $categories       = wp_get_post_categories( $post_id );
    $categories       = array_filter( $categories, function( $cat_id ) use ( $default_category ) {
        return $default_category !== $cat_id;
    });
    $category_count   = count( $categories );
    $checks['categories'] = array(
        'passed' => $category_count > 0,
        'value'  => $category_count,
        'label'  => __( 'Categories', 'post-health-score' ),
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
        'label'  => __( 'Tags', 'post-health-score' ),
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
 * Build tooltip content showing the breakdown of checks
 *
 * @param array $checks The checks array from phs_calculate_score()
 * @return string HTML content for the tooltip
 */
function phs_build_tooltip_content( $checks ) {
    $lines = array();

    foreach ( $checks as $key => $check ) {
        $icon  = $check['passed'] ? '✓' : '✗';
        $value = $check['value'];

        // Format value based on check type
        switch ( $key ) {
            case 'word_count':
                /* translators: %d is the number of words */
                $detail = sprintf( __( '%d words', 'post-health-score' ), $value );
                break;
            case 'title_length':
                /* translators: %d is the number of characters */
                $detail = sprintf( __( '%d chars', 'post-health-score' ), $value );
                break;
            case 'categories':
            case 'tags':
                /* translators: %d is the number of items assigned */
                $detail = sprintf( __( '%d assigned', 'post-health-score' ), $value );
                break;
            default:
                $detail = $value;
        }

        $lines[] = sprintf(
            '<span class="phs-tooltip-line %s">%s %s: %s</span>',
            $check['passed'] ? 'passed' : 'failed',
            $icon,
            esc_html( $check['label'] ),
            esc_html( $detail )
        );
    }

    return implode( '', $lines );
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

    $score_data      = phs_calculate_score( $post_id );
    $grade           = phs_get_grade( $score_data['score'] );
    $tooltip_content = phs_build_tooltip_content( $score_data['checks'] );

    // Note: Score is stored via save_post hook, not here, to avoid
    // unnecessary DB writes on every page view

    // Display grade with emoji and tooltip
    printf(
        '<span class="phs-grade-wrapper">
            <span class="phs-grade %s">%s %s</span>
            <span class="phs-tooltip">%s</span>
        </span>',
        esc_attr( $grade['class'] ),
        esc_html( $grade['letter'] ),
        esc_html( $grade['emoji'] ),
        $tooltip_content // Already escaped in phs_build_tooltip_content()
    );
}
add_action( 'manage_posts_custom_column', 'phs_render_health_score_column', 10, 2 );

/**
 * Make the Health Score column sortable
 *
 * @param array $columns Sortable columns
 * @return array Modified sortable columns
 */
function phs_make_column_sortable( $columns ) {
    $columns['health_score'] = 'health_score';
    return $columns;
}
add_filter( 'manage_edit-post_sortable_columns', 'phs_make_column_sortable' );

/**
 * Handle sorting by health score
 *
 * @param WP_Query $query The main query
 */
function phs_handle_health_score_sorting( $query ) {
    if ( ! is_admin() || ! $query->is_main_query() ) {
        return;
    }

    if ( 'health_score' === $query->get( 'orderby' ) ) {
        $query->set( 'meta_key', '_phs_health_score' );
        $query->set( 'orderby', 'meta_value_num' );
    }
}
add_action( 'pre_get_posts', 'phs_handle_health_score_sorting' );

/**
 * Update health score when post is saved
 *
 * @param int $post_id Post ID
 */
function phs_update_score_on_save( $post_id ) {
    // Don't run on autosave
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Only for posts
    if ( 'post' !== get_post_type( $post_id ) ) {
        return;
    }

    $score_data = phs_calculate_score( $post_id );
    update_post_meta( $post_id, '_phs_health_score', $score_data['score'] );
}
add_action( 'save_post', 'phs_update_score_on_save' );
