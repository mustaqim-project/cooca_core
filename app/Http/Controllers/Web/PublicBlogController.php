<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PublicBlogController extends Controller
{
    /**
     * Blog index with cluster tabs (Cluster K vs Cluster O).
     */
    public function index(Request $request): View
    {
        // Auto-populate posts if table is empty on fresh or production deployment
        if (Post::count() === 0) {
            try {
                \Illuminate\Support\Facades\Artisan::call('blog:import-articles');
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Auto-import articles failed: ' . $e->getMessage());
            }
        }

        $cluster = $request->get('cluster'); // 'tutorial' or 'edukasi'
        $category = $request->get('category');
        $search = $request->get('q');

        $query = Post::published()->latest('published_at');

        if ($cluster && in_array($cluster, ['tutorial', 'edukasi'], true)) {
            $query->where('cluster', $cluster);
        }

        if ($category) {
            $query->where('category', $category);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        $posts = $query->paginate(9)->withQueryString();

        $featuredPost = null;
        if (!$cluster && !$category && !$search && $posts->currentPage() === 1) {
            $featuredPost = $posts->first();
        }

        $recentTutorials = Post::published()->tutorial()->latest('published_at')->take(4)->get();
        $recentEdukasi = Post::published()->edukasi()->latest('published_at')->take(4)->get();
        $categories = Post::published()->distinct()->pluck('category')->filter()->values();

        return view('public.blog.index', [
            'posts' => $posts,
            'featuredPost' => $featuredPost,
            'currentCluster' => $cluster,
            'currentCategory' => $category,
            'categories' => $categories,
            'search' => $search,
            'recentTutorials' => $recentTutorials,
            'recentEdukasi' => $recentEdukasi,
        ]);
    }

    /**
     * Single post view.
     */
    public function show(string $slug): View
    {
        if (Post::count() === 0) {
            try {
                \Illuminate\Support\Facades\Artisan::call('blog:import-articles');
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Auto-import articles failed in show: ' . $e->getMessage());
            }
        }

        $post = Post::published()->where('slug', $slug)->firstOrFail();

        // Increment views only once per visitor per post (guards against bot inflation)
        $viewKey = 'post_viewed_' . $post->id;
        if (! session()->has($viewKey)) {
            $post->increment('views_count');
            session()->put($viewKey, true);
        }

        $relatedPosts = Post::published()
            ->where('id', '!=', $post->id)
            ->where(function ($q) use ($post) {
                if (!empty($post->category)) {
                    $q->where('category', $post->category);
                } elseif (!empty($post->cluster)) {
                    $q->where('cluster', $post->cluster);
                }
            })
            ->latest('published_at')
            ->take(3)
            ->get();

        if ($relatedPosts->count() < 3) {
            $existingIds = $relatedPosts->pluck('id')->push($post->id);
            $morePosts = Post::published()
                ->whereNotIn('id', $existingIds)
                ->latest('published_at')
                ->take(3 - $relatedPosts->count())
                ->get();
            $relatedPosts = $relatedPosts->concat($morePosts);
        }

        return view('public.blog.show', [
            'post' => $post,
            'relatedPosts' => $relatedPosts,
        ]);
    }
}
