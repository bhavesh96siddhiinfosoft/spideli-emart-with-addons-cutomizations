<?php

namespace Modules\AI\app\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Helpers\FunctionHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Modules\AI\app\Services\Products\Action\ProductAutoFillService;
use Modules\AI\app\Services\Products\Response\ProductResponse;
use Modules\AI\app\Traits\ConversationTrait;

class ProductAutoFillController extends Controller
{

    use ConversationTrait;
    public function __construct(
        private  ProductAutoFillService $productAutoFillService,
        private ProductResponse $productResponse,
    ) {
        
    }

    public function titleAutoFill(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'lang' => 'nullable|string|max:20',
        ], [
            'name.required' => 'Please provide a product name so the AI can generate a suitable title or description.',
            'name.max' => 'The product name may not exceed 255 characters.',
        ]);
        
        $result = $this->productAutoFillService->titleAutoFill(
            $request->name,
            $request->lang
        );

        return $this->productResponse->titleAutoFill($result);
    }

    public function descriptionAutoFill(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'lang' => 'nullable|string|max:20',
        ], [
            'name.required' => 'Please provide a default name so the AI can generate a description.',
            'name.max' => 'The product name may not exceed 255 characters.',
        ]);

        $result = $this->productAutoFillService->descriptionAutoFill(
            $request->name,
            $request->lang
        );

        return $this->productResponse->descriptionAutoFill($result);
    }
    
    public function variationSetupAutoFill(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'description' => 'required',
        ],[
            'name.required' => 'Please provide a default name so the AI can generate a Data.',
            'name.max' => 'The product name may not exceed 255 characters.',
            'description.required' => 'Please provide a default description so the AI can generate a Data.',
        ]);
        
        $result = $this->productAutoFillService->variationSetupAutoFill($request->all());

        return $this->productResponse->variationSetupAutoFill($result);
    }

    public function analyzeImageAutoFill(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:1024',
        ], [
            'image.required' => 'Image is required for analysis.',
            'image.image' => 'The uploaded file must be an image.',
            'image.mimes' => 'Only JPEG, PNG, JPG, and GIF images are allowed.',
            'image.max' => 'Image size must not exceed 1MB.',
        ]);
        if ($validator->fails()) {
            return response()->json(
                $this->inputValidationErrors($validator->errors()->toArray()),
                422
            );
        }

        $extension = $request->image->getClientOriginalExtension();
        $imageName = FunctionHelper::upload(dir: 'images/ai_product_image', format: $extension, image: $request->image);
        $imageUrl = asset('images/ai_product_image/' . $imageName);
        
        $result = $this->productAutoFillService->imageAnalysisAutoFill(
            imageUrl: $imageUrl,
        );

        FunctionHelper::check_and_delete(dir: 'images/ai_product_image/', old_image: $imageName);

        return $this->productResponse->analyzeImageAutoFill($result);
    }

    public function generateTitleSuggestions(Request $request)
    {
        $validated = $request->validate([
            'keywords' => 'required|string|max:255',
        ]);
        
        $keywords = array_map('trim', explode(',', $request->keywords));
        $result = $this->productAutoFillService->generateTitleSuggestions($keywords);

        return $this->productResponse->generateTitleSuggestions($result);
    }

    public function ingredientsAutoFill(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'description' => 'required',
        ],[
            'name.required' => 'Please provide a default name so the AI can generate a Data.',
            'name.max' => 'The product name may not exceed 255 characters.',
            'description.required' => 'Please provide a default description so the AI can generate a Data.',
        ]);
        
        $result = $this->productAutoFillService->ingredientsAutoFill($request->all());

        return $this->productResponse->ingredientsAutoFill($result);
    }

    public function addonsAutoFill(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'description' => 'required',
        ],[
            'name.required' => 'Please provide a default name so the AI can generate a Data.',
            'name.max' => 'The product name may not exceed 255 characters.',
            'description.required' => 'Please provide a default description so the AI can generate a Data.',
        ]);
        
        $result = $this->productAutoFillService->addonsAutoFill($request->all());

        return $this->productResponse->addonsAutoFill($result);
    }

    public function specificationAutoFill(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'description' => 'required',
        ],[
            'name.required' => 'Please provide a default name so the AI can generate a Data.',
            'name.max' => 'The product name may not exceed 255 characters.',
            'description.required' => 'Please provide a default description so the AI can generate a Data.',
        ]);
        
        $result = $this->productAutoFillService->specificationAutoFill($request->all());

        return $this->productResponse->specificationAutoFill($result);
    }
}
