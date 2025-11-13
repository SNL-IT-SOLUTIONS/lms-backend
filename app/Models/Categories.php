<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categories extends Model
{
    use HasFactory;

    // ✅ Table name (optional if Laravel uses default plural)
    protected $table = 'categories';

    // ✅ Primary key (optional if Laravel default 'id' is used)
    protected $primaryKey = 'category_id';

    // ✅ Fillable fields
    protected $fillable = [
        'category_name',
        'category_description',
        'edited_by',
    ];

    // ✅ Dates for automatic casting
    protected $dates = [
        'date_stamped',
        'date_edited',
    ];

    // ✅ Relationship: A category has many books
    public function books()
    {
        return $this->hasMany(Book::class, 'category_id', 'category_id');
    }
}
