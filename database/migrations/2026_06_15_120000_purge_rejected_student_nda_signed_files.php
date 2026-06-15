<?php

use App\Models\StudentNda;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        StudentNda::query()
            ->where('approval_status', StudentNda::STATUS_REJECTED)
            ->whereNotNull('signed_document_path')
            ->where('signed_document_path', '!=', '')
            ->orderBy('id')
            ->each(function (StudentNda $nda): void {
                $nda->removeSignedDocument();
            });
    }

    public function down(): void
    {
        // Files removed from storage cannot be restored.
    }
};
