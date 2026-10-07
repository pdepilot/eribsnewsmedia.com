<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_networks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->index();
            $table->string('publisher_id')->nullable();
            $table->boolean('enabled')->default(false)->index();
            $table->unsignedSmallInteger('priority')->default(0)->index();
            $table->json('configuration')->nullable();
            $table->text('credentials')->nullable();
            $table->timestamps();
        });

        Schema::create('ad_placements', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->string('device')->default('all')->index();
            $table->boolean('enabled')->default(true)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('advertisers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->string('status')->default('active')->index();
            $table->timestamps();
        });

        Schema::create('advertisement_creatives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advertiser_id')->nullable()->constrained('advertisers')->nullOnDelete();
            $table->string('name');
            $table->string('type')->default('image');
            $table->string('image_path')->nullable();
            $table->longText('html')->nullable();
            $table->string('alt_text')->nullable();
            $table->unsignedSmallInteger('width')->nullable();
            $table->unsignedSmallInteger('height')->nullable();
            $table->timestamps();
        });

        Schema::create('ad_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_network_id')->constrained('ad_networks')->cascadeOnDelete();
            $table->foreignId('ad_placement_id')->nullable()->constrained('ad_placements')->nullOnDelete();
            $table->string('name');
            $table->string('ad_client')->nullable();
            $table->string('ad_slot')->nullable();
            $table->string('format')->nullable();
            $table->boolean('responsive')->default(true);
            $table->string('device')->default('all');
            $table->boolean('enabled')->default(false)->index();
            $table->unsignedSmallInteger('priority')->default(0);
            $table->longText('markup')->nullable();
            $table->timestamps();

            $table->index(['ad_placement_id', 'enabled']);
        });

        Schema::create('ad_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advertiser_id')->constrained('advertisers')->restrictOnDelete();
            $table->foreignId('ad_placement_id')->constrained('ad_placements')->restrictOnDelete();
            $table->foreignId('creative_id')->constrained('advertisement_creatives')->restrictOnDelete();
            $table->string('name');
            $table->timestamp('start_at')->nullable()->index();
            $table->timestamp('end_at')->nullable()->index();
            $table->unsignedSmallInteger('priority')->default(0);
            $table->string('status')->default('draft')->index();
            $table->string('click_url')->nullable();
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->timestamps();

            $table->index(['ad_placement_id', 'status', 'priority']);
        });

        Schema::create('ad_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_campaign_id')->nullable()->constrained('ad_campaigns')->cascadeOnDelete();
            $table->foreignId('ad_placement_id')->nullable()->constrained('ad_placements')->nullOnDelete();
            $table->string('type')->index();
            $table->timestamp('created_at')->useCurrent()->index();
        });

        Schema::create('ads_txt_lines', function (Blueprint $table) {
            $table->id();
            $table->string('line');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('enabled')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ads_txt_lines');
        Schema::dropIfExists('ad_events');
        Schema::dropIfExists('ad_campaigns');
        Schema::dropIfExists('ad_units');
        Schema::dropIfExists('advertisement_creatives');
        Schema::dropIfExists('advertisers');
        Schema::dropIfExists('ad_placements');
        Schema::dropIfExists('ad_networks');
    }
};
