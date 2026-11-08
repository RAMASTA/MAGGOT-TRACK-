<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\BatchPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BatchPhotoController extends Controller
{
    public function store(Request $request, $batchId)
    {
        $batch = Batch::where('user_id', Auth::id())->findOrFail($batchId);
        $data = $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'description' => ['nullable', 'string', 'max:200'],
        ]);

        $path = $request->file('photo')->store('batches/' . $batch->id, 'public');
        $batch->photos()->create(['image_path' => $path, 'description' => $data['description'] ?? null]);

        return back()->with('success', 'Foto berhasil diunggah.');
    }

    public function destroy($batchId, $photoId)
    {
        $batch = Batch::where('user_id', Auth::id())->findOrFail($batchId);
        $photo = BatchPhoto::where('batch_id', $batch->id)->findOrFail($photoId);

        Storage::disk('public')->delete($photo->image_path);
        $photo->delete();

        return back()->with('success', 'Foto dihapus.');
    }
}
