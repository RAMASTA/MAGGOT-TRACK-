<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Feeding;
use App\Models\Harvest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate(['q' => 'nullable|string|max:100']);
        $term = trim((string) $request->q);
        $uid = Auth::id();
        $like = '%' . addcslashes($term, '%_\\') . '%';

        $batches = collect();
        $feedings = collect();
        $harvests = collect();

        if (mb_strlen($term) >= 2) {
            $batches = Batch::where('user_id', $uid)
                ->where(fn ($q) => $q->where('batch_name', 'like', $like)->orWhere('batch_code', 'like', $like)->orWhere('notes', 'like', $like))
                ->limit(10)->get();
            $feedings = Feeding::forUser($uid)->with('batch')
                ->where(fn ($q) => $q->where('feed_type', 'like', $like)->orWhere('waste_source', 'like', $like)->orWhere('notes', 'like', $like))
                ->latest('feeding_date')->limit(10)->get();
            $harvests = Harvest::forUser($uid)->with('batch')
                ->where(fn ($q) => $q->where('utilization', 'like', $like)->orWhere('notes', 'like', $like))
                ->latest('harvest_date')->limit(10)->get();
        }

        return view('search.index', compact('term', 'batches', 'feedings', 'harvests'));
    }
}
