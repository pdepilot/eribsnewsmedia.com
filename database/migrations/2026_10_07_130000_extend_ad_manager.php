<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ad_networks', function (Blueprint $table) {
            $table->string('website_url')->nullable()->after('type');
            $table->text('notes')->nullable()->after('publisher_id');
        });

        Schema::table('ad_placements', function (Blueprint $table) {
            $table->string('location')->nullable()->after('slug');
        });

        Schema::table('ad_units', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
            $table->string('page_target')->default('all')->after('device');
            $table->unsignedSmallInteger('weight')->default(100)->after('priority');
            $table->longText('fallback_code')->nullable()->after('markup');
            $table->timestamp('starts_at')->nullable()->after('fallback_code');
            $table->timestamp('ends_at')->nullable()->after('starts_at');
        });

        Schema::table('advertisers', function (Blueprint $table) {
            $table->string('contact_name')->nullable()->after('company');
            $table->string('address')->nullable()->after('website');
            $table->text('notes')->nullable()->after('address');
        });

        Schema::table('ad_campaigns', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
            $table->string('type')->default('direct')->after('slug');
            $table->decimal('budget', 12, 2)->nullable()->after('type');
            $table->unsignedSmallInteger('weight')->default(100)->after('priority');
            $table->text('notes')->nullable()->after('click_url');
        });

        Schema::table('advertisement_creatives', function (Blueprint $table) {
            $table->string('destination_url')->nullable()->after('image_path');
            $table->string('status')->default('active')->after('height');
        });

        Schema::create('ad_placement_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_unit_id')->constrained('ad_units')->cascadeOnDelete();
            $table->string('page_type')->default('all');
            $table->string('device')->default('all');
            $table->unsignedSmallInteger('priority')->default(0);
            $table->unsignedSmallInteger('weight')->default(100);
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->index(['ad_unit_id', 'status']);
        });

        Schema::create('ad_impressions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_unit_id')->nullable()->constrained('ad_units')->nullOnDelete();
            $table->foreignId('ad_placement_id')->nullable()->constrained('ad_placements')->nullOnDelete();
            $table->foreignId('ad_campaign_id')->nullable()->constrained('ad_campaigns')->nullOnDelete();
            $table->string('page_type')->nullable();
            $table->string('device')->nullable();
            $table->string('session_hash', 64)->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->timestamp('occurred_at')->useCurrent()->index();
            $table->index(['ad_placement_id', 'occurred_at']);
            $table->index(['ad_unit_id', 'occurred_at']);
        });

        Schema::create('ad_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_unit_id')->nullable()->constrained('ad_units')->nullOnDelete();
            $table->foreignId('ad_placement_id')->nullable()->constrained('ad_placements')->nullOnDelete();
            $table->foreignId('ad_campaign_id')->nullable()->constrained('ad_campaigns')->nullOnDelete();
            $table->string('page_type')->nullable();
            $table->string('device')->nullable();
            $table->string('session_hash', 64)->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->timestamp('occurred_at')->useCurrent()->index();
            $table->index(['ad_placement_id', 'occurred_at']);
            $table->index(['ad_unit_id', 'occurred_at']);
        });

        Schema::create('ads_txt_entries', function (Blueprint $table) {
            $table->id();
            $table->string('advertising_system');
            $table->string('publisher_account_id');
            $table->string('relationship');
            $table->string('certification_authority_id')->nullable();
            $table->boolean('status')->default(true)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ads_txt_entries');
        Schema::dropIfExists('ad_clicks');
        Schema::dropIfExists('ad_impressions');
        Schema::dropIfExists('ad_placement_rules');

        Schema::table('advertisement_creatives', function (Blueprint $table) {
            $table->dropColumn(['destination_url', 'status']);
        });

        Schema::table('ad_campaigns', function (Blueprint $table) {
            $table->dropColumn(['slug', 'type', 'budget', 'weight', 'notes']);
        });

        Schema::table('advertisers', function (Blueprint $table) {
            $table->dropColumn(['contact_name', 'address', 'notes']);
        });

        Schema::table('ad_units', function (Blueprint $table) {
            $table->dropColumn(['slug', 'page_target', 'weight', 'fallback_code', 'starts_at', 'ends_at']);
        });

        Schema::table('ad_placements', function (Blueprint $table) {
            $table->dropColumn('location');
        });

        Schema::table('ad_networks', function (Blueprint $table) {
            $table->dropColumn(['website_url', 'notes']);
        });
    }
};
