<?php

namespace Modules\AI\app\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\FunctionHelper;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Modules\AI\app\Services\Products\Action\ProductAutoFillService;
use Modules\AI\app\Services\Products\Response\ProductResponse;
use Illuminate\Support\Facades\Validator;
use Modules\AI\app\Traits\ConversationTrait;
use Illuminate\Support\Facades\Log;


class ProductAutoFillController extends Controller
{

    use ConversationTrait;
    public function __construct(
        private  ProductAutoFillService $productAutoFillService,
        private ProductResponse $productResponse,
    ) {
       
    }

    public function generateTitleAndDescription(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            $response['status'] = 'failed';
            $response['code'] = 404;
            $response['message'] = $validator->errors()->first();
            $response['data'] = null;
            return response()->json($response);
        }

        try {

            $title = $this->productAutoFillService->titleAutoFill(
                $request->name,
                $request->lang ??  App::getLocale(),
            );

            $description = $this->productAutoFillService->descriptionAutoFill(
                $request->name,
                $request->lang ??  App::getLocale(),
            );

            $response['status'] = 'success';
            $response['code'] = 200;
            $response['message'] = 'Title & description successfully generated';
            $response['data'] = [
                'title' => $title,
                'description' => $description,
            ];

        } catch (\Throwable $e) {

            $msg = $e->getMessage();

            $response['status'] = 'failed';
            $response['code'] = 429;
            $response['message'] = 'AI generation is temporarily unavailable due to usage limits. Please try again later.';
            $response['data'] = null;
        }

