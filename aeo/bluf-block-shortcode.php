<?php
/**
 * BLUF (Bottom Line Up Front) answer block shortcode.
 *
 * Outputs a short direct answer in visible HTML using the same markup the
 * schema sample in /schema parses, so the visible text and the JSON-LD
 * always match.
 *
 * Usage in post content:
 *   [bluf_answer question="How long does shipping take?"]
 *     Orders ship within 2 business days and arrive in 3 to 5.
 *   [/bluf_answer]
 */

namespace GrowthEngine\AEO;

class BlufBlock {

    public static function register(): void {
        add_shortcode('bluf_answer', [self::class, 'render']);
    }

    public static function render(array $atts, ?string $content = ''): string {
        $atts = shortcode_atts(['question' => ''], $atts, 'bluf_answer');

        $question = trim($atts['question']);
        $answer   = trim(wp_strip_all_tags((string) $content));

        if ($question === '' || $answer === '') {
            return '';
        }

        // Soft guard: direct answers work best at roughly 40 to 60 words.
        $words = str_word_count($answer);
        $note  = $words > 80 ? '<!-- BLUF answer is long; consider trimming -->' : '';

        return sprintf(
            '<details class="faq-item" open>' .
                '<summary>%s</summary>' .
                '<div class="faq-answer">%s</div>' .
            '</details>%s',
            esc_html($question),
            esc_html($answer),
            $note
        );
    }
}

BlufBlock::register();
