<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Categories;
use Illuminate\Support\Facades\Log;

class DropdownController extends Controller
{
    // ✅ Get categories for dropdown
    public function dropdownCategories()
    {
        try {
            $categories = Categories::where('is_archived', false)
                ->orderBy('category_name', 'asc')
                ->get(['category_id', 'category_name']); // only send what’s needed

            return response()->json([
                'success' => true,
                'data'    => $categories,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching categories for dropdown: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch categories.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
