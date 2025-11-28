<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BoardColumn extends Model
{
    use HasFactory;

    protected $fillable = ['project_id', 'title', 'order', 'color']; // Add 'color'

    /**
     * A column belongs to a Project.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * A column has many Tasks, ordered by their 'order' column.
     * The foreign key is specified as 'column_id' in the tasks table.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'column_id')->orderBy('order');
    }
}
