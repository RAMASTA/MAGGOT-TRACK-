<?php

namespace App\Models;

use App\Models\Concerns\ScopedToUser;
use Illuminate\Database\Eloquent\Model;

class EnvironmentLog extends Model
{
    use ScopedToUser;

    protected $fillable = [
        'batch_id', 'measurement_date', 'temperature', 'humidity',
        'media_condition', 'odor_condition', 'notes', 'source', 'device_id',
    ];
    protected $casts = ['measurement_date' => 'date', 'temperature' => 'float', 'humidity' => 'float'];
}
