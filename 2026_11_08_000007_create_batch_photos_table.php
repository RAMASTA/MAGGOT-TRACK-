<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('batch_photos', function (Blueprint $t) {
            $t->id();
            $t->foreignId('batch_id')->constrained()->cascadeOnDelete();
            $t->foreignId('growth_log_id')->nullable()->constrained('growth_logs')->nullOnDelete();
            $t->string('image_path');
            $t->string('description')->nullable();
            $t->timestamps();

            $t->index('batch_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_photos');
    }
};
