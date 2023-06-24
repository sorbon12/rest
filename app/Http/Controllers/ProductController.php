<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $query = Product::query();
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        if ($request->has('category_id')) {
            $query->whereHas('categories', function ($query) use ($request) {
                $query->where('categories.id', $request->input('category_id'));
            });
        }
        if ($request->has('category_name')) {
            $query->whereHas('categories', function ($query) use ($request) {
                $query->where('categories.name', 'like', '%' . $request->input('category_name') . '%');
            });
        }

        if ($request->has('price_from')) {
            $query->where('price', '>=', $request->input('price_from'));
        }

        if ($request->has('price_to')) {
            $query->where('price', '<=', $request->input('price_to'));
        }

        $query->where('published', $request->input('published', true))
            ->where('deleted', false);

        $products = $query->get();
        
        return response()->json($products);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'published' => 'boolean',
            'categories' => 'required|array',
            'categories.*' => 'exists:categories,id'
        ]);
        $product = new Product();
        $product->name = $validatedData['name'];
        $product->price = $validatedData['price'];
        $product->published = $validatedData['published'] ?? true;
        $product->deleted = false;
        $product->save();
        $product->categories()->attach($validatedData['categories']);
    
        return response()->json($product);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $product = Product::findOrFail($id);
        return response()->json($product);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'published' => 'boolean',
            'categories' => 'array',
            'categories.*' => 'exists:categories,id'
        ]);
    
        $product = Product::findOrFail($id);
        $product->name = $validatedData['name'];
        $product->price = $validatedData['price'];
        $product->published = $validatedData['published'] ?? true;
        $product->save();
    
        if (isset($validatedData['categories'])) {
            $product->categories()->sync($validatedData['categories']);
        }
    
        return response()->json($product);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->deleted = true;
        $product->save();
        return response()->json(["status"=>'success']);
    }
}
