<?php

namespace Modules\AI\app\Services\Products\Prompts;

use Illuminate\Support\Facades\Cache;
use Modules\AI\app\Services\Products\Resource\ProductResource;

class ProductPrompts
{
    protected ProductResource $ProductResource;

    public function __construct()
    {
        $this->ProductResource = new ProductResource();
    }

    public function titleAutoFill(string $name, string $lang = "en")
    {
        $lang = strtoupper($lang);

        $promptText = <<<PROMPT
      You are a professional e-commerce copywriter.

      Rewrite the product name "{$name}" as a clean, concise, and professional product title for online stores.

      CRITICAL INSTRUCTION:
      - The output must be 100% in {$lang} — this is mandatory.
      - If the original name is not in {$lang}, fully translate it into {$lang} while keeping the meaning.
      - Do not mix languages; use only {$lang} characters and words.
      - Keep it short (35–70 characters), plain, and ready for listings.
      - No extra words, slogans, or punctuation.
      - Return only the translated title as plain text in {$lang}.

      PROMPT;

        return $promptText;
    }

    public function descriptionAutoFill(string $name, string $lang = "en")
    {
        $lang = strtoupper($lang);

        $promptText = <<<PROMPT
        You are a creative and professional food copywriter.

        Generate a detailed, engaging, and persuasive product description for the product named "{$name}".

        CRITICAL LANGUAGE RULES:
        - The entire description must be written 100% in {$lang} — this is mandatory.
        - If the product name is in another language, translate and localize it naturally into {$lang}.
        - Do not mix languages; use only {$lang} characters and words.
        - Adapt the tone, phrasing, and examples to be natural for {$lang} readers.

        Content & Structure:
        - Include a section with key features as separate paragraphs with its ingredients.
        - Focus on benefits, unique selling points, and appeal to the target audience.
        - Use clear, compelling, and marketing-friendly language.
        - Ensure the description is engaging and interesting.
        - Avoid any non-product-specific information.
        - Must be in 500–1000 characters.
        - Keep it short and to the point, plain, simple and ready for listings.

        Formatting:
        - Output valid Product descriptions.
        - Do NOT include any markdown syntax, code fences, or triple backticks.
        - Return only plain text in the paragraph (no HTML tags, no empty lines).


        PROMPT;

        return $promptText;
    }

