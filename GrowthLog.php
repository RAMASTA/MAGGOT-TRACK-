<?php

namespace App\Models;

use App\Models\Concerns\ScopedToUser;
use Illuminate\Database\Eloquent\Model;

class GrowthLog extends Model
{
    use ScopedToUser;

    protected $fillable = ['batch_id', 'measurement_date', 'sample_weight', 'average_length', 'condition', 'notes'];
    protected $casts = ['measurement_date' => 'date', 'sample_weight' => 'float', 'average_length' => 'float'];

    public function getAgeDaysAttribute(): ?int
    {
        return $this->batch ? max(0, (int) $this->batch->egg_date->diffInDays($this->measurement_date, false)) : null;
    }
}
