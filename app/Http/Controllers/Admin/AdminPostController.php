<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostCluster;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class AdminPostController extends Controller
{
    /**
     * Display listing of blog posts, categories, and clusters.
     */
    public function index(Request $request): View
    {
        $activeTab = $request->get('tab', 'posts');

        $query = Post::with(['postCategory', 'postCluster'])->latest();

        if ($cluster = $request->get('cluster')) {
            $query->where('cluster', $cluster);
        }

        if ($categoryId = $request->get('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($status = $request->get('status')) {
            if ($status === 'published') {
                $query->where('is_published', true);
            } elseif ($status === 'draft') {
                $query->where('is_published', false);
            }
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        $posts = $query->paginate(15)->withQueryString();

        $categories = PostCategory::withCount('posts')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $clusters = PostCluster::withCount('posts')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.posts.index', [
            'activeTab' => $activeTab,
            'posts' => $posts,
            'categories' => $categories,
            'clusters' => $clusters,
            'totalPosts' => Post::count(),
            'publishedPosts' => Post::where('is_published', true)->count(),
            'draftPosts' => Post::where('is_published', false)->count(),
            'totalTutorial' => Post::where('cluster', 'tutorial')->count(),
            'totalEdukasi' => Post::where('cluster', 'edukasi')->count(),
            'totalCategories' => $categories->count(),
            'totalClusters' => $clusters->count(),
        ]);
    }

    /**
     * Show create form.
     */
    public function create(): View
    {
        $categories = PostCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $clusters = PostCluster::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.posts.create', [
            'categories' => $categories,
            'clusters' => $clusters,
        ]);
    }

    /**
     * Store new post.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:posts,slug',
            'cluster_id' => 'nullable|exists:post_clusters,id',
            'cluster' => 'nullable|string|in:tutorial,edukasi',
            'category_id' => 'nullable|exists:post_categories,id',
            'category' => 'nullable|string|max:100',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'cover_image' => 'nullable|url|max:500',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:300',
            'author_name' => 'nullable|string|max:100',
            'is_published' => 'nullable|boolean',
        ]);

        // Dual-sync Cluster ID & Cluster string code
        if (!empty($validated['cluster_id'])) {
            $clusterModel = PostCluster::find($validated['cluster_id']);
            if ($clusterModel) {
                $validated['cluster'] = $clusterModel->code;
            }
        } elseif (!empty($validated['cluster'])) {
            $clusterModel = PostCluster::where('code', $validated['cluster'])->first();
            if ($clusterModel) {
                $validated['cluster_id'] = $clusterModel->id;
            }
        } else {
            // Default to edukasi
            $clusterModel = PostCluster::where('code', 'edukasi')->first();
            if ($clusterModel) {
                $validated['cluster_id'] = $clusterModel->id;
                $validated['cluster'] = $clusterModel->code;
            } else {
                $validated['cluster'] = 'edukasi';
            }
        }

        // Dual-sync Category ID & Category string name
        if (!empty($validated['category_id'])) {
            $categoryModel = PostCategory::find($validated['category_id']);
            if ($categoryModel) {
                $validated['category'] = $categoryModel->name;
            }
        } elseif (!empty($validated['category'])) {
            $categoryModel = PostCategory::firstOrCreate(
                ['name' => trim($validated['category'])],
                ['slug' => Str::slug($validated['category']), 'color' => 'blue', 'is_active' => true]
            );
            $validated['category_id'] = $categoryModel->id;
        } else {
            $validated['category'] = 'Umum';
        }

        $validated['slug'] = !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['title']);
        $validated['is_published'] = $request->boolean('is_published');
        $validated['published_at'] = $validated['is_published'] ? now() : null;
        $validated['author_name'] = !empty($validated['author_name']) ? $validated['author_name'] : 'Tim Edukasi COOCA';

        Post::create($validated);

        return redirect()->route('admin.posts.index')->with('success', 'Artikel berhasil diterbitkan.');
    }

    /**
     * Show edit form.
     */
    public function edit(Post $post): View
    {
        $categories = PostCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $clusters = PostCluster::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.posts.edit', [
            'post' => $post,
            'categories' => $categories,
            'clusters' => $clusters,
        ]);
    }

    /**
     * Update post.
     */
    public function update(Request $request, Post $post): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:posts,slug,' . $post->id,
            'cluster_id' => 'nullable|exists:post_clusters,id',
            'cluster' => 'nullable|string|in:tutorial,edukasi',
            'category_id' => 'nullable|exists:post_categories,id',
            'category' => 'nullable|string|max:100',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'cover_image' => 'nullable|url|max:500',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:300',
            'author_name' => 'nullable|string|max:100',
            'is_published' => 'nullable|boolean',
        ]);

        // Dual-sync Cluster ID & Cluster string code
        if (!empty($validated['cluster_id'])) {
            $clusterModel = PostCluster::find($validated['cluster_id']);
            if ($clusterModel) {
                $validated['cluster'] = $clusterModel->code;
            }
        } elseif (!empty($validated['cluster'])) {
            $clusterModel = PostCluster::where('code', $validated['cluster'])->first();
            if ($clusterModel) {
                $validated['cluster_id'] = $clusterModel->id;
            }
        }

        // Dual-sync Category ID & Category string name
        if (!empty($validated['category_id'])) {
            $categoryModel = PostCategory::find($validated['category_id']);
            if ($categoryModel) {
                $validated['category'] = $categoryModel->name;
            }
        } elseif (!empty($validated['category'])) {
            $categoryModel = PostCategory::firstOrCreate(
                ['name' => trim($validated['category'])],
                ['slug' => Str::slug($validated['category']), 'color' => 'blue', 'is_active' => true]
            );
            $validated['category_id'] = $categoryModel->id;
        }

        $validated['slug'] = Str::slug($validated['slug']);
        $validated['is_published'] = $request->boolean('is_published');

        if ($validated['is_published'] && !$post->published_at) {
            $validated['published_at'] = now();
        } elseif (!$validated['is_published']) {
            $validated['published_at'] = null;
        }

        $post->update($validated);

        return redirect()->route('admin.posts.index')->with('success', 'Artikel berhasil diperbarui.');
    }

    /**
     * Delete post.
     */
    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        return redirect()->route('admin.posts.index')->with('success', 'Artikel telah dihapus.');
    }

    /**
     * Toggle published status.
     */
    public function toggleStatus(Post $post): RedirectResponse
    {
        $newStatus = !$post->is_published;
        $post->update([
            'is_published' => $newStatus,
            'published_at' => $newStatus ? ($post->published_at ?? now()) : null,
        ]);

        return back()->with('success', 'Status artikel berhasil diubah.');
    }

    /**
     * Store new category (supports AJAX for inline modal).
     */
    public function storeCategory(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:post_categories,name',
            'slug' => 'nullable|string|max:100|unique:post_categories,slug',
            'description' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:30',
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['slug'] = !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name']);
        $validated['color'] = $validated['color'] ?? 'blue';
        $validated['icon'] = $validated['icon'] ?? 'tag';
        $validated['is_active'] = true;
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $category = PostCategory::create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Kategori berhasil ditambahkan.',
                'category' => $category,
            ]);
        }

        return redirect()->route('admin.posts.index', ['tab' => 'categories'])->with('success', 'Kategori baru berhasil ditambahkan.');
    }

    /**
     * Update category.
     */
    public function updateCategory(Request $request, PostCategory $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:post_categories,name,' . $category->id,
            'slug' => 'required|string|max:100|unique:post_categories,slug,' . $category->id,
            'description' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:30',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['slug'] = Str::slug($validated['slug']);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        $category->update($validated);

        // Keep post category strings in sync if changed
        Post::where('category_id', $category->id)->update(['category' => $category->name]);

        return redirect()->route('admin.posts.index', ['tab' => 'categories'])->with('success', 'Kategori berhasil diperbarui.');
    }

    /**
     * Delete category.
     */
    public function destroyCategory(PostCategory $category): RedirectResponse
    {
        // Disassociate posts rather than deleting them
        Post::where('category_id', $category->id)->update(['category_id' => null]);
        $category->delete();

        return redirect()->route('admin.posts.index', ['tab' => 'categories'])->with('success', 'Kategori telah dihapus.');
    }

    /**
     * Store new cluster.
     */
    public function storeCluster(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:post_clusters,code',
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:100|unique:post_clusters,slug',
            'description' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['code'] = Str::lower(trim($validated['code']));
        $validated['slug'] = !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['code']);
        $validated['icon'] = $validated['icon'] ?? 'layers';
        $validated['is_active'] = true;
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $cluster = PostCluster::create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Cluster berhasil ditambahkan.',
                'cluster' => $cluster,
            ]);
        }

        return redirect()->route('admin.posts.index', ['tab' => 'clusters'])->with('success', 'Cluster konten baru berhasil ditambahkan.');
    }

    /**
     * Update cluster.
     */
    public function updateCluster(Request $request, PostCluster $cluster): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:post_clusters,code,' . $cluster->id,
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:post_clusters,slug,' . $cluster->id,
            'description' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['code'] = Str::lower(trim($validated['code']));
        $validated['slug'] = Str::slug($validated['slug']);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        $cluster->update($validated);

        // Keep post cluster strings in sync
        Post::where('cluster_id', $cluster->id)->update(['cluster' => $cluster->code]);

        return redirect()->route('admin.posts.index', ['tab' => 'clusters'])->with('success', 'Cluster konten berhasil diperbarui.');
    }

    /**
     * Delete cluster.
     */
    public function destroyCluster(PostCluster $cluster): RedirectResponse
    {
        // Disassociate posts
        Post::where('cluster_id', $cluster->id)->update(['cluster_id' => null]);
        $cluster->delete();

        return redirect()->route('admin.posts.index', ['tab' => 'clusters'])->with('success', 'Cluster konten telah dihapus.');
    }
}
