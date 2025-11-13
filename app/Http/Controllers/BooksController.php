<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Books;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class BooksController extends Controller
{
    //======================
    // LIST ALL BOOKS
    //======================
    public function listBooks(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10); // Default 10 per page
            $search  = $request->input('search');

            $query = Books::where('is_archived', false);

            // Apply search if provided
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('author', 'like', "%{$search}%")
                        ->orWhere('isbn', 'like', "%{$search}%");
                });
            }

            $books = $query->orderBy('date_stamped', 'desc')
                ->paginate($perPage)
                ->through(function ($book) {
                    $book->book_image = $book->book_image ? asset($book->book_image) : null;
                    return $book;
                });

            return response()->json([
                'success' => true,
                'data' => $books->items(),
                'pagination' => [
                    'total' => $books->total(),
                    'per_page' => $books->perPage(),
                    'current_page' => $books->currentPage(),
                    'last_page' => $books->lastPage(),
                    'from' => $books->firstItem(),
                    'to' => $books->lastItem(),
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching books: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch books.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    //======================
    // GET BOOK BY ID
    //======================
    public function getBookById($id)
    {
        try {
            $book = Books::where('id', $id)
                ->where('is_archived', false)
                ->first();

            if (!$book) {
                return response()->json([
                    'success' => false,
                    'message' => 'Book not found.',
                ], 404);
            }

            // Convert image path to full URL
            $book->book_image = $book->book_image ? asset($book->book_image) : null;

            return response()->json([
                'success' => true,
                'data'    => $book,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching book: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch book.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    //======================
    // CREATE BOOK
    //======================
    public function createBook(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'category_id'     => 'required|exists:categories,category_id',
                'title'           => 'required|string',
                'description'     => 'nullable|string',
                'author'          => 'required|string',
                'date_published'  => 'nullable|date',
                'isbn'            => 'nullable|string',
                'book_image'      => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'ebook_link'      => 'nullable|url',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $bookData = $request->only([
                'category_id',
                'title',
                'description',
                'author',
                'date_published',
                'isbn',
                'ebook_link',
            ]);

            //  Save book image if provided
            if ($request->hasFile('book_image')) {
                $bookData['book_image'] = $this->saveFileToPublic($request->file('book_image'), 'book');
            }

            $bookData['date_stamped'] = now();
            $bookData['date_modified'] = now();

            $book = Books::create($bookData);

            return response()->json([
                'success' => true,
                'message' => 'Book created successfully.',
                'data'    => $book,
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating book: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create book.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    //======================
    // UPDATE BOOK
    //======================
    public function updateBook(Request $request, $id)
    {
        try {
            $book = Books::find($id);
            if (!$book) {
                return response()->json([
                    'success' => false,
                    'message' => 'Book not found.',
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'category_id'     => 'nullable|exists:categories,category_id',
                'title'           => 'nullable|string',
                'description'     => 'nullable|string',
                'author'          => 'nullable|string',
                'date_published'  => 'nullable|date',
                'isbn'            => 'nullable|string',
                'book_image'      => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'ebook_link'      => 'nullable|url',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $bookData = $request->only([
                'category_id',
                'title',
                'description',
                'author',
                'date_published',
                'isbn',
                'ebook_link',
            ]);

            if ($request->hasFile('book_image')) {
                $bookData['book_image'] = $this->saveFileToPublic($request->file('book_image'), 'book');
            }

            $bookData['date_modified'] = now();

            $book->update($bookData);

            return response()->json([
                'success' => true,
                'message' => 'Book updated successfully.',
                'data'    => $book,
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating book: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update book.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    //======================
    // ARCHIVE BOOK
    //======================

    public function archiveBook($id)
    {
        try {
            $book = Books::find($id);
            if (!$book) {
                return response()->json([
                    'success' => false,
                    'message' => 'Book not found.',
                ], 404);
            }

            $book->is_archived = true;
            $book->date_modified = now();
            $book->save();

            return response()->json([
                'success' => true,
                'message' => 'Book archived successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error archiving book: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to archive book.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }



    //HELPERS
    private function saveFileToPublic($fileInput, $prefix)
    {
        $directory = public_path('lms_files');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $saveSingleFile = function ($file) use ($directory, $prefix) {
            $filename = $prefix . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($directory, $filename);
            return 'lms_files/' . $filename;
        };

        //  Case 1: Multiple files
        if (is_array($fileInput)) {
            $paths = [];
            foreach ($fileInput as $file) {
                $paths[] = $saveSingleFile($file);
            }
            return $paths; // Return array of paths
        }

        // Case 2: Single file
        if ($fileInput instanceof \Illuminate\Http\UploadedFile) {
            return $saveSingleFile($fileInput);
        }

        return null;
    }
}
