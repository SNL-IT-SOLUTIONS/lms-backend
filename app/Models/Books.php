<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    // ✅ Table name (optional if Laravel default plural is correct)
    protected $table = 'books';

    // ✅ Primary key (optional if Laravel default 'id' is used)
    protected $primaryKey = 'bookid';

    // ✅ Fillable fields
    protected $fillable = [
        'category_id',
        'title',
        'description',
        'author',
        'date_published',
        'isbn',
        'book_image',
        'ebook_link',
    ];

    // ✅ Dates for automatic casting
    protected $dates = [
        'date_published',
        'date_modified',
        'date_stamp',
    ];

    // ✅ Relationship: A book belongs to a category
    public function category()
    {
        return $this->belongsTo(Categories::class, 'category_id', 'category_id');
    }
}
