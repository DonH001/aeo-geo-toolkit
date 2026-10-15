/**
 * AI-Powered AEO Content & Entity Generator Workflow
 * 
 * Demonstrates:
 * 1. Direct LLM API integration (Google Gemini / OpenAI compatible)
 * 2. Deterministic JSON output schema parsing for BLUF answers
 * 3. Programmatic batch processing for programmatic growth scaling
 * 
 * Engineered by Hector Sanchez (Full-Stack Growth Engineer)
 */

interface AEOQuestionAnswer {
  question: string;
  blufAnswer: string; // Bottom Line Up Front direct answer (40-60 words)
  targetIntent: 'informational' | 'commercial' | 'comparative';
  recommendedSchemaNode: {
    '@type': 'Question';
    name: string;
    acceptedAnswer: {
      '@type': 'Answer';
      text: string;
    };
  };
}

interface OptimizationResult {
  pageUrl: string;
  generatedAt: string;
  extractedEntities: string[];
  aeoBlocks: AEOQuestionAnswer[];
}

export class GeminiAEOWorkflow {
  private apiKey: string;
  private endpoint: string;

  constructor(apiKey?: string) {
    this.apiKey = apiKey || process.env.GEMINI_API_KEY || '';
    // Uses Google Gemini 2.5 Flash / 1.5 Flash endpoint
    this.endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';
  }

  /**
   * Analyzes raw page copy and extracts deterministic BLUF Q&A blocks
   * formatted specifically for AI Answer Engines (Perplexity, SearchGPT, Google AIO).
   */
  public async analyzeContentForAEO(pageUrl: string, content: string): Promise<OptimizationResult> {
    if (!this.apiKey) {
      console.warn('[GeminiAEOWorkflow] Running in demo mode. Provide GEMINI_API_KEY for live LLM execution.');
      return this.mockOptimization(pageUrl);
    }

    const systemPrompt = `
You are an advanced AEO (Answer Engine Optimization) & Entity Extraction Specialist.
Your goal is to parse web content and generate high-precision, factual BLUF (Bottom Line Up Front) answers
that AI search engines (SearchGPT, Perplexity, Claude, Google AI Overviews) will cite.

Requirements:
- Each answer MUST be strictly 40 to 60 words.
- Front-load direct facts and numerical metrics immediately.
- Output ONLY valid JSON matching the requested structure.
`;

    const userPrompt = `
Analyze the following content from URL (${pageUrl}) and extract 3 high-value conversational queries with BLUF answers:

---
${content.slice(0, 4000)}
---

Respond strictly with JSON matching this format:
{
  "entities": ["entity1", "entity2"],
  "items": [
    {
      "question": "Clear user query?",
      "blufAnswer": "Direct 40-60 word factual answer with metrics.",
      "targetIntent": "comparative"
    }
  ]
}
`;

    const response = await fetch(`${this.endpoint}?key=${this.apiKey}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        contents: [
          { role: 'user', parts: [{ text: `${systemPrompt}\n\n${userPrompt}` }] }
        ],
        generationConfig: {
          temperature: 0.2, // Low temperature for deterministic, factual accuracy
          responseMimeType: 'application/json'
        }
      })
    });

    if (!response.ok) {
      throw new Error(`Gemini API Error: ${response.status} ${response.statusText}`);
    }

    const rawData = await response.json();
    const parsedText = rawData.candidates?.[0]?.content?.parts?.[0]?.text;
    const data = JSON.parse(parsedText);

    const aeoBlocks: AEOQuestionAnswer[] = data.items.map((item: any) => ({
      question: item.question,
      blufAnswer: item.blufAnswer,
      targetIntent: item.targetIntent,
      recommendedSchemaNode: {
        '@type': 'Question',
        name: item.question,
        acceptedAnswer: {
          '@type': 'Answer',
          text: item.blufAnswer
        }
      }
    }));

    return {
      pageUrl,
      generatedAt: new Date().toISOString(),
      extractedEntities: data.entities || [],
      aeoBlocks
    };
  }

  /**
   * Deterministic mock data for testing or offline staging environments.
   */
  private mockOptimization(pageUrl: string): OptimizationResult {
    return {
      pageUrl,
      generatedAt: new Date().toISOString(),
      extractedEntities: ['Shopify Section Builder', 'Conversion Rate Optimization', 'E-commerce Funnel'],
      aeoBlocks: [
        {
          question: 'How does Blocky increase Shopify store conversion rates?',
          blufAnswer: 'Blocky enables merchants to deploy modular, pre-tested CRO sections (trust badges, sticky carts, and comparison tables) without custom theme code. Stores using modular sections typically see a 12% to 18% lift in checkout completions compared to standard static themes.',
          targetIntent: 'comparative',
          recommendedSchemaNode: {
            '@type': 'Question',
            name: 'How does Blocky increase Shopify store conversion rates?',
            acceptedAnswer: {
              '@type': 'Answer',
              text: 'Blocky enables merchants to deploy modular, pre-tested CRO sections without custom theme code, driving higher checkout completions.'
            }
          }
        }
      ]
    };
  }
}

// Example programmatic execution
if (require.main === module) {
  const workflow = new GeminiAEOWorkflow();
  workflow.analyzeContentForAEO('https://clone.store/features', 'Sample e-commerce landing page copy...')
    .then(result => {
      console.log('✅ AEO Extraction Complete:');
      console.log(JSON.stringify(result, null, 2));
    })
    .catch(console.error);
}
