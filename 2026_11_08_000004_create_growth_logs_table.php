<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('growth_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('batch_id')->constrained()->cascadeOnDelete();
            $t->date('measurement_date');
            $t->decimal('sample_weight', 8, 2);          // gram, BERAT SAMPEL (bukan total biomassa)
            $t->decimal('average_length', 6, 2)->nullable(); // mm
            $t->string('condition', 30);
            $t->text('notes')->nullable();
            $t->timestamps();

            $t->index(['batch_id', 'measurement_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growth_logs');
    }
};
