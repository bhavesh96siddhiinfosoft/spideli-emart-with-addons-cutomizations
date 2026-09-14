<?php

namespace Modules\AI\app\Services\Products\Action;

use Modules\AI\app\Core\Constants\AIEngineNames;
use Modules\AI\app\Core\Contracts\AIEngineInterface;
use Modules\AI\app\Core\Factory\AIEngineFactory;
use Modules\AI\app\Services\Products\Prompts\ProductPrompts;
use Modules\AI\app\Services\Products\Resource\ProductResource;
use Modules\AI\app\Traits\ConversationTrait;

class ProductAutoFillService
{
   use ConversationTrait;
   protected AIEngineInterface $engine;
   protected ProductPrompts $productPrompts;

   public function __construct()
   {
      $this->engine = AIEngineFactory::create(AIEngineNames::getDefault());
      $this->productPrompts = new ProductPrompts();
   }

   public function titleAutoFill(string $name,  $lang): string
   {
      $prompt = $this->productPrompts->titleAutoFill($name, $lang);

      return  $this->engine->core($prompt);
   }

   public function descriptionAutoFill(string $name,  $lang): string
   {
      $prompt = $this->productPrompts->descriptionAutoFill($name, $lang);

      return $this->cleanAIHtmlOutput($this->engine->core($prompt));
   }
   
   public function variationSetupAutoFill(array $data): string
   {
      $prompt = $this->productPrompts->variationSetupAutoFill($data);

      return  $this->engine->core($prompt);
   }

   public function imageAnalysisAutoFill(string $imageUrl): string
   {
      $prompt = $this->productPrompts->imageAnalysisAutoFill();

      return  $this->engine->core($prompt, $imageUrl);
   }
   
   public function generateTitleSuggestions(array $keywords): string
   {
      $prompt = $this->productPrompts->generateTitleSuggestions($keywords);
      
      return  $this->engine->core($prompt);
   }

   public function ingredientsAutoFill(array $data): string
   {
      $prompt = $this->productPrompts->ingredientsAutoFill($data);

      return  $this->engine->core($prompt);
   }

   public function addonsAutoFill(array $data): string
   {
      $prompt = $this->productPrompts->addonsAutoFill($data);

      return  $this->engine->core($prompt);
   }

   public function specificationAutoFill(array $data): string
   {
      $prompt = $this->productPrompts->specificationAutoFill($data);

      return  $this->engine->core($prompt);
   }
}
