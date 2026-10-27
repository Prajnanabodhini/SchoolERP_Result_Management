<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LibraryBook extends Model
{
    protected $table = 'library_books';

    protected $fillable = [
        'book_code',
        'book_name',
        'author',
        'publisher',
        'publication_year',
        'edition',
        'total_pages',
        'donor_name',
        'bill_no',
        'bill_date',
        'printed_price',
        'remarks',
        'category_id',
        'total_copies',
        'available_copies',
        'shelf_location',
        'is_active',
    ];

    protected $casts = [
        'is_active'        => 'boolean',
        'total_copies'     => 'integer',
        'available_copies' => 'integer',
        'publication_year' => 'integer',
        'total_pages'      => 'integer',
        'bill_date'        => 'date',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(LibraryBookCategory::class, 'category_id');
    }
}