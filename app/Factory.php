<?php
namespace GenerCodeOrm;

class Factory {

    protected $products = [];


    function addProducts($products) {
        $this->products = array_merge($this->products, $products);
    }

    
    function __invoke($product_slug) {
        if (!isset($this->products[$product_slug])) {
            throw new \Exception("Slug doesn't exist in factory: " . $product_slug);
        }
        
        return ($this->products[$product_slug])();
    }
}