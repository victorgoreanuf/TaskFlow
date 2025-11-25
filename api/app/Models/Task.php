<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'column_id',
        'name',
        'description',
        'order',
        'due_date',
        'priority',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'due_date' => 'datetime',
    ];

    /**
     * A task belongs to a Project.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * A task belongs to a BoardColumn (its current list).
     * The foreign key is 'column_id'.
     */
    public function column(): BelongsTo
    {
        return $this->belongsTo(BoardColumn::class, 'column_id');
    }
}