    public function variationSetupAutoFill(array $resource)
    {
        $name         = $resource['name'];
        $description  = $resource['description'];
        $categories   = json_encode($resource['categories'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $item_attribute  = json_encode($resource['item_attribute'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $promptText = <<<PROMPT
            You are an expert food product classifier and variation generator.

            Your task: Given the product name and description, generate a valid JSON object describing the item, including vendor, category, veg/non-veg status, and realistic variations.

            Product details:
            Name: {$name}
            Description: {$description}

            Available data:
            - Categories (name => id): {$categories}
            - Item Attributes (attribute_name => attribute_id): {$item_attribute}

            Return ONLY a valid JSON object with this exact structure (no markdown, no explanations, no text outside JSON):

            {
                "category_id": "Category Id", // must be selected from Categories list
                "category_name": "Category name", // must be selected from Categories list
                "is_nonveg": true, // true if vegetarian, false if non vegetarian
                "item_attribute": { 
                    "attributes": [
                        {
                            "attribute_id": "ATTRIBUTE_ID_FROM_item_attribute",
                            "attribute_options": [
                                "Option 1",
                                "Option 2",
                            ]
                        },
                        {
                            "attribute_id": "ATTRIBUTE_ID_FROM_item_attribute",
                            "attribute_options": [
                                "Option A", 
                                "Option B"
                            ]
                        }
                    ],
                    "variants": [
                        {
                            "variant_id": "AUTO_GENERATED_ID_1",
                            "variant_sku": "Option 1-Option A",
                            "variant_price": "100",
                            "variant_quantity": "-1"
                        },
                        {
                            "variant_id": "AUTO_GENERATED_ID_2",
                            "variant_sku": "Option 1-Option B",
                            "variant_price": "150",
                            "variant_quantity": "-1"
                        },
                        {
                            "variant_id": "AUTO_GENERATED_ID_3",
                            "variant_sku": "Option 2-Option A",
                            "variant_price": "200",
                            "variant_quantity": "-1"
                        },
                        {
                            "variant_id": "AUTO_GENERATED_ID_3",
                            "variant_sku": "Option 2-Option B",
                            "variant_price": "200",
                            "variant_quantity": "-1"
                        }
                    ]
                }
            }

            Rules:
            - Match vendor_name and category_name using relevant keywords in product name or description.
            - Use all available attributes from item_attribute to create realistic options (e.g., Size → Small, Medium, Large; Type → Veg, Non-Veg, etc.).
            - Generate all possible combinations of options across attributes to form variants.
            - Each "variant_id" must be a unique random 14-character alphanumeric string (similar to "642ac74bd67ab8").
            - Prices must be realistic positive numbers and can vary slightly between variants.
            - variant_quantity is always "-1".
            - If only one attribute exists, generate variants using that attribute only.
            - Output must be valid JSON (for json_decode in PHP).
            - Do NOT include markdown, code blocks, or explanations.
            PROMPT;

        return $promptText;
    }

    public function imageAnalysisAutoFill(string $lang = "en")
    {
        $lang = strtoupper($lang);

        $promptText = <<<PROMPT
            You are an advanced food product analyst with strong skills in image recognition.

            Analyze the uploaded product image provided by the user.
            Your task is to generate a clean, concise, and professional product title for online stores.

            CRITICAL INSTRUCTION:
            - The output must be 100% in {$lang} — this is mandatory.
            - Identify the main product in the image and name it clearly.
            - Do not add extra descriptions like "high quality" or "best".
            - Keep it short (35–70 characters), plain, and ready for listings.
            - Return only the translated product title as plain text in {$lang}.

            PROMPT;

        return $promptText;
    }
    public function generateTitleSuggestions(array $keywords, string $lang = "en")
    {
        $lang = strtoupper($lang);
        $keywordsText = implode(' ', $keywords);

        $promptText = <<<PROMPT
               You are an advanced e-commerce product analyst.

               Using the keywords "{$keywordsText}", generate 4 professional, clean, and concise product titles for online stores.

               CRITICAL INSTRUCTIONS:
               - The output must be 100% in {$lang}.
               - Titles must use the keywords naturally.
               - Keep them short (35–70 characters), clear, and ready for listings.
               - Return exactly 4 titles in **plain JSON** format as shown below (do not include ```json``` or any extra markdown):

               {
                 "titles": [
                   "Title 1",
                   "Title 2",
                   "Title 3",
                   "Title 4"
                 ]
               }

               Do not include any extra explanation, only return the JSON.
               PROMPT;

        return $promptText;
    }

    public function ingredientsAutoFill(array $resource)
    {
        $name         = $resource['name'];
        $description  = $resource['description'];
        
        $promptText = <<<PROMPT
            You are a nutrition expert and food analyzer.

            Your task: Based on the given product title and description, calculate realistic nutritional values and return a valid JSON object containing calories, grams, fats, and proteins.

            Product details:
            Name: {$name}
            Description: {$description}

            Return ONLY a valid JSON object with the exact structure below (no markdown, no explanations, no text outside JSON):

            {
            "calories": "number in kcal",
            "grams": "total weight in grams",
            "fats": "total fats in grams",
            "proteins": "total proteins in grams"
            }

            Rules:
            - Values must be realistic for the product described.
            - calories must be in kilocalories (kcal) as a number string (e.g. "250").
            - grams must be the total serving weight in grams (e.g. "150").
            - fats must be total fat amount in grams (e.g. "12").
            - proteins must be total protein amount in grams (e.g. "8").
            - Do NOT include units like "kcal", "g", etc. — return numbers only as strings.
            - Output must be valid JSON and parseable in PHP using json_decode().
            - Do NOT include any text, explanation, or formatting outside the JSON object.

            PROMPT;

        return $promptText;
    }

    public function addonsAutoFill(array $resource)
    {
        $name         = $resource['name'];
        $description  = $resource['description'];
        
        $promptText = <<<PROMPT
            You are a product add-on suggestion generator for food and commerce items.

            Your task: Based on the given product name and description, generate realistic add-ons that customers may buy with the item.

            Product details:
            Name: {$name}
            Description: {$description}

            Return ONLY a valid JSON object with this exact structure (no markdown, no explanations, no extra text):

            {
            "addOnsTitle": ["Addon 1", "Addon 2"],
            "addOnsPrice": ["50", "20"]
            }

            Rules:
            - Generate 2 to 5 meaningful add-ons related to the product
            - Titles must be short and clear
            - Prices must be realistic, positive numbers, returned as strings
            - The number of titles and prices must match
            - Prices should match addon value (example: premium = higher price)
            - Do NOT repeat the product name itself as an addon
            - Do NOT include currency symbols (₹ $ etc)
            - Output must be valid JSON (for json_decode in PHP)
            - Do NOT include markdown, backticks, or text outside JSON

            Example output:
            {
            "addOnsTitle": ["Fire Candle", "Normal Stick Candle"],
            "addOnsPrice": ["50", "20"]
            }
            PROMPT;

        return $promptText;
    }

    public function specificationAutoFill(array $resource)
    {
        $name         = $resource['name'];
        $description  = $resource['description'];
        
        $promptText = <<<PROMPT
            You are a product specification generator for food and commercial products.

            Your task: Based on the product name and description, generate key product specifications with appropriate values.

            Product details:
            Name: {$name}
            Description: {$description}

            Return ONLY a valid JSON object with this exact structure (no markdown, no explanations, no extra text):

            {
                "product_specification": {
                    "Specification Name 1": "Value",
                    "Specification Name 2": "Value"
                }
            }

            Rules:
            - Generate 2 to 6 realistic and relevant specifications
            - Keys must be short and meaningful (e.g., Size, Weight, Serves, Pieces, Flavor, Capacity, etc)
            - Values must be realistic and returned as strings (do not include data types like number)
            - Do NOT include currency symbols unless required (₹ $ etc)
            - Avoid repeated or duplicate keys
            - Do NOT include the product name itself as a key
            - Output must be valid JSON (for json_decode in PHP)
            - Do NOT include markdown, backticks, or any text outside JSON

            Example output:
            {
                "product_specification": {
                    "Double Pound": "800",
                    "Single Pound": "500",
                    "Serves": "6-8",
                    "Egg Type": "Eggless"
                }
            }
            PROMPT;

        return $promptText;
    }
}
