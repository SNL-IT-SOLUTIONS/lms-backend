<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transactions extends Model
{
    use HasFactory;

    // Optional if your table name is standard "transactions"
    protected $table = 'transactions';

    // Allow mass assignment for these columns
    protected $fillable = [
        'book_id',
        'borrower_name',
        'borrower_email',
        'borrow_date',
        'due_date',
        'return_date',
        'status',
    ];

    // If you want to customize timestamps format
    protected $dates = [
        'borrow_date',
        'due_date',
        'return_date',
        'created_at',
        'updated_at',
    ];

    // Example: define relation to Book model
    public function book()
    {
        return $this->belongsTo(Books::class, 'book_id');
    }
}
