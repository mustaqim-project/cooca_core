<?php

declare(strict_types=1);

namespace App\Domain\Commerce\GroupOrder;

use App\Domain\Commerce\Storefront\CommerceOrderService;
use App\Models\Business;
use App\Models\CommerceGroupOrder;
use App\Models\CommerceGroupOrderItem;
use App\Models\CommerceOrder;
use App\Models\GlobalCustomer;
use App\Models\Product;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class CommerceGroupOrderService
{
    public function __construct(
        private readonly ?CommerceOrderService $orderService = null
    ) {}

    private function getOrderService(): CommerceOrderService
    {
        return $this->orderService ?? app(CommerceOrderService::class);
    }

    /**
     * Create a new Group Order session initiated by a Host customer.
     */
    public function createGroup(Business $business, GlobalCustomer $host, array $data): CommerceGroupOrder
    {
        $title = trim((string) ($data['title'] ?? ''));
        if ($title === '') {
            $title = 'Makan Siang Bareng ' . ($host->name ?? 'Kantor');
        }

        $shareToken = 'grp-' . Str::lower(Str::random(10));
        while (CommerceGroupOrder::where('share_token', $shareToken)->exists()) {
            $shareToken = 'grp-' . Str::lower(Str::random(10));
        }

        $scheduledDate = ! empty($data['scheduled_date']) ? (string) $data['scheduled_date'] : null;
        $scheduledTimeSlot = ! empty($data['scheduled_time_slot']) ? (string) $data['scheduled_time_slot'] : null;

        $expiresAt = now()->addHours(24);
        if ($scheduledDate) {
            $dateObj = \Carbon\Carbon::parse($scheduledDate)->endOfDay();
            if ($dateObj->isFuture()) {
                $expiresAt = $dateObj;
            }
        }

        return CommerceGroupOrder::create([
            'business_id'         => $business->id,
            'host_customer_id'    => $host->id,
            'title'               => $title,
            'share_token'         => $shareToken,
            'scheduled_date'      => $scheduledDate,
            'scheduled_time_slot' => $scheduledTimeSlot,
            'status'              => CommerceGroupOrder::STATUS_OPEN,
            'delivery_address'    => $data['delivery_address'] ?? null,
            'delivery_notes'      => $data['delivery_notes'] ?? null,
            'expires_at'          => $expiresAt,
        ]);
    }

    /**
     * Add a product to the shared group order cart.
     */
    public function addItem(
        CommerceGroupOrder $group,
        GlobalCustomer $member,
        Product $product,
        float $quantity,
        ?string $notes = null,
        ?array $selectedModifiers = null
    ): CommerceGroupOrderItem {
        if (! $group->isOpen()) {
            throw new DomainException('Pesanan bersama ini telah dikunci atau diselesaikan oleh Host.');
        }

        if ($quantity <= 0) {
            throw new DomainException('Jumlah item yang dipesan harus minimal 1.');
        }

        $notes = $notes !== null ? trim($notes) : null;

        // Cek apakah member yang sama sudah memesan menu yang sama dengan catatan serupa
        $existing = CommerceGroupOrderItem::where('group_order_id', $group->id)
            ->where('global_customer_id', $member->id)
            ->where('product_id', $product->id)
            ->where('notes', $notes)
            ->first();

        if ($existing) {
            $existing->update([
                'quantity' => (float) $existing->quantity + $quantity,
            ]);

            return $existing->fresh();
        }

        return CommerceGroupOrderItem::create([
            'group_order_id'     => $group->id,
            'global_customer_id' => $member->id,
            'member_name'        => $member->name ?: 'Rekan Kantor',
            'product_id'         => $product->id,
            'quantity'           => $quantity,
            'unit_price'         => (float) $product->selling_price,
            'notes'              => $notes,
            'selected_modifiers' => $selectedModifiers,
        ]);
    }

    /**
     * Update quantity or notes of a group order item.
     */
    public function updateItem(
        CommerceGroupOrderItem $item,
        GlobalCustomer $user,
        float $quantity,
        ?string $notes = null
    ): ?CommerceGroupOrderItem {
        $group = $item->groupOrder;

        if (! $group->isOpen()) {
            throw new DomainException('Pesanan bersama ini telah dikunci atau diselesaikan oleh Host.');
        }

        // Hanya pemilik item atau Host grup yang berhak mengubah
        if (! $item->isOwnedBy($user) && ! $group->isHost($user)) {
            throw new DomainException('Anda hanya dapat mengubah pesanan Anda sendiri.');
        }

        if ($quantity <= 0) {
            $this->removeItem($item, $user);

            return null;
        }

        $item->update([
            'quantity' => $quantity,
            'notes'    => $notes !== null ? trim($notes) : $item->notes,
        ]);

        return $item->fresh();
    }

    /**
     * Remove an item from the shared cart.
     */
    public function removeItem(CommerceGroupOrderItem $item, GlobalCustomer $user): void
    {
        $group = $item->groupOrder;

        if (! $group->isOpen()) {
            throw new DomainException('Pesanan bersama ini telah dikunci atau diselesaikan oleh Host.');
        }

        // Hanya pemilik item atau Host grup yang berhak menghapus
        if (! $item->isOwnedBy($user) && ! $group->isHost($user)) {
            throw new DomainException('Anda hanya dapat menghapus pesanan Anda sendiri.');
        }

        $item->delete();
    }

    /**
     * Lock the group order (Host only) so members cannot add/edit items anymore.
     */
    public function lockGroup(CommerceGroupOrder $group, GlobalCustomer $host): CommerceGroupOrder
    {
        if (! $group->isHost($host)) {
            throw new DomainException('Hanya pembuat grup (Host) yang dapat mengunci pesanan bersama.');
        }

        if ($group->isCheckedOut()) {
            throw new DomainException('Pesanan bersama ini telah selesai diproses.');
        }

        $group->update(['status' => CommerceGroupOrder::STATUS_LOCKED]);

        return $group->fresh();
    }

    /**
     * Unlock the group order (Host only) allowing members to resume modifying items.
     */
    public function unlockGroup(CommerceGroupOrder $group, GlobalCustomer $host): CommerceGroupOrder
    {
        if (! $group->isHost($host)) {
            throw new DomainException('Hanya pembuat grup (Host) yang dapat membuka kunci pesanan bersama.');
        }

        if ($group->isCheckedOut()) {
            throw new DomainException('Pesanan bersama ini telah selesai diproses.');
        }

        $group->update(['status' => CommerceGroupOrder::STATUS_OPEN]);

        return $group->fresh();
    }

    /**
     * Host checks out the collective shared basket into a final CommerceOrder.
     */
    public function checkoutGroup(
        CommerceGroupOrder $group,
        GlobalCustomer $host,
        array $payload
    ): CommerceOrder {
        if (! $group->isHost($host)) {
            throw new DomainException('Hanya pembuat grup (Host) yang dapat menyelesaikan pembayaran pesanan bersama.');
        }

        if ($group->isCheckedOut()) {
            throw new DomainException('Pesanan bersama ini sudah pernah di-checkout sebelumnya.');
        }

        $items = $group->items()->with('product')->get();
        if ($items->isEmpty()) {
            throw new DomainException('Keranjang bersama masih kosong. Belum ada menu yang dipilih.');
        }

        return DB::transaction(function () use ($group, $host, $items, $payload): CommerceOrder {
            // Lock the group order record
            $lockedGroup = CommerceGroupOrder::where('id', $group->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedGroup->isCheckedOut()) {
                throw new DomainException('Pesanan bersama ini sudah di-checkout.');
            }

            // Formulate line items preserving recipient attribution
            $itemsData = [];
            foreach ($items as $item) {
                $notes = "[{$item->member_name}]";
                if ($item->notes) {
                    $notes .= ' ' . $item->notes;
                }

                $itemsData[] = [
                    'product_id' => $item->product_id,
                    'quantity'   => (float) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'notes'      => $notes,
                ];
            }

            $deliveryAddress = trim((string) ($payload['delivery_address'] ?? $lockedGroup->delivery_address ?? $host->shipping_address ?? ''));
            $deliveryNotes = trim((string) ($payload['delivery_notes'] ?? $lockedGroup->delivery_notes ?? ''));
            $fulfillmentType = $payload['fulfillment_type'] ?? CommerceOrder::FULFILLMENT_MERCHANT_DELIVERY;
            $paymentMethodId = $payload['payment_method_id'] ?? null;
            $shippingFee = (float) ($payload['shipping_fee'] ?? 0.0);

            $orderNotes = "🏢 [Pesanan Bersama: {$lockedGroup->title}]";
            if ($deliveryNotes !== '') {
                $orderNotes .= " Catatan: {$deliveryNotes}";
            }

            $customerData = [
                'name'    => $host->name ?: 'Host Pesan Bareng',
                'phone'   => $host->phone ?: '',
                'email'   => $host->email ?: '',
                'address' => $deliveryAddress,
            ];

            $orderService = $this->getOrderService();

            if ($lockedGroup->scheduled_date) {
                $order = $orderService->createScheduledOrder(
                    business: $lockedGroup->business,
                    customerData: $customerData,
                    itemsData: $itemsData,
                    fulfillmentType: $fulfillmentType,
                    scheduledDate: $lockedGroup->scheduled_date->toDateString(),
                    scheduledTimeSlot: $lockedGroup->scheduled_time_slot,
                    paymentMethodId: $paymentMethodId,
                    options: [
                        'notes'        => $orderNotes,
                        'shipping_fee' => $shippingFee,
                    ]
                );
            } else {
                $order = $orderService->createDirectOrder(
                    business: $lockedGroup->business,
                    customerData: $customerData,
                    itemsData: $itemsData,
                    fulfillmentType: $fulfillmentType,
                    paymentMethodId: $paymentMethodId,
                    options: [
                        'notes'        => $orderNotes,
                        'shipping_fee' => $shippingFee,
                    ]
                );
            }

            // Link group order to created commerce order and mark as checked out
            $lockedGroup->update([
                'status'            => CommerceGroupOrder::STATUS_CHECKED_OUT,
                'commerce_order_id' => $order->id,
                'delivery_address'  => $deliveryAddress,
                'delivery_notes'    => $deliveryNotes,
            ]);

            return $order;
        });
    }
}
