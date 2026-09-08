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
        $cluster = $request->get('cluster'); // 'tutorial' or 'edukasi'
        $search = $request->get('q');

        $query = Post::published()->latest('published_at');

        if ($cluster && in_array($cluster, ['tutorial', 'edukasi'], true)) {
            $query->where('cluster', $cluster);
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
        if (!$cluster && !$search && $posts->currentPage() === 1) {
            $featuredPost = $posts->first();
        }

        $recentTutorials = Post::published()->tutorial()->latest('published_at')->take(4)->get();
        $recentEdukasi = Post::published()->edukasi()->latest('published_at')->take(4)->get();

        return view('public.blog.index', [
            'posts' => $posts,
            'featuredPost' => $featuredPost,
            'currentCluster' => $cluster,
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
        $post = Post::published()->where('slug', $slug)->firstOrFail();

        // Increment views count safely
        $post->increment('views_count');

        $relatedPosts = Post::published()
            ->where('id', '!=', $post->id)
            ->where('cluster', $post->cluster)
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('public.blog.show', [
            'post' => $post,
            'relatedPosts' => $relatedPosts,
        ]);
    }
}
