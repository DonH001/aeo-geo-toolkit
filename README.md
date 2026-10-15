# AEO + GEO Toolkit

Small, generic building blocks for making a WordPress site easier for AI search and answer engines to read and quote. Written for demonstration. Contains no client code or data.

## What's inside

| Path | What it does |
|---|---|
| `schema/aeo-entity-graph.php` | Injects a linked JSON-LD graph (Organization, WebSite, WebPage, FAQPage) into `wp_head` and builds FAQ nodes from on-page Q&A blocks. |
| `aeo/bluf-block-shortcode.php` | `[bluf_answer]` shortcode that outputs a short direct answer in markup the schema file can parse, so visible text and structured data match. |
| `geo/llms-txt-generator.php` | Serves a curated `/llms.txt` index of key pages. An emerging convention; not every AI tool reads it. |
| `geo/ai-crawler-robots.txt` | Sample `robots.txt` rules for AI crawlers (OAI-SearchBot, GPTBot, PerplexityBot, ClaudeBot). |
| `workflows/ai-aeo-analyzer.ts` | TypeScript workflow integrating with Google Gemini API to parse content, extract high-intent queries, generate BLUF answers, and output Schema nodes. |
| `audit/citation-check.md` | A repeatable checklist for testing which queries cite your site. |

## How the pieces fit
1. Pass landing page or product copy to `ai-aeo-analyzer.ts` (powered by Google Gemini API) to generate high-intent BLUF Q&A pairs.
2. Render those direct answers in markup via `[bluf_answer]`.
3. The schema manager injects the full interconnected `@graph` (Organization, WebSite, WebPage, FAQPage).
4. Verify crawler access with `ai-crawler-robots.txt` and `/llms.txt`.
5. Benchmark rankings and citations using the citation checklist.

## About AEO and GEO
The terms overlap. AEO targets being quoted as the direct answer. GEO targets being cited inside AI-generated answers. The technical foundations are largely shared: clear structure, accurate structured data, crawler access, and authority built over time.

## Honest limits
No tool or snippet can guarantee citations from any AI product. Each one chooses its own sources, and new sites often need authority (links, mentions, age) before they appear. These files are technical foundations, not shortcuts.

## Requirements
- PHP 7.4+ for WordPress modules.
- TypeScript / Node.js 18+ for AI workflow scripts. Set `GEMINI_API_KEY` for live execution.

## License
MIT. See `LICENSE`.

Built by Hector Sanchez.
