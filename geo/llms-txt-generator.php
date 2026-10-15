<?php
/**
 * Generic /llms.txt generator for WordPress.
 *
 * llms.txt is an emerging convention (not a ratified standard): a plain-text
 * Markdown index that gives AI tools a curated map of a site's key pages.
 * Not every AI tool reads it, so treat it as a low-cost helper, not a guarantee.
 *
 * Usage: drop into a plugin or theme. Visit /llms.txt after flushing permalinks.
 */

namespace GrowthEngine\GEO;

class LlmsTxtGenerator {

    public static function register(): void {
        add_action('init', [self::class, 'add_rewrite']);
        add_action('template_redirect', [self::class, 'maybe_render']);
    }

    public static function add_rewrite(): void {
        add_rewrite_rule('^llms\.txt$', 'index.php?llms_txt=1', 'top');
        add_filter('query_vars', function (array $vars): array {
            $vars[] = 'llms_txt';
            return $vars;
        });
    }

    public static function maybe_render(): void {
        if (!get_query_var('llms_txt')) {
            return;
        }

        header('Content-Type: text/plain; charset=utf-8');

        echo '# ' . get_bloginfo('name') . "\n\n";
        echo '> ' . get_bloginfo('description') . "\n\n";

        // Group pages by top-level section so the file stays short and scannable.
        $sections = [
            'Key pages' => get_posts([
                'post_type'      => 'page',
                'post_status'    => 'publish',
                'posts_per_page' => 20,
                'orderby'        => 'menu_order',
                'order'          => 'ASC',
            ]),
            'Guides' => get_posts([
                'post_type'      => 'post',
                'post_status'    => 'publish',
                'posts_per_page' => 20,
            ]),
        ];

        foreach ($sections as $heading => $posts) {
            if (empty($posts)) {
                continue;
            }
            echo "## {$heading}\n\n";
            foreach ($posts as $p) {
                $summary = wp_strip_all_tags(get_the_excerpt($p));
                $summary = $summary !== '' ? ': ' . $summary : '';
                printf("- [%s](%s)%s\n", get_the_title($p), get_permalink($p), $summary);
            }
            echo "\n";
        }

        exit;
    }
}

LlmsTxtGenerator::register();
