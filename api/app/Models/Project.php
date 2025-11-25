<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'user_id', 'slug'];

    // --- SLUG AND KEY NAME DEFINITION ---
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($project) {
            // Generates a random slug (e.g., 16 characters) if one is not already set
            if (empty($project->slug)) {
                $project->slug = Str::uuid();
            }
        });
    }

    /**
     * Instructs Laravel to use the 'slug' column instead of 'id'
     * for implicit route model binding.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }
    // ---------------------------------

    /**
     * A project has many Board Columns, ordered by their 'order' field.
     */
    public function columns(): HasMany
    {
        return $this->hasMany(BoardColumn::class)->orderBy('order');
    }

    /**
     * A project has many Tasks (across all columns).
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