        return response()->json($response);
    }

    public function generateVariationData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'description' => 'required',
            'categories'  => 'required|array',
            'item_attribute'  => 'required|array',
        ],[
            'name.required' => 'Please provide a name so the AI can generate data.',
            'name.max' => 'The product name may not exceed 255 characters.',
            'description.required' => 'Please provide a description so the AI can generate data.',
            'categories.required' => 'Please provide a category list so the AI can generate data.',
            'item_attribute.required' => 'Please provide an attribute list so the AI can generate data.',
        ]);

        if ($validator->fails()) {
            $response['status'] = 'failed';
            $response['code'] = 404;
            $response['message'] = $validator->errors()->first();
            $response['data'] = null;
            return response()->json($response);
        }
        
        try {

            $result = $this->productAutoFillService->variationSetupAutoFill($request->all());

            $result = preg_replace('/```[a-z]*\n?|\n?```/', '', trim($result));
            $data = json_decode($result, true);

            $response['status'] = 'success';
            $response['code'] = 200;
            $response['message'] = 'Variation successfully generated';
            $response['data'] = $data;

        } catch (\Throwable $e) {

            $msg = $e->getMessage();

            $response['status'] = 'failed';
            $response['code'] = 429;
            $response['message'] = 'AI generation is temporarily unavailable due to usage limits. Please try again later.';
            $response['data'] = null;
        }

        return response()->json($response);
    }

    public function generateIngredients(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'description' => 'required',
        ],[
            'name.required' => 'Please provide a name so the AI can generate data.',
            'name.max' => 'The product name may not exceed 255 characters.',
            'description.required' => 'Please provide a description so the AI can generate data.',
        ]);

        if ($validator->fails()) {
            $response['status'] = 'failed';
            $response['code'] = 404;
            $response['message'] = $validator->errors()->first();
            $response['data'] = null;
            return response()->json($response);
        }

        try {

            $result = $this->productAutoFillService->ingredientsAutoFill($request->all());
            $data = json_decode($result, true);

            $response['status'] = 'success';
            $response['code'] = 200;
            $response['message'] = 'Ingredients data successfully generated';
            $response['data'] = $data;

        } catch (\Throwable $e) {

            $msg = $e->getMessage();

            $response['status'] = 'failed';
            $response['code'] = 429;
            $response['message'] = 'AI generation is temporarily unavailable due to usage limits. Please try again later.';
            $response['data'] = null;
        }

        return response()->json($response);
    }

    public function generateAddons(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'description' => 'required',
        ],[
            'name.required' => 'Please provide a name so the AI can generate data.',
            'name.max' => 'The product name may not exceed 255 characters.',
            'description.required' => 'Please provide a description so the AI can generate data.',
        ]);

        if ($validator->fails()) {
            $response['status'] = 'failed';
            $response['code'] = 404;
            $response['message'] = $validator->errors()->first();
            $response['data'] = null;
            return response()->json($response);
        }

        try {

            $result = $this->productAutoFillService->addonsAutoFill($request->all());
            $data = json_decode($result, true);

            $response['status'] = 'success';
            $response['code'] = 200;
            $response['message'] = 'Addons data successfully generated';
            $response['data'] = $data;

        } catch (\Throwable $e) {

            $msg = $e->getMessage();

            $response['status'] = 'failed';
            $response['code'] = 429;
            $response['message'] = 'AI generation is temporarily unavailable due to usage limits. Please try again later.';
            $response['data'] = null;
        }
        return response()->json($response);
    }

    public function generateSpecification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'description' => 'required',
        ],[
            'name.required' => 'Please provide a name so the AI can generate data.',
            'name.max' => 'The product name may not exceed 255 characters.',
            'description.required' => 'Please provide a description so the AI can generate data.',
        ]);

        if ($validator->fails()) {
            $response['status'] = 'failed';
            $response['code'] = 404;
            $response['message'] = $validator->errors()->first();
            $response['data'] = null;
            return response()->json($response);
        }

        try {

            $result = $this->productAutoFillService->specificationAutoFill($request->all());
            $data = json_decode($result, true);

            $response['status'] = 'success';
            $response['code'] = 200;
            $response['message'] = 'Specification data successfully generated';
            $response['data'] = $data;

        } catch (\Throwable $e) {

            $msg = $e->getMessage();

            $response['status'] = 'failed';
            $response['code'] = 429;
            $response['message'] = 'AI generation is temporarily unavailable due to usage limits. Please try again later.';
            $response['data'] = null;
        }

        return response()->json($response);
    }
    
    public function generateImageData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:1024',
            'categories'  => 'required|string',
            'item_attribute'  => 'required|string',
        ], [
            'image.required' => 'Image is required for analysis.',
            'image.image' => 'The uploaded file must be an image.',
            'image.mimes' => 'Only JPEG, PNG, JPG, and GIF images are allowed.',
            'image.max' => 'Image size must not exceed 1MB.',
            'categories.required' => 'Please provide a category list so the AI can generate data.',
            'item_attribute.required' => 'Please provide an attribute list so the AI can generate data.',
        ]);
        if ($validator->fails()) {
            $response['status'] = 'failed';
            $response['code'] = 404;
            $response['message'] = $validator->errors()->first();
            $response['data'] = null;
            return response()->json($response);
        }
        
        try {
            
            $categories = json_decode($request->categories, true);
            $item_attribute   = json_decode($request->item_attribute, true);

            $extension = $request->image->getClientOriginalExtension();
            $imageName = FunctionHelper::upload(dir: 'images/ai_product_image', format: $extension, image: $request->image);
            $imageUrl = asset('images/ai_product_image/' . $imageName);
            
            $title = $this->productAutoFillService->imageAnalysisAutoFill(
                imageUrl: $imageUrl,
            );

            FunctionHelper::check_and_delete(dir: 'images/ai_product_image/', old_image: $imageName);

            $description = $variationData = $ingredientsData = $addonsData = $specificationData = null;

            //Generate Description
            if(!empty($title)){
                $description = $this->productAutoFillService->descriptionAutoFill($title, App::getLocale());
            }

            if(!empty($title) && !empty($description)){

                $requestData = [ 
                    'name' => $title, 
                    'description' => $description, 
                    'categories' => $categories, 
                    'item_attribute' => $item_attribute 
                ];

                //Generate Variation
                $variationResult = $this->productAutoFillService->variationSetupAutoFill($requestData);
                $variationResult = preg_replace('/```[a-z]*\n?|\n?```/', '', trim($variationResult));
                $variationData = json_decode($variationResult, true);

                //Generate Ingredients
                $ingredientsResult = $this->productAutoFillService->ingredientsAutoFill($requestData);
                $ingredientsData = json_decode($ingredientsResult, true);

                //Generate Addons
                $addonsResult = $this->productAutoFillService->addonsAutoFill($requestData);
                $addonsData = json_decode($addonsResult, true);

                //Generate Specification
                $specificationResult = $this->productAutoFillService->specificationAutoFill($requestData);
                $specificationData = json_decode($specificationResult, true);
            }

            $response['status'] = 'success';
            $response['code'] = 200;
            $response['message'] = 'Image data successfully generated';
            $response['data'] = [
                'title' => $title,
                'description' => $description,
                'variation' => $variationData,
                'ingredients' => $ingredientsData,
                'addons' => $addonsData,
                'specification' => $specificationData,
            ];

        } catch (\Throwable $e) {

            $msg = $e->getMessage();

            $response['status'] = 'failed';
            $response['code'] = 429;
            $response['message'] = 'AI generation is temporarily unavailable due to usage limits. Please try again later.';
            $response['data'] = null;
        }

        return response()->json($response);
    }
}
