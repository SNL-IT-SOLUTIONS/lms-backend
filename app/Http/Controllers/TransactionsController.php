<?php

namespace App\Http\Controllers;

use App\Models\Transactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TransactionsController extends Controller
{
    /**
     * Display a listing of transactions.
     */
    public function listTransactions()
    {
        $transactions = Transactions::with('book')
            ->orderBy('borrow_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Transactions retrieved successfully.',
            'data'    => $transactions,
        ]);
    }


    /**
     * Display the specified transaction.
     */
    public function getTransactionById($id)
    {
        $transaction = Transactions::with('book')->find($id);

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Transaction retrieved successfully.',
            'data'    => $transaction,
        ]);
    }

    /**
     * Store a newly created transaction.
     */
    public function createTransaction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'book_id' => 'required|exists:books,book_id',
            'borrower_name'  => 'required|string|max:255',
            'borrower_email' => 'required|email|max:255',
            'borrow_date'    => 'required|date',
            'due_date'       => 'required|date|after_or_equal:borrow_date',
            'return_date'    => 'nullable|date|after_or_equal:borrow_date',
            'status'         => 'nullable|string|in:borrowed,returned,overdue',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Create the transaction
        $transaction = Transactions::create($request->all());

        // Update book status to "borrowed"
        $transaction->book()->update(['status' => 'borrowed']);

        return response()->json([
            'success' => true,
            'message' => 'Transaction created and book status updated to borrowed.',
            'data'    => $transaction->load('book'),
        ]);
    }



    /**
     * Update the specified transaction.
     */
    public function updateTransaction(Request $request, $id)
    {
        $transaction = Transactions::find($id);

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'return_date' => 'nullable|date|after_or_equal:borrow_date',
            'status'      => 'nullable|string|in:borrowed,returned,overdue',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $transaction->update($request->all());

        // If transaction is returned, update book status to Available
        if (isset($request->status) && $request->status === 'returned') {
            $transaction->book()->update(['status' => 'Available']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Transaction updated successfully.',
            'data'    => $transaction->load('book'),
        ]);
    }


    /**
     * Remove the specified transaction.
     */
    public function archiveTransaction($id)
    {
        $transaction = Transactions::find($id);

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found.',
            ], 404);
        }

        // Soft-delete using is_archived flag
        $transaction->update(['is_archived' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Transaction archived successfully.',
            'data' => $transaction,
        ]);
    }
}
