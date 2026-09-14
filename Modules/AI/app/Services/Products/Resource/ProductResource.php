<?php

namespace Modules\AI\app\Services\Products\Resource;

class ProductResource
{

    private $productType = ["veg", "nonveg"];
    
    public function __construct()
    {
    }

    public function productGeneralSetupData($vendorId): array
    {
        $data = [
            'vendors' => [],
            'categories' => [],
            'item_attribute' => [],
        ];
        return $data;
    }
}
