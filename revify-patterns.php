<?php
/**
 * Plugin Name: revify Patterns for SWELL
 * Description: revify受講生専用のカスタムブロックパターン集（GitHub自動更新対応）
 * Version: 1.0.4
 * Author: revify
 * Text Domain: revify-patterns
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * 1. GitHubからの自動更新設定
 */
require __DIR__ . '/vendor/plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/hakubi-git/revify-patterns/', 
    __FILE__,
    'revify-patterns'
);

/**
 * 2. 独自CSSの読み込み
 */
add_action( 'enqueue_block_assets', function() {
    wp_enqueue_style(
        'revify-patterns-style',
        plugins_url( 'assets/style.css', __FILE__ ),
        array(),
        filemtime( plugin_dir_path( __FILE__ ) . 'assets/style.css' )
    );
});

/**
 * 3. ブロックパターンの登録（日本語タイトル対応版）
 */
add_action( 'init', function() {

    register_block_pattern_category(
        'revify-category',
        array( 'label' => 'revify専用パターン' )
    );

    $pattern_files = glob( plugin_dir_path( __FILE__ ) . 'patterns/*.html' );

    if ( ! empty( $pattern_files ) ) {
        foreach ( $pattern_files as $file ) {
            $content = file_get_contents( $file );
            $slug    = basename( $file, '.html' );
            
            // デフォルトのタイトル（ファイル名から作成）
            $title = ucwords( str_replace( array( '-', '_' ), ' ', $slug ) );

            // HTML内の 「Title: 〇〇」 を探し出す（※ここはシステムなので半角 < です）
            if ( preg_match( '//', $content, $matches ) ) {
                $title = $matches[1]; 
                $content = str_replace( $matches[0], '', $content );
            }

            register_block_pattern(
                'revify/' . $slug,
                array(
                    'title'      => $title,
                    'categories' => array( 'revify-category' ),
                    'content'    => trim( $content ),
                )
            );
        }
    }
});