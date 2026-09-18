<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('post_clusters', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g. 'tutorial', 'edukasi'
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->default('layers');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('post_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('color')->nullable()->default('#007AFF');
            $table->string('icon')->default('folder');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->foreignId('cluster_id')->nullable()->after('cluster')->constrained('post_clusters')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->after('category')->constrained('post_categories')->nullOnDelete();
        });

        // Seed standard initial clusters
        $clusters = [
            [
                'code' => 'tutorial',
                'name' => 'Cluster K - Tutorial Cara',
                'slug' => 'tutorial',
                'description' => 'Panduan teknis langkah demi langkah operasional bisnis, POS kasir, dan resep usaha',
                'icon' => 'wrench',
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'edukasi',
                'name' => 'Cluster O - Edukasi Topikal',
                'slug' => 'edukasi',
                'description' => 'Wawasan bisnis, manajemen keuangan, kalkulasi HPP, dan strategi pertumbuhan UMKM',
                'icon' => 'graduation-cap',
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        DB::table('post_clusters')->insert($clusters);

        // Seed standard initial categories
        $categories = [
            [
                'name' => 'HPP & Biaya',
                'slug' => 'hpp-biaya',
                'description' => 'Penentuan harga pokok penjualan, biaya bahan, dan kalkulasi margin keuntungan',
                'color' => '#34C759',
                'icon' => 'calculator',
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pembukuan & Akuntansi',
                'slug' => 'pembukuan-akuntansi',
                'description' => 'Pencatatan keuangan, arus kas berjalan, dan jurnal akuntansi otomatis',
                'color' => '#007AFF',
                'icon' => 'book-open',
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Operasional Kasir & POS',
                'slug' => 'operasional-kasir-pos',
                'description' => 'Manajemen meja kasir, shift harian, dan penerimaan transaksi',
                'color' => '#FF9500',
                'icon' => 'monitor',
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Inventori & Bahan Baku',
                'slug' => 'inventori-bahan-baku',
                'description' => 'Manajemen stok gudang, resep BOM, dan opname persediaan',
                'color' => '#5856D6',
                'icon' => 'boxes',
                'is_active' => true,
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pemasaran & Toko Online',
                'slug' => 'pemasaran-toko-online',
                'description' => 'Strategi penjualan online, pre-order batch, dan etalase digital',
                'color' => '#AF52DE',
                'icon' => 'shopping-bag',
                'is_active' => true,
                'sort_order' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Umum',
                'slug' => 'umum',
                'description' => 'Informasi umum platform dan wawasan perkembangan UMKM',
                'color' => '#8E8E93',
                'icon' => 'info',
                'is_active' => true,
                'sort_order' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        DB::table('post_categories')->insert($categories);

        // Auto-link any existing posts to category and cluster IDs
        $clusterMap = DB::table('post_clusters')->pluck('id', 'code');
        $categoryMap = DB::table('post_categories')->pluck('id', 'name');

        $existingPosts = DB::table('posts')->select('id', 'cluster', 'category')->get();
        foreach ($existingPosts as $post) {
            $update = [];
            if ($post->cluster && isset($clusterMap[$post->cluster])) {
                $update['cluster_id'] = $clusterMap[$post->cluster];
            }
            if ($post->category && isset($categoryMap[$post->category])) {
                $update['category_id'] = $categoryMap[$post->category];
            } elseif ($post->category) {
                $catSlug = Str::slug($post->category);
                $catId = DB::table('post_categories')->insertGetId([
                    'name' => $post->category,
                    'slug' => $catSlug . '-' . Str::random(4),
                    'color' => '#007AFF',
                    'icon' => 'folder',
                    'is_active' => true,
                    'sort_order' => 99,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $categoryMap[$post->category] = $catId;
                $update['category_id'] = $catId;
            }
            if (! empty($update)) {
                DB::table('posts')->where('id', $post->id)->update($update);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['cluster_id']);
            $table->dropForeign(['category_id']);
            $table->dropColumn(['cluster_id', 'category_id']);
        });

        Schema::dropIfExists('post_categories');
        Schema::dropIfExists('post_clusters');
    }
};
