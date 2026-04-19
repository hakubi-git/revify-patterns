<?php
/**
 * Plugin Name: Revify Patterns for SWELL
 * Description: Revify製　SWELL用のカスタムブロックパターン集
 * Version: 1.0.23
 * Author: revify
 * Text Domain: revify-patterns
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// 1. 自動更新チェック
require_once __DIR__ . '/vendor/plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;
$myUpdateChecker = PucFactory::buildUpdateChecker('https://github.com/hakubi-git/revify-patterns/', __FILE__, 'revify-patterns');

// 2. CSS読み込み
add_action( 'enqueue_block_assets', function() {
    $style_path = plugin_dir_path( __FILE__ ) . 'assets/style.css';
    if ( file_exists( $style_path ) ) {
        wp_enqueue_style('revify-patterns-style', plugins_url( 'assets/style.css', __FILE__ ), array(), filemtime( $style_path ));
    }
});

// 3. パターンの登録処理
add_action( 'init', function() {
    $pattern_files = glob( plugin_dir_path( __FILE__ ) . 'patterns/*.html' );
    if ( empty( $pattern_files ) ) return;

    $all_patterns = array();
    $categories   = array();

    foreach ( $pattern_files as $file ) {
        $content = file_get_contents( $file );
        if ( false === $content ) continue;

        $slug = basename( $file, '.html' );
        $title = $slug;
        $order = 999;
        $cat_name = 'revify専用パターン';

        /**
         * ここが肝心の「消去命令」です。
         * HTML内のコメント（<!-- ... -->）を探して、中身を変数に保存し、
         * その後、元のコンテンツからは「空（''）」に置き換えて消しています。
         */

        // ① Title（タイトル）を探して消す
        if ( preg_match( '/<!--\s*Title\s*[:：]\s*(.*?)\s*-->/us', $content, $m ) ) {
            $title = trim( $m[1] );
            $content = str_replace( $m[0], '', $content ); // コンテンツから完全に消去
        }

        // ② Category（カテゴリ）を探して消す
        if ( preg_match( '/<!--\s*Category\s*[:：]\s*(.*?)\s*-->/us', $content, $m ) ) {
            $cat_name = trim( $m[1] );
            $content = str_replace( $m[0], '', $content ); // コンテンツから完全に消去
        }

        // ③ Order（順序）を探して消す
        if ( preg_match( '/<!--\s*Order\s*[:：]\s*(\d+)\s*-->/us', $content, $m ) ) {
            $order = (int)$m[1];
            $content = str_replace( $m[0], '', $content ); // コンテンツから完全に消去
        }

        $cat_slug = 'revify-cat-' . md5($cat_name);
        $categories[$cat_slug] = $cat_name;

        $all_patterns[] = array(
            'slug' => $slug, 'title' => $title, 'order' => $order, 'cat_slug' => $cat_slug, 'content' => trim( $content ),
        );
    }

    // カテゴリを登録
    foreach ( $categories as $slug => $label ) {
        register_block_pattern_category( $slug, array( 'label' => $label ) );
    }

    // Order順に並び替え
    usort( $all_patterns, function( $a, $b ) { return $a['order'] <=> $b['order']; });

    // パターンの登録
    foreach ( $all_patterns as $p ) {
        register_block_pattern( 'revify/' . $p['slug'], array(
            'title' => $p['title'], 'categories' => array( $p['cat_slug'] ), 'content' => $p['content'],
        ));
    }
}, 99 );