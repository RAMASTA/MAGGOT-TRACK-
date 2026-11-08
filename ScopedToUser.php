<?php

namespace App\Models\Concerns;

use App\Models\Batch;

/** Dipakai model turunan batch agar query selalu bisa dibatasi pada pemilik. */
trait ScopedToUser
{
    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->whereHas('batch', fn ($b) => $b->where('user_id', $userId));
    }
}
