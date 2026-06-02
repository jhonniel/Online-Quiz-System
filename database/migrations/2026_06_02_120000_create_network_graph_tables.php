<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('network_graph_nodes', function (Blueprint $table) {
            $table->id();
            $table->string('node_key')->unique();
            $table->string('label');
            $table->string('node_group', 32)->index();
            $table->string('color', 20)->nullable();
            $table->unsignedBigInteger('hit_count')->default(0);
            $table->json('meta')->nullable();
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });

        Schema::create('network_graph_edges', function (Blueprint $table) {
            $table->id();
            $table->string('source_node_key');
            $table->string('target_node_key');
            $table->string('edge_type', 32)->default('traffic');
            $table->unsignedBigInteger('weight')->default(0);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->unique(['source_node_key', 'target_node_key', 'edge_type'], 'network_graph_edges_unique');
            $table->index('source_node_key');
            $table->index('target_node_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('network_graph_edges');
        Schema::dropIfExists('network_graph_nodes');
    }
};
