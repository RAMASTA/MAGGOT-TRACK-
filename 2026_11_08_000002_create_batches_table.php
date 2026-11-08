<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('batches', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('batch_code', 30)->unique();
            $t->string('batch_name', 120);
            $t->date('egg_date');
            $t->date('hatch_date')->nullable();
            $t->unsignedBigInteger('initial_population')->nullable();
            $t->string('harvest_method', 30)->nullable();
            $t->date('target_harvest_date')->nullable();
            $t->date('actual_harvest_date')->nullable();
            $t->string('status', 20)->default('persiapan');
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->softDeletes();

            $t->index(['user_id', 'status']);
            $t->index('target_harvest_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
