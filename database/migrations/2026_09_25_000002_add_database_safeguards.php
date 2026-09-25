<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->timestamp('reminders_consented_at')->nullable()->after('accepts_reminders');
        });

        DB::statement('UPDATE clients SET reminders_consented_at = updated_at WHERE accepts_reminders');
        DB::statement('CREATE UNIQUE INDEX clients_document_index_unique ON clients (document_index) WHERE deleted_at IS NULL AND document_index IS NOT NULL');

        Schema::create('data_audits', function (Blueprint $table) {
            $table->id();
            $table->string('table_name', 63);
            $table->unsignedBigInteger('row_id');
            $table->string('operation', 6);
            $table->jsonb('changed_columns')->nullable();
            $table->unsignedBigInteger('app_user_id')->nullable();
            $table->string('db_user', 63);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['table_name', 'row_id']);
        });

        // Column names only: copying values would duplicate personal data
        // (and CPF/CNPJ is encrypted anyway). app.user_id is set per request
        // by the SetDatabaseActor middleware.
        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION audit_row_change() RETURNS trigger LANGUAGE plpgsql AS $$
            DECLARE
                changed jsonb;
            BEGIN
                IF TG_OP = 'UPDATE' THEN
                    SELECT jsonb_agg(n.key ORDER BY n.key) INTO changed
                    FROM jsonb_each(to_jsonb(NEW)) AS n
                    WHERE n.value IS DISTINCT FROM to_jsonb(OLD) -> n.key
                      AND n.key NOT IN ('updated_at', 'remember_token');

                    IF changed IS NULL THEN
                        RETURN NULL;
                    END IF;
                END IF;

                INSERT INTO data_audits (table_name, row_id, operation, changed_columns, app_user_id, db_user, created_at)
                VALUES (
                    TG_TABLE_NAME,
                    (CASE WHEN TG_OP = 'DELETE' THEN to_jsonb(OLD) ELSE to_jsonb(NEW) END ->> 'id')::bigint,
                    TG_OP,
                    changed,
                    NULLIF(current_setting('app.user_id', true), '')::bigint,
                    current_user,
                    now()
                );

                RETURN NULL;
            END;
            $$;

            CREATE OR REPLACE FUNCTION reject_change() RETURNS trigger LANGUAGE plpgsql AS $$
            BEGIN
                RAISE EXCEPTION '% on % is not allowed: %', TG_OP, TG_TABLE_NAME, TG_ARGV[0]
                    USING ERRCODE = 'restrict_violation';
            END;
            $$;

            CREATE TRIGGER clients_audit AFTER INSERT OR UPDATE OR DELETE ON clients
                FOR EACH ROW EXECUTE FUNCTION audit_row_change();
            CREATE TRIGGER users_audit AFTER INSERT OR UPDATE OR DELETE ON users
                FOR EACH ROW EXECUTE FUNCTION audit_row_change();

            CREATE TRIGGER data_audits_append_only BEFORE UPDATE OR DELETE ON data_audits
                FOR EACH ROW EXECUTE FUNCTION reject_change('the audit trail is append-only');
            CREATE TRIGGER data_audits_no_truncate BEFORE TRUNCATE ON data_audits
                FOR EACH STATEMENT EXECUTE FUNCTION reject_change('the audit trail is append-only');

            CREATE TRIGGER appointment_activities_append_only BEFORE UPDATE OR DELETE ON appointment_activities
                FOR EACH ROW EXECUTE FUNCTION reject_change('the appointment history is append-only');
            CREATE TRIGGER appointment_activities_no_truncate BEFORE TRUNCATE ON appointment_activities
                FOR EACH STATEMENT EXECUTE FUNCTION reject_change('the appointment history is append-only');

            CREATE TRIGGER appointments_keep_rows BEFORE DELETE ON appointments
                FOR EACH ROW EXECUTE FUNCTION reject_change('appointments are cancelled or soft deleted');

            CREATE VIEW appointment_facts AS
            SELECT
                a.id,
                a.starts_at,
                a.ends_at,
                a.status,
                a.location_type,
                s.name AS service,
                c.name AS category,
                cl.type AS client_type,
                a.responsible_user_id,
                a.created_at
            FROM appointments a
            JOIN services s ON s.id = a.service_id
            JOIN service_categories c ON c.id = s.service_category_id
            JOIN clients cl ON cl.id = a.client_id
            WHERE a.deleted_at IS NULL;
            SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
            DROP VIEW IF EXISTS appointment_facts;
            DROP TRIGGER IF EXISTS appointments_keep_rows ON appointments;
            DROP TRIGGER IF EXISTS appointment_activities_no_truncate ON appointment_activities;
            DROP TRIGGER IF EXISTS appointment_activities_append_only ON appointment_activities;
            DROP TRIGGER IF EXISTS data_audits_no_truncate ON data_audits;
            DROP TRIGGER IF EXISTS data_audits_append_only ON data_audits;
            DROP TRIGGER IF EXISTS users_audit ON users;
            DROP TRIGGER IF EXISTS clients_audit ON clients;
            DROP FUNCTION IF EXISTS reject_change();
            DROP FUNCTION IF EXISTS audit_row_change();
            SQL);

        Schema::dropIfExists('data_audits');
        DB::statement('DROP INDEX IF EXISTS clients_document_index_unique');

        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('reminders_consented_at');
        });
    }
};
