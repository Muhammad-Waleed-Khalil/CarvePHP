<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carve_trace_events', function (Blueprint $table) {
            $table->id();
            $table->string('trace_id')->index();
            $table->string('event_type')->index();
            $table->string('name')->nullable()->index();
            $table->string('table_name')->nullable()->index();
            $table->string('operation')->nullable();
            $table->string('class_name')->nullable();
            $table->string('method')->nullable();
            $table->integer('duration_ms')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carve_trace_events');
    }
};
