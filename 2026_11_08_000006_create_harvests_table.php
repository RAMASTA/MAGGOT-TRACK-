<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('harvests', function (Blueprint $t) {
            $t->id();
            $t->foreignId('batch_id')->constrained()->cascadeOnDelete();
            $t->date('harvest_date');
            $t->decimal('harvest_weight', 10, 2); // kilogram
            $t->string('harvest_condition', 30);
            $t->string('utilization', 150)->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();

            $t->index(['batch_id', 'harvest_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('harvests');
    }
};
