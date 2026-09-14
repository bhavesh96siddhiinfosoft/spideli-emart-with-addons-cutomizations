<?php

namespace Modules\AI\app\Services\Products\Response;

use Modules\AI\app\Services\Products\Resource\ProductResource;
use Modules\AI\app\Traits\ConversationTrait;

class ProductResponse
{
    use ConversationTrait;
    protected ProductResource $ProductResource;
    public function __construct()
    {
        $this->ProductResource = new ProductResource();
    }

    public function titleAutoFill(string $result)
    {
        $response["data"]["title"] = $result;
        return response()->json($response);
    }
    
    public function descriptionAutoFill(string $result)
    {
        $response["data"]["description"] = $result;
        return response()->json($response);
    }

    public function variationSetupAutoFill(string $result)
    {
        $result = preg_replace('/```[a-z]*\n?|\n?```/', '', trim($result));
        $data = json_decode($result, true);
        $response = [
            'data' => $data,
            'status' => 'success',
        ];
        return response()->json($response);
    }

    public function analyzeImageAutoFill(string $result)
    {
        $response["data"]["title"] = $result;
        return response()->json($response);
    }

    public function generateTitleSuggestions(string $result)
    {
        $response["data"] = json_decode($result, true);
        return response()->json($response);
    }

    public function ingredientsAutoFill(string $result)
    {
        $response["data"] = json_decode($result, true);
        return response()->json($response);
    }

    public function addonsAutoFill(string $result)
    {
        $response["data"] = json_decode($result, true);
        return response()->json($response);
    }

    public function specificationAutoFill(string $result)
    {
        $response["data"] = json_decode($result, true);
        return response()->json($response);
    }
}
