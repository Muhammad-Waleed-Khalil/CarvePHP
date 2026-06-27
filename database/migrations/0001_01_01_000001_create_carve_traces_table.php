<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carve_traces', function (Blueprint $table) {
            $table->id();
            $table->string('trace_id')->unique()->index();
            $table->string('request_id')->nullable()->index();
            $table->string('type')->index();
            $table->string('method')->nullable();
            $table->string('uri')->nullable()->index();
            $table->string('route_name')->nullable()->index();
            $table->string('controller_action')->nullable();
            $table->string('job_class')->nullable();
            $table->string('user_id')->nullable();
            $table->integer('status_code')->nullable();
            $table->timestamp('started_at')->nullable()->index();
            $table->timestamp('ended_at')->nullable();
            $table->integer('duration_ms')->nullable();
            $table->string('exception_class')->nullable();
            $table->text('exception_message')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carve_traces');
    }
};
