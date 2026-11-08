<?php

namespace App\Models;

use App\Models\Concerns\ScopedToUser;
use Illuminate\Database\Eloquent\Model;

class Harvest extends Model
{
    use ScopedToUser;

    protected $fillable = ['batch_id', 'harvest_date', 'harvest_weight', 'harvest_condition', 'utilization', 'notes'];
    protected $casts = ['harvest_date' => 'date', 'harvest_weight' => 'float'];
}
