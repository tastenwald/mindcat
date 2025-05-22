<?php
/*
* MindCat
*
* @package     MindCat
* @subpackage  MindCat/Views
* @version     2.2.2
* @since       1.0.0
*/

namespace Mindcat;

function subcat($cat, $level, $args = array())
{
    $terms = get_terms('category', array(
        'parent' => $cat,
        'hirearchical' => false,
        'hide_empty' => $args['hide_empty'] ?? 0,
    ));
    $ret = '';
    $MindCatColors = get_option('MindCatColors', array());
    if (!is_array($MindCatColors)) $MindCatColors = array();

    if (sizeof($terms) > 0) {
        $ret .= '<ul>';
        foreach ($terms as $term) {
            $link = get_term_link($term, 'category');
            $bgcolor = isset($MindCatColors[$term->term_id]['bg']) ? $MindCatColors[$term->term_id]['bg'] : '#CCCCCC';
            $color = isset($MindCatColors[$term->term_id]['txt']) ? $MindCatColors[$term->term_id]['txt'] : '#333333';
            // Remove link to increase usability
            /* $ret .= '<li data-id="' . esc_attr($term->term_id) . '" class="mindcat_child"><a href="' . esc_attr($link) . '" style="background:' . esc_attr($bgcolor) . ';color:' . esc_attr($color) . ';">'; */
            $ret .= '<li data-id="' . esc_attr($term->term_id) . '" class="mindcat_child"><a style="background:' . esc_attr($bgcolor) . ';color:' . esc_attr($color) . ';">';
            $ret .= '<span>' . $term->name . '</span>';
            $input = $term->name;
            // Remove count to avoid additional css
            /* if (!empty($args['count']) && 1 == $args['count']) {
                $ret .= '<span class="mindcat_count">' . $term->count . '</span>';
            } */
            $ret .= '<input type="hidden" value="' . $input . '" />';
            $ret .= '<span class="dashicons dashicons-yes-alt" title="' . esc_html__( 'Save changes', 'mindcat' ) . '"></span>';
            $ret .= '<span class="mindcat-cancel dashicons dashicons-dismiss" title="' . esc_html__( 'Cancel changes', 'mindcat' ) . '"></span>';
            $ret .= '<span class="dashicons dashicons-welcome-write-blog" title="' . esc_html__( 'Rename this term', 'mindcat' ) . '"></span>';
            $ret .= '<span class="dashicons dashicons-plus-alt" title="' . esc_html__( 'Add a sub-term', 'mindcat' ) . '"></span>';
            $children = get_term_children( $term->term_id, 'category' );
            if ( is_array( $children )
                && empty( $children )
                && (( ! empty( $args[ 'max_level' ] )
                && $args[ 'max_level' ] > 0
                && $args[ 'max_level' ] > $level )
                || $args[ 'max_level' ] == $level )
            ) {
                $ret .= '<span class="mindcat-remove dashicons dashicons-dismiss" title="' . esc_html__( 'Remove this term', 'mindcat' ) . '"></span>';
            }
            $ret .= '</a>';
            if (!empty($args['max_level']) && $args['max_level'] > 0 && $args['max_level'] > $level) {
                $ret .= subcat($term->term_id, $level + 1, $args);
            }
            $ret .= '</li>';
        }
        $ret .= '</ul>';
    }
    return $ret;
}

function mindmap($args = '')
{
    extract(
        shortcode_atts(
            array(
                'cat' => '',
                'size' => 50,
                'title' => '',
                'hide_empty' => 0,
                'count' => 0,
                'max_level' => 0
            ),
            $args
        )
    );
    $root = get_option('blogname');
    $link = get_option('siteurl');


    $bgcolor = '#CCCCCC';
    $color = '#33333';
    $posts_count = 0;

    if ($cat == '') {
        $cat = 0;
    }
    if (is_string($cat) || $cat != '0') {

        if (is_numeric($cat)) {
            $term = get_term($cat, 'category');
        } else {
            $term = get_term_by('slug', $cat, 'category');
        }
        $cat = 0;
        if (!is_wp_error($term)) {
            $root = $term->name;
            $link = get_term_link($term, 'category');
            $cat = $term->term_id;
            $posts_count = $term->count;
            $MindCatColors = get_option('MindCatColors', array());
            if (!is_array($MindCatColors)) $MindCatColors = array();
            $bgcolor = isset($MindCatColors[$term->term_id]['bg']) ? $MindCatColors[$term->term_id]['bg'] : '#CCCCCC';
            $color = isset($MindCatColors[$term->term_id]['txt']) ? $MindCatColors[$term->term_id]['txt'] : '#333333';
        }
        if (is_wp_error($link)) {
            $link = get_option('siteurl');
        
        }
    }
    if (!empty($title)) {
        $root = $title;
    }

    if (false == $hide_empty || $count > 0) {
        $ret = '<div class="mindcat-wrapper">';
        $ret .= '<div class="mindcat" data-size="' . esc_attr($size) . '"><ul class="mindcat-top"><li class="mindcat_root">';
        // Remove link to increase usability
        /* $ret .= '<a href="' . esc_attr($link) . '" style="background:' . esc_attr($bgcolor) . ';color:' . esc_attr($color) . ';">'; */
        $ret .= '<a style="background:' . esc_attr($bgcolor) . ';color:' . esc_attr($color) . ';">';
        $ret .= '<span>' . $root . '</span>';
        // Remove count to avoid additional css
        /* if ($cat != 0 && 1 == $count) {
            $ret .= '<span class="mindcat_count">' . $posts_count . '</span>';
        } */
        $ret .= '<input type="hidden" value="' . $root . '" length="50" maxlength="50" />';
        $ret .= '<span class="dashicons dashicons-yes-alt" title="' . esc_html__( 'Save changes', 'mindcat' ) . '"></span>';
        $ret .= '<span class="mindcat-cancel dashicons dashicons-dismiss" title="' . esc_html__( 'Cancel changes', 'mindcat' ) . '"></span>';
        $ret .= '<span class="dashicons dashicons-welcome-write-blog" title="' . esc_html__( 'Rename mind map root', 'mindcat' ) . '"></span>';
        $ret .= '<span class="dashicons dashicons-plus-alt" title="' . esc_html__( 'Add a sub-term', 'mindcat' ) . '"></span>';
        $ret .= '</a>';
        $ret .= subcat($cat, 1, $args);
        $ret .= '</li></ul>';
        $ret .= '<div class="mindcat-layer"><div>';
        $ret .= '<p>' . esc_html__( 'Please enter sub-term:', 'mindcat' ) . '</p>';
        $ret .= '<input type="text" value="" length="50" maxlength="50" />';
        $ret .= '<span class="dashicons dashicons-yes-alt" title="' . esc_html__( 'Add sub-term', 'mindcat' ) . '"></span>';
        $ret .= '<span class="dashicons dashicons-dismiss" title="' . esc_html__( 'Cancel', 'mindcat' ) . '"></span>';
        $ret .= '</div></div>';
        $ret .= '</div>';
        $ret .= '<div class="mindcat-options">';
        $ret .= '<span class="dashicons dashicons-fullscreen-alt" title="' . esc_html__( 'Switch to full-screen', 'mindcat' ) . '"></span>';
        $ret .= '<span class="dashicons dashicons-fullscreen-exit-alt" title="' . esc_html__( 'Exit full-screen', 'mindcat' ) . '"></span>';
        $ret .= '</div>';
        $ret .= '</div>';
    }
    return $ret;
}