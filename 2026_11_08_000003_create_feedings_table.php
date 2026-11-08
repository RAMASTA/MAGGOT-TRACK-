<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('feedings', function (Blueprint $t) {
            $t->id();
            $t->foreignId('batch_id')->constrained()->cascadeOnDelete();
            $t->date('feeding_date');
            $t->string('feed_type', 100);
            $t->decimal('feed_weight', 10, 2); // kilogram
            $t->string('waste_source', 150)->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();

            $t->index(['batch_id', 'feeding_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedings');
    }
};
