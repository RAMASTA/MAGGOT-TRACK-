<?php

namespace App\Models;

use App\Models\Concerns\ScopedToUser;
use Illuminate\Database\Eloquent\Model;

class Feeding extends Model
{
    use ScopedToUser;

    protected $fillable = ['batch_id', 'feeding_date', 'feed_type', 'feed_weight', 'waste_source', 'notes'];
    protected $casts = ['feeding_date' => 'date', 'feed_weight' => 'float'];
}
