<?php

namespace Tests\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Category;
use App\Models\Product;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function create_category()
    {
        $category = Category::create(['name' => 'Electronics']);

        // Assert that the product is attached to the category
        $this->assertTrue($category->name=='Electronics');
    }

    /** @test */
    public function create_product()
    {
        $category = Category::create(['name' => 'Electronics']);
        $product = Product::create(['name' => 'Laptop','price'=>200]);

        // Attach the product to the category
        $category->products()->attach($product->id);

        // Assert that the product is attached to the category
        $this->assertTrue($category->products->contains($product));
    }
}
