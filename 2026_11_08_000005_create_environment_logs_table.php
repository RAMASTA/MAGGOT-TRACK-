<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('environment_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('batch_id')->constrained()->cascadeOnDelete();
            $t->date('measurement_date');
            $t->decimal('temperature', 5, 2);   // °C
            $t->decimal('humidity', 5, 2);      // %
            $t->string('media_condition', 30);
            $t->string('odor_condition', 30);
            $t->text('notes')->nullable();
            // Persiapan integrasi IoT: sumber data dan ID perangkat sensor
            $t->string('source', 10)->default('manual');
            $t->string('device_id', 60)->nullable();
            $t->timestamps();

            $t->index(['batch_id', 'measurement_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('environment_logs');
    }
};
