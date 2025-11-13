<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class CategoriesController extends Controller
{
    // ✅ List all categories
    public function listCategories()
    {
        try {
            $categories = Category::orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data'    => $categories,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching categories: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch categories.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ✅ Get single category
    public function getCategory($id)
    {
        try {
            $category = Category::find($id);

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data'    => $category,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching category: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch category.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ✅ Create a new category
    public function createCategory(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'category_name'        => 'required|string|unique:categories,category_name',
                'category_description' => 'nullable|string',
                'who_edited'           => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $category = Category::create([
                'category_name'        => $request->category_name,
                'category_description' => $request->category_description,
                'who_edited'           => $request->who_edited,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully.',
                'data'    => $category,
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating category: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create category.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ✅ Update a category
    public function updateCategory(Request $request, $id)
    {
        try {
            $category = Category::find($id);
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found.',
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'category_name'        => "required|string|unique:categories,category_name,{$id}",
                'category_description' => 'nullable|string',
                'who_edited'           => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $category->update([
                'category_name'        => $request->category_name,
                'category_description' => $request->category_description,
                'who_edited'           => $request->who_edited,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully.',
                'data'    => $category,
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating category: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update category.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ✅ Delete a category
    public function deleteCategory($id)
    {
        try {
            $category = Category::find($id);
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found.',
                ], 404);
            }

            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting category: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete category.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
