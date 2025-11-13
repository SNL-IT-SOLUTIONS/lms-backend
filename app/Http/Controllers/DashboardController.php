<?php

namespace App\Http\Controllers;

use App\Models\Books;
use App\Models\Transactions;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Get dashboard summary
     */
    public function dashboardSummary()
    {
        // Total books (excluding archived)
        $totalBooks = Books::where('is_archived', false)->count();

        // Available books
        $availableBooks = Books::where('status', 'Available')
            ->where('is_archived', false)
            ->count();

        // Active borrowers (distinct names from transactions that are currently borrowed)
        $activeBorrowers = Transactions::where('status', 'borrowed')
            ->distinct('borrower_name')
            ->count('borrower_name');

        // Total transactions (all transactions)
        $totalTransactions = Transactions::count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_books' => $totalBooks,
                'available_books' => $availableBooks,
                'active_borrowers' => $activeBorrowers,
                'total_transactions' => $totalTransactions,
            ],
        ]);
    }
}
