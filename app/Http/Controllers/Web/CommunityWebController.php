<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Storage\OwnerStorageQuotaService;
use App\Http\Controllers\Controller;
use App\Models\CommunityComment;
use App\Models\CommunityLike;
use App\Models\CommunityPost;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class CommunityWebController extends Controller
{
    public const MAX_IMAGE_BYTES = 5 * 1024 * 1024; // 5 MB

    public function __construct(
        private readonly OwnerStorageQuotaService $storageQuota = new OwnerStorageQuotaService,
    ) {}

    /**
     * Display the owner community feed.
     */
    public function index(Request $request): View
    {
        $business = Context::requireBusiness();
        $user = Context::user();

        $posts = CommunityPost::with(['owner', 'business', 'comments.user'])
            ->withCount('likes', 'comments')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // Bubble "liked by me" state without N+1 lookups.
        if ($user !== null) {
            $likedPostIds = CommunityLike::where('user_id', $user->getAuthIdentifier())
                ->whereIn('post_id', $posts->pluck('id'))
                ->pluck('post_id')
                ->flip();

            $posts->getCollection()->transform(function (CommunityPost $post) use ($likedPostIds): CommunityPost {
                $post->liked_by_me = $likedPostIds->has($post->id);
                return $post;
            });
        }

        // Storage summary for the upload warning.
        $storageSummary = $this->storageQuota->getSummary($user);

        return view('app.community.index', compact('business', 'posts', 'storageSummary'));
    }
/**
     * Store a new community post (text + optional image).
     */
    public function store(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();
        $user = $request->user();
        abort_unless($user !== null, 403);

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:5000'],
            'image' => ['nullable', 'file', 'image', 'mimes:jpeg,png,webp,gif', 'max:' . (self::MAX_IMAGE_BYTES / 1024)],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $trackingService = app(\App\Domain\Storage\StorageTrackingService::class);
            $owner = app(\App\Domain\Storage\OwnerStorageQuotaService::class)->ownerForBusiness($business) ?? $user;
            $trackingService->assertCanUpload($owner, (int) $file->getSize(), 'image');

            $extension = $file->getClientOriginalExtension() ?: 'jpg';
            $imagePath = $file->storeAs(
                "businesses/{$business->id}/community",
                Str::uuid() . '.' . $extension,
                'public'
            );

            $trackingService->recordUpload(
                file: $file,
                filePath: $imagePath,
                category: \App\Models\StorageFile::CATEGORY_COMMUNITY_IMAGE,
                module: 'community',
                owner: $owner,
                business: $business,
                uploader: $user
            );
        }

        CommunityPost::create([
            'owner_id' => $user->getAuthIdentifier(),
            'business_id' => $business->id,
            'content' => $validated['content'],
            'image_path' => $imagePath,
        ]);

        return back()->with('success', 'Postingan komunitas berhasil dibagikan.');
    }

    /**
     * Toggle like on a community post (JSON).
     */
    public function toggleLike(Request $request, CommunityPost $post): JsonResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        $like = CommunityLike::where('post_id', $post->id)
            ->where('user_id', $user->getAuthIdentifier())
            ->first();

        if ($like) {
            $like->delete();
            $liked = false;
        } else {
            CommunityLike::create([
                'post_id' => $post->id,
                'user_id' => $user->getAuthIdentifier(),
            ]);
            $liked = true;
        }

        return response()->json([
            'success' => true,
            'liked' => $liked,
            'likes_count' => $post->likes()->count(),
        ]);
    }
/**
     * Store a comment on a community post (JSON).
     */
    public function comment(Request $request, CommunityPost $post): JsonResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:1000'],
        ]);

        $comment = CommunityComment::create([
            'post_id' => $post->id,
            'user_id' => $user->getAuthIdentifier(),
            'content' => $validated['content'],
        ]);

        $comment->load(['user']);

        return response()->json([
            'success' => true,
            'comment' => [
                'id' => $comment->id,
                'content' => $comment->content,
                'created_at' => $comment->created_at->diffForHumans(),
                'user' => [
                    'name' => $comment->user->name ?? 'Owner',
                ],
            ],
            'comments_count' => $post->comments()->count(),
        ]);
    }

    /**
     * Delete a community post (only the author/owner).
     */
    public function destroy(Request $request, CommunityPost $post): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        if ((string) $post->owner_id !== (string) $user->getAuthIdentifier()) {
            abort(403, 'Anda hanya dapat menghapus postingan milik sendiri.');
        }

        if ($post->image_path) {
            app(\App\Domain\Storage\StorageTrackingService::class)->deleteFile($post->image_path, 'public');
        }

        $post->delete();

        return back()->with('success', 'Postingan berhasil dihapus.');
    }
}