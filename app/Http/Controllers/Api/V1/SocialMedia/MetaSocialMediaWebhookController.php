<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\SocialMedia;

use App\Domain\SocialMedia\Clients\MetaSocialMediaClient;
use App\Http\Controllers\Controller;
use App\Models\SocialMediaAccount;
use App\Models\SocialMediaComment;
use App\Models\SocialMediaPost;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class MetaSocialMediaWebhookController extends Controller
{
    public function __construct(
        protected MetaSocialMediaClient $client
    ) {}

    /**
     * Handle Meta Webhook Verification (GET /api/v1/social-media/meta/webhook).
     */
    public function verify(Request $request): Response
    {
        $mode = (string) ($request->query('hub.mode') ?? $request->query('hub_mode') ?? '');
        $token = (string) ($request->query('hub.verify_token') ?? $request->query('hub_verify_token') ?? '');
        $challenge = (string) ($request->query('hub.challenge') ?? $request->query('hub_challenge') ?? '');

        $configuredToken = (string) (SystemSetting::get('social_media_webhook_verify_token') ?: config('services.meta_social.webhook_verify_token', 'cooca_meta_social_webhook_token'));

        if ($mode === 'subscribe' && hash_equals($configuredToken, $token)) {
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        return response('Forbidden', 403);
    }

    /**
     * Handle Incoming Meta Webhook Events (POST /api/v1/social-media/meta/webhook).
     */
    public function handle(Request $request): JsonResponse
    {
        $rawPayload = $request->getContent();
        $signature = $request->header('X-Hub-Signature-256');

        // 1. Verify HMAC-SHA256 signature
        if (! $this->client->verifyWebhookSignature($rawPayload, $signature)) {
            Log::warning('Meta Social Media Webhook signature mismatch', [
                'ip'        => $request->ip(),
                'signature' => $signature,
            ]);

            return response()->json(['error' => 'Invalid signature'], 403);
        }

        $payload = $request->all();
        $entries = $payload['entry'] ?? [];

        foreach ($entries as $entry) {
            $targetAssetId = (string) ($entry['id'] ?? '');
            if (empty($targetAssetId)) {
                continue;
            }

            // 2. Multi-Tenant Asset Matching: Match asset ID (Page ID / IG ID) to merchant account
            $account = SocialMediaAccount::where('account_id', $targetAssetId)
                ->where('status', 'active')
                ->first();

            if (! $account) {
                // Asset belongs to an unmanaged or disconnected merchant account
                continue;
            }

            $businessId = $account->business_id;

            // 3. Process changes (comments, feed, mentions)
            foreach ($entry['changes'] ?? [] as $change) {
                $field = $change['field'] ?? '';
                $value = $change['value'] ?? [];

                if (in_array($field, ['feed', 'comments'], true)) {
                    $item = $value['item'] ?? '';

                    if ($item === 'comment' || isset($value['comment_id'])) {
                        $commentId = (string) ($value['comment_id'] ?? '');
                        $postId = (string) ($value['post_id'] ?? '');
                        $message = (string) ($value['message'] ?? '');
                        $senderId = (string) (data_get($value, 'from.id') ?: ($value['sender_id'] ?? ''));
                        $senderName = (string) (data_get($value, 'from.name') ?: ($value['sender_name'] ?? 'User'));
                        $parentId = (string) ($value['parent_id'] ?? '');

                        if (! empty($commentId) && ! empty($message)) {
                            // Find local post if tracked
                            $localPost = SocialMediaPost::where('business_id', $businessId)
                                ->where('platform_post_id', $postId)
                                ->first();

                            SocialMediaComment::updateOrCreate(
                                [
                                    'business_id'         => $businessId,
                                    'platform'            => $account->platform,
                                    'platform_comment_id' => $commentId,
                                ],
                                [
                                    'social_media_account_id' => $account->id,
                                    'social_media_post_id'    => $localPost?->id,
                                    'platform_post_id'        => $postId,
                                    'parent_comment_id'       => ($parentId && $parentId !== $postId) ? $parentId : null,
                                    'from_id'                 => $senderId,
                                    'from_name'               => $senderName,
                                    'message'                 => $message,
                                    'is_from_page'            => ($senderId === $account->account_id),
                                    'status'                  => 'unread',
                                    'created_time'            => isset($value['created_time']) ? date('Y-m-d H:i:s', (int) $value['created_time']) : now(),
                                ]
                            );
                        }
                    }
                }
            }
        }

        return response()->json(['status' => 'EVENT_RECEIVED'], 200);
    }
}
