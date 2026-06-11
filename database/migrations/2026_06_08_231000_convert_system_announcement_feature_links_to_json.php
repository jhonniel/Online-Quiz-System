<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_announcements', function (Blueprint $table) {
            $table->json('feature_links')->nullable()->after('content');
        });

        if (Schema::hasColumn('system_announcements', 'feature_link_url')) {
            foreach (DB::table('system_announcements')->orderBy('id')->get(['id', 'feature_link_url', 'feature_link_label']) as $row) {
                $url = trim((string) ($row->feature_link_url ?? ''));
                $label = trim((string) ($row->feature_link_label ?? ''));

                if ($url === '') {
                    continue;
                }

                DB::table('system_announcements')
                    ->where('id', $row->id)
                    ->update([
                        'feature_links' => json_encode([[
                            'url' => $url,
                            'label' => $label !== '' ? $label : 'Open feature',
                        ]]),
                    ]);
            }

            Schema::table('system_announcements', function (Blueprint $table) {
                $table->dropColumn(['feature_link_url', 'feature_link_label']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('system_announcements', function (Blueprint $table) {
            $table->string('feature_link_url', 500)->nullable()->after('content');
            $table->string('feature_link_label', 100)->nullable()->after('feature_link_url');
        });

        foreach (DB::table('system_announcements')->orderBy('id')->get(['id', 'feature_links']) as $row) {
            $links = json_decode((string) ($row->feature_links ?? ''), true);
            if (! is_array($links) || $links === []) {
                continue;
            }

            $first = $links[0] ?? null;
            if (! is_array($first)) {
                continue;
            }

            DB::table('system_announcements')
                ->where('id', $row->id)
                ->update([
                    'feature_link_url' => $first['url'] ?? null,
                    'feature_link_label' => $first['label'] ?? null,
                ]);
        }

        Schema::table('system_announcements', function (Blueprint $table) {
            $table->dropColumn('feature_links');
        });
    }
};
