<?php
/**
 * Sample Technical Architecture: Programmatic AEO Entity Graph & JSON-LD Injection
 * Demonstrates: Clean OOP, Dynamic Entity Mapping, BLUF Schema Formatting, and Performance Hooking
 * 
 * Engineered by Hector Sanchez (Full-Stack Growth Engineer)
 */

namespace GrowthEngine\SEO;

class AEOEntityGraphManager {

    /**
     * Injects multi-layered JSON-LD Schema graph into document head.
     * Engineered specifically for AI retrieval models (SearchGPT, Perplexity, Google AIO).
     */
    public static function inject_aeo_entity_graph(): void {
        if (is_admin() || is_feed()) {
            return;
        }

        global $post;
        if (!$post) {
            return;
        }

        $canonical_url = get_permalink($post->ID);
        $page_title    = get_the_title($post->ID);

        // Base Entity Graph Structure
        $graph = [
            '@context' => 'https://schema.org',
            '@graph'   => [
                // 1. Organization / Brand Entity
                [
                    '@type'       => 'Organization',
                    '@id'         => home_url('/#organization'),
                    'name'        => get_bloginfo('name'),
                    'url'         => home_url('/'),
                    'logo'        => [
                        '@type'  => 'ImageObject',
                        'url'    => home_url('/assets/brand-logo.webp'),
                        'width'  => 512,
                        'height' => 512
                    ],
                    'knowsAbout'  => [
                        'E-commerce Automation',
                        'Programmatic SEO',
                        'Shopify Store Creation',
                        'AI Workflows'
                    ]
                ],

                // 2. WebSite Node with SearchAction
                [
                    '@type'       => 'WebSite',
                    '@id'         => home_url('/#website'),
                    'url'         => home_url('/'),
                    'name'        => get_bloginfo('name'),
                    'publisher'   => ['@id' => home_url('/#organization')],
                    'potentialAction' => [
                        '@type'       => 'SearchAction',
                        'target'      => home_url('/?s={search_term_string}'),
                        'query-input' => 'required name=search_term_string'
                    ]
                ],

                // 3. WebPage Entity with Contextual Mentions
                [
                    '@type'           => 'WebPage',
                    '@id'             => $canonical_url . '#webpage',
                    'url'             => $canonical_url,
                    'name'            => $page_title,
                    'isPartOf'        => ['@id' => home_url('/#website')],
                    'breadcrumb'      => ['@id' => $canonical_url . '#breadcrumb'],
                    'inLanguage'      => 'en-US'
                ]
            ]
        ];

        // 4. Dynamic FAQPage / Direct-Answer Schema for AEO Extractors
        $faq_data = self::extract_bluf_qa_nodes($post->post_content);
        if (!empty($faq_data)) {
            $graph['@graph'][] = [
                '@type'      => 'FAQPage',
                '@id'        => $canonical_url . '#faq',
                'isPartOf'   => ['@id' => $canonical_url . '#webpage'],
                'mainEntity' => $faq_data
            ];
        }

        // Render clean, minified JSON-LD block
        echo "\n<!-- Programmatic AEO & Knowledge Graph Layer -->\n";
        echo '<script type="application/ld+json">' . json_encode($graph, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
    }

    /**
     * Parses direct-answer BLUF (Bottom Line Up Front) QA pairs from post content
     * for deterministic AI answer extraction.
     */
    private static function extract_bluf_qa_nodes(string $content): array {
        $qa_nodes = [];

        // Matches custom direct-answer semantic blocks
        preg_match_all('/<details class="faq-item"[^>]*>.*?<summary[^>]*>(.*?)<\/summary>.*?<div class="faq-answer"[^>]*>(.*?)<\/div>.*?<\/details>/is', $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $question = trim(strip_tags($match[1]));
            $answer   = trim(strip_tags($match[2]));

            if (!empty($question) && !empty($answer)) {
                $qa_nodes[] = [
                    '@type'          => 'Question',
                    'name'           => $question,
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text'  => $answer
                    ]
                ];
            }
        }

        return $qa_nodes;
    }
}

// Hook into template lifecycle at highest priority
add_action('wp_head', [AEOEntityGraphManager::class, 'inject_aeo_entity_graph'], 1);
