<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class BatchPhoto extends Model
{
    protected $fillable = ['batch_id', 'growth_log_id', 'image_path', 'description'];

    public function batch() { return $this->belongsTo(Batch::class); }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->image_path);
    }
}
