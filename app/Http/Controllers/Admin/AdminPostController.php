<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class AdminPostController extends Controller
{
    /**
     * Display listing of blog posts.
     */
    public function index(Request $request): View
    {
        $query = Post::latest();

        if ($cluster = $request->get('cluster')) {
            $query->where('cluster', $cluster);
        }

        if ($search = $request->get('search')) {
            $query->where('title', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
        }

        $posts = $query->paginate(15)->withQueryString();

        return view('admin.posts.index', [
            'posts' => $posts,
            'totalPosts' => Post::count(),
            'totalTutorial' => Post::where('cluster', 'tutorial')->count(),
            'totalEdukasi' => Post::where('cluster', 'edukasi')->count(),
        ]);
    }

    /**
     * Show create form.
     */
    public function create(): View
    {
        return view('admin.posts.create');
    }

    /**
     * Store new post.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:posts,slug',
            'cluster' => 'required|in:tutorial,edukasi',
            'category' => 'required|string|max:100',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'cover_image' => 'nullable|url|max:500',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:300',
            'author_name' => 'nullable|string|max:100',
            'is_published' => 'nullable|boolean',
        ]);

        $validated['slug'] = !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['title']);
        $validated['is_published'] = $request->boolean('is_published');
        $validated['published_at'] = $validated['is_published'] ? now() : null;
        $validated['author_name'] = $validated['author_name'] ?? 'Tim Edukasi COOCA';

        Post::create($validated);

        return redirect()->route('admin.posts.index')->with('success', 'Artikel berhasil diterbitkan.');
    }

    /**
     * Show edit form.
     */
    public function edit(Post $post): View
    {
        return view('admin.posts.edit', [
            'post' => $post,
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
            'cluster' => 'required|in:tutorial,edukasi',
            'category' => 'required|string|max:100',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'cover_image' => 'nullable|url|max:500',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:300',
            'author_name' => 'nullable|string|max:100',
            'is_published' => 'nullable|boolean',
        ]);

        $validated['slug'] = Str::slug($validated['slug']);
        $validated['is_published'] = $request->boolean('is_published');

        if ($validated['is_published'] && !$post->published_at) {
            $validated['published_at'] = now();
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
        $post->update([
            'is_published' => !$post->is_published,
            'published_at' => !$post->is_published ? now() : $post->published_at,
        ]);

        return back()->with('success', 'Status artikel berhasil diubah.');
    }
}
