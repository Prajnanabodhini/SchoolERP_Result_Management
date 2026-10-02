<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class LibraryIssue extends Model
{
    protected $table = 'library_issues';

    protected $fillable = [
        'borrower_type', 'borrower_id', 'borrower_name', 'borrower_class',
        'book_name', 'book_code', 'author',
        'issue_date', 'due_date', 'return_date',
        'status', 'remarks', 'created_by',
    ];

    protected $casts = [
        'issue_date'  => 'date',
        'due_date'    => 'date',
        'return_date' => 'date',
    ];

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'issued'
            && $this->due_date
            && $this->due_date->lt(Carbon::today());
    }

    public function getDaysOverdueAttribute(): int
    {
        return $this->is_overdue ? $this->due_date->diffInDays(Carbon::today()) : 0;
    }
}