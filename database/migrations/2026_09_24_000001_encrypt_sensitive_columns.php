<?php

use App\Support\BlindIndex;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * CPF/CNPJ and free-text notes (which tend to describe someone's financial
 * situation) are encrypted at rest. The document keeps an HMAC next to it so
 * it can still be found by exact value.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropIndex(['document']);
            $table->text('document')->nullable()->change();
            $table->string('document_index', 64)->nullable()->index();
        });

        DB::table('clients')->orderBy('id')->each(function (object $client): void {
            DB::table('clients')->where('id', $client->id)->update([
                'document' => $this->encrypt($client->document),
                'document_index' => BlindIndex::forDocument($client->document),
                'notes' => $this->encrypt($client->notes),
            ]);
        });

        DB::table('appointments')->orderBy('id')->each(function (object $appointment): void {
            DB::table('appointments')->where('id', $appointment->id)->update([
                'service_details' => $this->encrypt($appointment->service_details),
                'notes' => $this->encrypt($appointment->notes),
            ]);
        });
    }

    public function down(): void
    {
        DB::table('clients')->orderBy('id')->each(function (object $client): void {
            DB::table('clients')->where('id', $client->id)->update([
                'document' => $this->decrypt($client->document),
                'notes' => $this->decrypt($client->notes),
            ]);
        });

        DB::table('appointments')->orderBy('id')->each(function (object $appointment): void {
            DB::table('appointments')->where('id', $appointment->id)->update([
                'service_details' => $this->decrypt($appointment->service_details),
                'notes' => $this->decrypt($appointment->notes),
            ]);
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('document_index');
            $table->string('document', 20)->nullable()->change();
            $table->index('document');
        });
    }

    private function encrypt(?string $value): ?string
    {
        return $value === null ? null : Crypt::encryptString($value);
    }

    private function decrypt(?string $value): ?string
    {
        return $value === null ? null : Crypt::decryptString($value);
    }
};
