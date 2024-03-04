<?php

class Categories
{
    // Method to get categories (dummy implementation)
    public function getCategories()
    {
        // Dummy categories data
        $categories = [
            "Fruits" => ["Apple", "Banana", "Orange"],
            "Vegetables" => ["Carrot", "Broccoli", "Spinach"],
            "Meat" => ["Beef", "Chicken", "Pork"]
        ];

        return $categories;
    }
}
