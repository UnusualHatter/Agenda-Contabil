<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->foreignId('service_id')->constrained()->restrictOnDelete();
            $table->text('service_details')->nullable();
            $table->foreignId('responsible_user_id')->constrained('users')->restrictOnDelete();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->string('status')->default('scheduled');
            $table->string('location_type')->nullable();
            $table->string('location')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('starts_at');
            $table->index('status');
            $table->index(['responsible_user_id', 'starts_at']);
            $table->index(['client_id', 'starts_at']);
        });

        DB::statement('ALTER TABLE appointments ADD CONSTRAINT appointments_period_check CHECK (ends_at > starts_at)');

        // Backstop for the overlap rule: two concurrent requests could both pass the
        // application check, so the database refuses the second overlap. The
        // status list mirrors AppointmentStatus::blocking() at the time of
        // writing; a new blocking status needs a new migration.
        DB::statement('CREATE EXTENSION IF NOT EXISTS btree_gist');
        DB::statement(<<<'SQL'
            ALTER TABLE appointments ADD CONSTRAINT appointments_no_overlap
            EXCLUDE USING gist (
                responsible_user_id WITH =,
                tsrange(starts_at, ends_at, '[)') WITH &&
            )
            WHERE (status IN ('scheduled', 'confirmed', 'in_progress') AND deleted_at IS NULL)
            SQL);

        Schema::create('appointment_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });

        Schema::create('appointment_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event_type');
            $table->jsonb('old_values')->nullable();
            $table->jsonb('new_values')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['appointment_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_activities');
        Schema::dropIfExists('appointment_documents');
        Schema::dropIfExists('appointments');
    }
};
