<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $row = DB::table('ad_networks')->where('slug', 'monetag')->first();

        if ($row === null) {
            return;
        }

        $configuration = json_decode((string) $row->configuration, true);
        $configuration = is_array($configuration) ? $configuration : [];
        $configuration['head'] = '<meta name="monetag" content="2b1599a262976829a3dc2a5889acbe2d">';

        DB::table('ad_networks')->where('id', $row->id)->update([
            'configuration' => json_encode($configuration),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        $row = DB::table('ad_networks')->where('slug', 'monetag')->first();

        if ($row === null) {
            return;
        }

        $configuration = json_decode((string) $row->configuration, true);
        $configuration = is_array($configuration) ? $configuration : [];
        unset($configuration['head']);

        DB::table('ad_networks')->where('id', $row->id)->update([
            'configuration' => $configuration === [] ? null : json_encode($configuration),
            'updated_at' => now(),
        ]);
    }
};
