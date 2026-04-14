<?php
/**
 * Plugin Name: revify Patterns for SWELL
 * Description: revify受講生専用のカスタムブロックパターン集（GitHub自動更新対応）
 * Version: 1.0.7
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
 * 2. 独自CSSの読み込み（フロントエンド & エディタ両方）
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
 * 3. ブロックパターンの登録
 */
add_action( 'init', function() {

    // 独自カテゴリの登録
    register_block_pattern_category(
        'revify-category',
        array( 'label' => 'revify専用パターン' )
    );

    // patternsフォルダ内の全HTMLファイルを自動取得
    $pattern_files = glob( plugin_dir_path( __FILE__ ) . 'patterns/*.html' );

    if ( ! empty( $pattern_files ) ) {
        foreach ( $pattern_files as $file ) {
            // ファイル名をスラッグとタイトルに利用
            $slug  = basename( $file, '.html' );
            $title = str_replace( array( '-', '_' ), ' ', $slug );
            $title = ucwords( $title ); // 先頭を大文字に

            register_block_pattern(
                'revify/' . $slug,
                array(
                    'title'      => 'revify: ' . $title,
                    'categories' => array( 'revify-category' ),
                    'content'    => file_get_contents( $file ),
                )
            );
        }
    }
});