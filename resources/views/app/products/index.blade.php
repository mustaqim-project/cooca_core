@extends('layouts.app', [
    'title' => 'Katalog Produk & Model HPP',
    'headerTitle' => 'Katalog Produk & Model HPP',
    'headerSubtitle' => 'Kelola produk jadi, kalkulasi biaya (BOM, ABC, Job Order), harga jual, dan saluran etalase',
])

@section('content')
    <div class="max-w-[1440px] mx-auto w-full px-4 sm:px-6 lg:px-8 space-y-6 pb-28 sm:pb-32 lg:pb-10" x-data="{
        showAddModal: false,
        showEditModal: {{ $editProduct ? 'true' : 'false' }},
        showAddCategoryModal: false,
        showAddUnitModal: false,
        quickAddTarget: 'add',
        quickCategoryName: '',
        quickCategoryDesc: '',
        quickCategoryLoading: false,
        quickUnitCode: '',
        quickUnitName: '',
        quickUnitCategory: 'quantity',
        quickUnitLoading: false,
        categoryList: {{ Js::from($categories->map(fn($c) => ['id' => (string) $c->id, 'name' => $c->name, 'marketplace_category_id' => $c->marketplace_category_id, 'marketplace_category_name' => $c->marketplace_category_name])) }},
        unitList: {{ Js::from($units->map(fn($u) => ['id' => (string) $u->id, 'name' => $u->name, 'code' => $u->code])) }},
        allProductsList: {{ Js::from($allProducts->map(fn($p) => ['id' => (string) $p->id, 'name' => $p->name, 'code' => $p->code, 'price' => (float)$p->selling_price, 'cost' => (float)$p->base_cost])) }},
        marketplaceCategories: {{ Js::from($marketplaceCategories ?? []) }},
        isPharmacy: {{ $isPharmacy ? 'true' : 'false' }},
        isServiceSector: {{ $isServiceSector ? 'true' : 'false' }},
        newIsMarketplaceEnabled: false,
        newSelectedCategoryId: '',
        getInheritedMarketplaceCategory(catId) {
            if (!catId) return null;
            const cat = this.categoryList.find(c => String(c.id) === String(catId));
            if (cat && cat.marketplace_category_id) {
                return {
                    id: cat.marketplace_category_id,
                    name: cat.marketplace_category_name || cat.marketplace_category_id
                };
            }
            return null;
        },
        suggestMarketplaceCategory(prodName, catId) {
            if (!this.marketplaceCategories || !this.marketplaceCategories.length) return null;
            const cat = this.categoryList.find(c => String(c.id) === String(catId));
            const searchStr = ((prodName || '') + ' ' + (cat ? cat.name : '')).toLowerCase();
            let best = null;
            let maxScore = 0;
            for (const mc of this.marketplaceCategories) {
                let score = 0;
                if (searchStr.includes(mc.name.toLowerCase())) score += 10;
                if (mc.keywords && Array.isArray(mc.keywords)) {
                    for (const kw of mc.keywords) {
                        if (searchStr.includes(kw.toLowerCase())) score += 3;
                    }
                }
                if (score > maxScore) {
                    maxScore = score;
                    best = mc;
                }
            }
            return (best && maxScore > 0) ? best : (this.marketplaceCategories[0] || null);
        },
        newIsBundle: false,
        newBundleItems: [],
        newChannelPrices: { dine_in: '', takeaway: '', gofood: '', grabfood: '', shopeefood: '' },
    
        showProductScannerPermission: false,
        showProductScanner: false,
        productScannerStarting: false,
        productScannerError: '',
        productScannerStream: null,
        productScannerDetector: null,
        productScannerFrameId: null,
        productScannerBusy: false,
        productScannerTarget: 'add',
    
        init() {
            window.addEventListener('pageshow', () => {
                this.showAddModal = false;
                this.showAddCategoryModal = false;
                this.showAddUnitModal = false;
                this.showProductScannerPermission = false;
                this.closeProductScanner();
                this.deleteModalOpen = false;
            });
        },
    
        posShowImages: {{ $business->pos_show_product_images ? 'true' : 'false' }},
        posImageToggling: false,
        newProductPreview: '',
        editProductPreview: '',
        newIsPreorder: false,
        newPreorderMode: 'customer_schedule',
        newPreorderLeadDays: 1,
        addBundleItem(target) {
            if (target === 'add') {
                this.newBundleItems.push({ child_product_id: '', quantity: 1 });
            } else {
                if (!this.editProduct.bundle_items) this.editProduct.bundle_items = [];
                this.editProduct.bundle_items.push({ child_product_id: '', quantity: 1 });
            }
        },
        removeBundleItem(target, index) {
            if (target === 'add') {
                this.newBundleItems.splice(index, 1);
            } else {
                this.editProduct.bundle_items.splice(index, 1);
            }
        },
        getEstimatedBundleCost(items) {
            if (!items || !items.length) return 0;
            let total = 0;
            for (const item of items) {
                const prod = this.allProductsList.find(p => p.id === String(item.child_product_id));
                if (prod) {
                    total += (prod.cost || 0) * (Number(item.quantity) || 1);
                }
            }
            return total;
        },
        getEstimatedBundleValue(items) {
            if (!items || !items.length) return 0;
            let total = 0;
            for (const item of items) {
                const prod = this.allProductsList.find(p => p.id === String(item.child_product_id));
                if (prod) {
                    total += (prod.price || 0) * (Number(item.quantity) || 1);
                }
            }
            return total;
        },
        showBranchPricesModal: false,
        branchPricesLoading: false,
        branchPricesSaving: false,
        branchProduct: { id: '', name: '', code: '', base_cost: 0, selling_price: 0 },
        branchList: [],
        async openBranchPricesModal(productId) {
            this.showBranchPricesModal = true;
            this.branchPricesLoading = true;
            this.branchList = [];
            try {
                const res = await fetch('/products/' + productId + '/branch-prices', {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Gagal memuat harga cabang');
                this.branchProduct = data.product;
                this.branchList = (data.branches || []).map(b => ({
                    ...b,
                    custom_price: b.has_price_override ? b.price : '',
                    custom_cost: (b.has_override && b.cost_price !== null) ? b.cost_price : '',
                    is_available: b.is_available ?? true,
                    use_custom: b.has_price_override ?? false
                }));
            } catch (e) {
                this.showBranchPricesModal = false;
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Akses Dibatasi',
                        text: e.message || 'Fitur Multi-Harga per Cabang memerlukan Paket Premium.',
                        confirmButtonColor: '#007AFF'
                    });
                }
            } finally {
                this.branchPricesLoading = false;
            }
        },
        toggleAllBranchesAvailability(status) {
            this.branchList.forEach(b => {
                b.is_available = status;
            });
        },
        resetAllBranchPrices() {
            this.branchList.forEach(b => {
                b.use_custom = false;
                b.custom_price = '';
                b.custom_cost = '';
                b.is_available = true;
            });
        },
        getBranchMargin(branch) {
            const price = branch.use_custom && branch.custom_price ? Number(branch.custom_price) : Number(this.branchProduct.selling_price || 0);
            const cost = branch.use_custom && branch.custom_cost !== '' ? Number(branch.custom_cost) : Number(this.branchProduct.base_cost || 0);
            if (!price || price <= 0) return 0;
            const profit = price - cost;
            return ((profit / price) * 100).toFixed(1);
        },
        async saveBranchPrices() {
            if (this.branchPricesSaving) return;
            this.branchPricesSaving = true;
            try {
                const payload = {
                    prices: this.branchList.map(b => ({
                        location_id: b.location_id,
                        price: b.is_available && b.use_custom && b.custom_price !== '' ? Number(b.custom_price) : null,
                        cost_price: b.is_available && b.use_custom && b.custom_cost !== '' ? Number(b.custom_cost) : null,
                        is_available: b.is_available,
                        use_custom: b.use_custom,
                        reset: b.is_available && !b.use_custom
                    }))
                };
                const res = await fetch('/products/' + this.branchProduct.id + '/branch-prices', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Gagal menyimpan harga cabang.');
                this.showBranchPricesModal = false;
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: data.message,
                        showConfirmButton: false,
                        timer: 2500
                    });
                }
            } catch (e) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Menyimpan',
                        text: e.message,
                        confirmButtonColor: '#FF3B30'
                    });
                }
            } finally {
                this.branchPricesSaving = false;
            }
        },
    
        deleteModalOpen: false,
        deleteTarget: { id: '', name: '' },
        openDelete(id, name) {
            this.deleteTarget = { id, name };
            this.deleteModalOpen = true;
        },
        closeDelete() {
            this.deleteModalOpen = false;
            this.deleteTarget = { id: '', name: '' };
        },
        submitDelete() {
            if (this.deleteTarget.id) {
                const form = document.getElementById('form-delete-' + this.deleteTarget.id);
                if (form) form.submit();
            }
        },
    
        async togglePosShowImages() {
            if (this.posImageToggling) return;
            this.posImageToggling = true;
            const nextState = !this.posShowImages;
            try {
                const res = await fetch('{{ route('products.toggle-pos-images') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ show_images: nextState })
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Gagal mengubah pengaturan POS.');
                this.posShowImages = Boolean(data.pos_show_product_images);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: data.message,
                        showConfirmButton: false,
                        timer: 2500
                    });
                }
            } catch (e) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: e.message,
                        confirmButtonColor: '#FF3B30'
                    });
                }
            } finally {
                this.posImageToggling = false;
            }
        },
    
        newGalleryPreviews: [],
        editGalleryPreviews: [],
        removeGalleryIds: [],

        handleNewProductImage(e) {
            const file = e.target.files[0];
            if (!file) return;
            if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Format Tidak Didukung', text: 'Gunakan format JPG, PNG, atau WebP.', confirmButtonColor: '#FF3B30' });
                }
                e.target.value = '';
                this.newProductPreview = '';
                return;
            }
            if (file.size > 4 * 1024 * 1024) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Ukuran Terlalu Besar', text: 'Ukuran file maksimal 4 MB.', confirmButtonColor: '#FF3B30' });
                }
                e.target.value = '';
                this.newProductPreview = '';
                return;
            }
            this.newProductPreview = URL.createObjectURL(file);
        },

        handleNewGalleryImages(e) {
            const files = Array.from(e.target.files || []);
            if (!files.length) return;
            if (files.length > 10) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Maksimal 10 Foto', text: 'Anda dapat mengunggah maksimal 10 foto galeri sekaligus.', confirmButtonColor: '#FF3B30' });
                }
                e.target.value = '';
                this.newGalleryPreviews = [];
                return;
            }
            const validPreviews = [];
            for (const file of files) {
                if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Format Tidak Didukung', text: `File ${file.name} bukan format JPG, PNG, atau WebP.`, confirmButtonColor: '#FF3B30' });
                    }
                    e.target.value = '';
                    this.newGalleryPreviews = [];
                    return;
                }
                if (file.size > 4 * 1024 * 1024) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Ukuran Terlalu Besar', text: `File ${file.name} melebihi batas 4 MB.`, confirmButtonColor: '#FF3B30' });
                    }
                    e.target.value = '';
                    this.newGalleryPreviews = [];
                    return;
                }
                validPreviews.push(URL.createObjectURL(file));
            }
            this.newGalleryPreviews = validPreviews;
        },

        handleEditProductImage(e) {
            const file = e.target.files[0];
            if (!file) return;
            if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Format Tidak Didukung', text: 'Gunakan format JPG, PNG, atau WebP.', confirmButtonColor: '#FF3B30' });
                }
                e.target.value = '';
                this.editProductPreview = '';
                return;
            }
            if (file.size > 4 * 1024 * 1024) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Ukuran Terlalu Besar', text: 'Ukuran file maksimal 4 MB.', confirmButtonColor: '#FF3B30' });
                }
                e.target.value = '';
                this.editProductPreview = '';
                return;
            }
            this.editProductPreview = URL.createObjectURL(file);
        },

        handleEditGalleryImages(e) {
            const files = Array.from(e.target.files || []);
            if (!files.length) return;
            if (files.length > 10) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Maksimal 10 Foto', text: 'Anda dapat mengunggah maksimal 10 foto galeri sekaligus.', confirmButtonColor: '#FF3B30' });
                }
                e.target.value = '';
                this.editGalleryPreviews = [];
                return;
            }
            const validPreviews = [];
            for (const file of files) {
                if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Format Tidak Didukung', text: `File ${file.name} bukan format JPG, PNG, atau WebP.`, confirmButtonColor: '#FF3B30' });
                    }
                    e.target.value = '';
                    this.editGalleryPreviews = [];
                    return;
                }
                if (file.size > 4 * 1024 * 1024) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Ukuran Terlalu Besar', text: `File ${file.name} melebihi batas 4 MB.`, confirmButtonColor: '#FF3B30' });
                    }
                    e.target.value = '';
                    this.editGalleryPreviews = [];
                    return;
                }
                validPreviews.push(URL.createObjectURL(file));
            }
            this.editGalleryPreviews = validPreviews;
        },

        toggleRemoveExistingGallery(id) {
            const idx = this.removeGalleryIds.indexOf(id);
            if (idx > -1) {
                this.removeGalleryIds.splice(idx, 1);
            } else {
                this.removeGalleryIds.push(id);
            }
        },

        editProduct: {!! $editProduct
            ? json_encode(
                [
                    'id' => $editProduct->id,
                    'slug' => $editProduct->slug,
                    'name' => $editProduct->name,
                    'sku' => $editProduct->code ?? '',
                    'category_id' => (string) ($editProduct->category_id ?? ''),
                    'output_unit_id' => (string) $editProduct->output_unit_id,
                    'base_cost' => (float) $editProduct->base_cost,
                    'selling_price' => (float) $editProduct->selling_price,
                    'min_stock' => (float) $editProduct->min_stock,
                    'weight' => (float) ($editProduct->weight ?? 200),
                    'length' => $editProduct->length,
                    'width' => $editProduct->width,
                    'height' => $editProduct->height,
                    'is_active' => (bool) $editProduct->is_active,
                    'show_in_website' => (bool) ($editProduct->show_in_website ?? true),
                    'show_in_pos' => (bool) ($editProduct->show_in_pos ?? true),
                    'show_in_sales_order' => (bool) ($editProduct->show_in_sales_order ?? true),
                    'show_price_on_web' => (bool) ($editProduct->show_price_on_web ?? true),
                    'is_preorder' => (bool) ($editProduct->is_preorder ?? false),
                    'preorder_mode' => (string) ($editProduct->preorder_mode ?? 'customer_schedule'),
                    'preorder_lead_days' => (int) ($editProduct->preorder_lead_days ?? 1),
                    'is_bundle' => (bool) ($editProduct->is_bundle ?? false),
                    'bundle_items' => $editProduct->bundleItems->map(fn($bi) => ['child_product_id' => (string)$bi->child_product_id, 'quantity' => (float)$bi->quantity])->values()->all(),
                    'channel_prices' => $editProduct->channelPrices->pluck('price', 'channel')->all(),
                    'description' => $editProduct->description ?? '',
                    'image_url' => $editProduct->image_url,
                    'images' => $editProduct->images->map(fn($img) => ['id' => $img->id, 'image_url' => $img->image_url, 'caption' => $img->caption])->values()->all(),
                    'is_marketplace_enabled' => (bool) ($editProduct->marketplaceMappings->where('is_active', true)->isNotEmpty()),
                    'marketplace_mappings_count' => (int) $editProduct->marketplaceMappings->count(),
                ],
                JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE,
            )
            : "{ id: '', slug: '', name: '', sku: '', category_id: '', output_unit_id: '', base_cost: 0, selling_price: 0, min_stock: 0, weight: 200, length: '', width: '', height: '', is_active: true, show_in_website: true, show_in_pos: true, show_in_sales_order: true, show_price_on_web: true, is_preorder: false, preorder_mode: 'customer_schedule', preorder_lead_days: 1, is_bundle: false, bundle_items: [], channel_prices: {}, description: '', image_url: '', images: [], is_marketplace_enabled: false, marketplace_mappings_count: 0 }" !!},
    
        productToggles: {
            @foreach($products as $p)
            '{{ $p->id }}': {
                show_in_website: {{ $p->show_in_website ?? true ? 'true' : 'false' }},
                show_in_pos: {{ $p->show_in_pos ?? true ? 'true' : 'false' }},
                show_in_sales_order: {{ $p->show_in_sales_order ?? true ? 'true' : 'false' }},
                show_price_on_web: {{ $p->show_price_on_web ?? true ? 'true' : 'false' }},
                is_preorder: {{ $p->is_preorder ?? false ? 'true' : 'false' }},
                is_active: {{ $p->is_active ? 'true' : 'false' }},
                loading: null
            },
            @endforeach
        },
    
        async toggleProductField(productId, field) {
            if (!this.productToggles[productId]) {
                this.productToggles[productId] = {
                    show_in_website: true,
                    show_in_pos: true,
                    show_in_sales_order: true,
                    show_price_on_web: true,
                    is_preorder: false,
                    is_active: true,
                    loading: null
                };
            }
            const item = this.productToggles[productId];
            if (item.loading) return;
    
            const prevVal = Boolean(item[field]);
            const nextVal = !prevVal;
    
            // Optimistic UI update
            item[field] = nextVal;
            item.loading = field;
    
            try {
                const res = await fetch('/products/' + productId + '/toggle-setting', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ field: field, value: nextVal })
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Gagal mengubah pengaturan.');
                item[field] = Boolean(data.value);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: data.message,
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            } catch (err) {
                item[field] = prevVal;
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: err.message || 'Gagal memperbarui status.',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
            } finally {
                item.loading = null;
            }
        },
    
        openEditModal(p) {
            const currentToggle = this.productToggles[p.id];
            this.editProduct = {
                ...p,
                category_id: String(p.category_id || ''),
                output_unit_id: String(p.output_unit_id || ''),
                weight: Number(p.weight || 200),
                length: p.length ? Number(p.length) : '',
                width: p.width ? Number(p.width) : '',
                height: p.height ? Number(p.height) : '',
                show_in_website: currentToggle ? currentToggle.show_in_website : p.show_in_website,
                show_in_pos: currentToggle ? currentToggle.show_in_pos : p.show_in_pos,
                show_in_sales_order: currentToggle ? currentToggle.show_in_sales_order : p.show_in_sales_order,
                show_price_on_web: currentToggle ? currentToggle.show_price_on_web : p.show_price_on_web,
                is_preorder: currentToggle ? currentToggle.is_preorder : p.is_preorder,
                is_active: currentToggle ? currentToggle.is_active : p.is_active,
                is_bundle: Boolean(p.is_bundle),
                bundle_items: (p.bundle_items || []).map(b => ({ child_product_id: String(b.child_product_id), quantity: Number(b.quantity) })),
                channel_prices: p.channel_prices || {},
                images: p.images || [],
            };
            this.editProductPreview = '';
            this.editGalleryPreviews = [];
            this.removeGalleryIds = [];
            this.showEditModal = true;
        },
    
        // Quick-Add Category AJAX
        async submitQuickCategory() {
            if (!this.quickCategoryName.trim() || this.quickCategoryLoading) return;
            this.quickCategoryLoading = true;
            try {
                const res = await fetch('{{ route('product-categories.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        name: this.quickCategoryName.trim(),
                        description: this.quickCategoryDesc.trim() || null
                    })
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Gagal menambahkan kategori.');
                const newCat = { id: String(data.category.id), name: data.category.name };
                this.categoryList.push(newCat);
                if (this.quickAddTarget === 'edit') {
                    this.editProduct.category_id = newCat.id;
                } else if (this.$refs.newProductCategory) {
                    this.$refs.newProductCategory.value = newCat.id;
                }
                this.quickCategoryName = '';
                this.quickCategoryDesc = '';
                this.showAddCategoryModal = false;
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Kategori baru berhasil ditambahkan.',
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            } catch (err) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Gagal!', text: err.message, confirmButtonColor: '#FF3B30' });
                }
            } finally {
                this.quickCategoryLoading = false;
            }
        },
    
        // Quick-Add Unit AJAX
        async submitQuickUnit() {
            if (!this.quickUnitCode.trim() || !this.quickUnitName.trim() || this.quickUnitLoading) return;
            this.quickUnitLoading = true;
            try {
                const res = await fetch('{{ route('units.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        code: this.quickUnitCode.trim(),
                        name: this.quickUnitName.trim(),
                        category: this.quickUnitCategory,
                        default_precision: 0
                    })
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Gagal menambahkan satuan.');
                const newUnit = { id: String(data.unit.id), name: data.unit.name, code: data.unit.code };
                this.unitList.push(newUnit);
                if (this.quickAddTarget === 'edit') {
                    this.editProduct.output_unit_id = newUnit.id;
                } else if (this.$refs.newProductUnit) {
                    this.$refs.newProductUnit.value = newUnit.id;
                }
                this.quickUnitCode = '';
                this.quickUnitName = '';
                this.quickUnitCategory = 'quantity';
                this.showAddUnitModal = false;
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Satuan baru berhasil ditambahkan.',
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            } catch (err) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Gagal!', text: err.message, confirmButtonColor: '#FF3B30' });
                }
            } finally {
                this.quickUnitLoading = false;
            }
        },
    
        requestProductScanner(target) {
            this.productScannerTarget = target;
            if (localStorage.getItem('cooca-product-camera-permission-intro-seen') === '1') {
                this.openProductScanner();
                return;
            }
            this.showProductScannerPermission = true;
        },
        async confirmProductScannerAccess() {
            localStorage.setItem('cooca-product-camera-permission-intro-seen', '1');
            this.showProductScannerPermission = false;
            await this.openProductScanner();
        },
        async openProductScanner() {
            this.showProductScanner = true;
            this.productScannerError = '';
            await this.$nextTick();
            if (!('BarcodeDetector' in window)) {
                this.productScannerError = 'Browser ini belum mendukung scan barcode kamera. Gunakan Chrome/Android terbaru atau input barcode manual.';
                return;
            }
            try {
                const supported = await BarcodeDetector.getSupportedFormats();
                const formats = ['ean_13', 'ean_8', 'upc_a', 'upc_e', 'code_128', 'code_39', 'codabar', 'itf'].filter(format => supported.includes(format));
                this.productScannerDetector = new BarcodeDetector({ formats });
                this.productScannerStarting = true;
                this.productScannerStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: { ideal: 'environment' }, width: { ideal: 1280 }, height: { ideal: 720 } }, audio: false });
                this.$refs.productBarcodeVideo.srcObject = this.productScannerStream;
                await this.$refs.productBarcodeVideo.play();
                this.productScannerStarting = false;
                this.scanProductBarcodeFrame();
            } catch (error) {
                this.productScannerStarting = false;
                this.productScannerError = this.productScannerMessage(error);
            }
        },
        async scanProductBarcodeFrame() {
            if (!this.showProductScanner || !this.productScannerDetector || !this.$refs.productBarcodeVideo) return;
            if (!this.productScannerBusy && this.$refs.productBarcodeVideo.readyState >= 2) {
                this.productScannerBusy = true;
                try {
                    const results = await this.productScannerDetector.detect(this.$refs.productBarcodeVideo);
                    const value = results.find(result => result.rawValue)?.rawValue;
                    if (value) {
                        this.setProductBarcode(value);
                        return;
                    }
                } catch (error) {
                    this.productScannerError = 'Barcode belum terbaca. Posisikan barcode di dalam kotak.';
                } finally {
                    this.productScannerBusy = false;
                }
            }
            this.productScannerFrameId = requestAnimationFrame(() => this.scanProductBarcodeFrame());
        },
        setProductBarcode(value) {
            if (this.productScannerTarget === 'edit') {
                this.editProduct.sku = value;
            } else if (this.$refs.newProductBarcode) {
                this.$refs.newProductBarcode.value = value;
            }
            this.closeProductScanner();
        },
        closeProductScanner() {
            this.showProductScanner = false;
            if (this.productScannerFrameId) cancelAnimationFrame(this.productScannerFrameId);
            this.productScannerFrameId = null;
            this.productScannerBusy = false;
            if (this.productScannerStream) this.productScannerStream.getTracks().forEach(track => track.stop());
            this.productScannerStream = null;
            if (this.$refs.productBarcodeVideo) this.$refs.productBarcodeVideo.srcObject = null;
        },
        productScannerMessage(error) {
            if (error?.name === 'NotAllowedError' || error?.name === 'PermissionDeniedError') return 'Akses kamera ditolak. Izinkan kamera di browser lalu coba lagi.';
            if (error?.name === 'NotFoundError') return 'Kamera tidak ditemukan pada perangkat ini.';
            if (error?.name === 'NotReadableError') return 'Kamera sedang digunakan aplikasi lain.';
            if (window.isSecureContext === false) return 'Scanner kamera memerlukan HTTPS atau localhost.';
            return 'Kamera tidak dapat dibuka. Periksa izin kamera lalu coba lagi.';
        }
    }">

        <!-- ===================================================== -->
        <!-- 1. TOOLBAR / PAGE HEADER                                -->
        <!-- ===================================================== -->
        <x-module-header
            :title="__('products.page_title')"
            :subtitle="__('products.page_subtitle')"
            :breadcrumbs="[
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => __('products.page_title'), 'url' => route('products.index')],
                ['label' => __('products.tab_catalog'), 'url' => null],
            ]">
            @if (\App\Support\Context::hasPermission('products.manage') || \App\Support\Context::hasPermission('products.edit'))
                <!-- Toggle Gambar POS -->
                <button type="button" @click="togglePosShowImages()" :disabled="posImageToggling"
                    class="h-10 px-3.5 rounded-[12px] text-[13px] font-medium transition-all border flex items-center justify-center gap-2 active:scale-[0.98] disabled:opacity-50 min-w-[44px]"
                    :class="posShowImages ? 'bg-[#34C759]/10 border-[#34C759]/30 text-[#248A3D] dark:text-[#30D158]' :
                        'bg-black/[0.04] dark:bg-white/[0.06] border-black/[0.06] dark:border-white/[0.08] text-black/70 dark:text-white/70 hover:bg-black/[0.07] dark:hover:bg-white/[0.1]'"
                    :title="__('products.toggle_pos_images_title')">
                    <span class="w-2 h-2 rounded-full transition-colors shrink-0"
                        :class="posShowImages ? 'bg-[#34C759]' : 'bg-black/30 dark:bg-white/30'"></span>
                    <span>{{ __('products.btn_pos_photo') }}:</span>
                    <span class="font-bold tabular-nums" x-text="posShowImages ? 'ON' : 'OFF'"></span>
                </button>
            @endif

            @if (\App\Support\Context::hasPermission('products.create') || \App\Support\Context::hasPermission('products.manage'))
                <!-- Import Excel -->
                <a href="{{ route('import.index', ['tab' => 'products']) }}"
                    class="h-10 px-3.5 rounded-[12px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] border border-black/[0.06] dark:border-white/[0.08] active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 min-w-[44px]"
                    title="Import data produk massal dari file Excel / CSV">
                    <i data-lucide="upload" class="w-4 h-4 text-black/60 dark:text-white/60 shrink-0"></i>
                    <span>{{ __('products.btn_import') }}</span>
                </a>

                <!-- Tambah Produk Baru -->
                <button @click="showAddModal = true"
                    class="h-10 px-4 rounded-[12px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 shadow-sm shadow-[#007AFF]/25 min-w-[44px]">
                    <i data-lucide="plus" class="w-4 h-4 shrink-0"></i>
                    <span>{{ __('products.btn_create_product') }}</span>
                </button>
            @endif
        </x-module-header>

        <!-- ===================================================== -->
        <!-- 2. PERSISTENT MODULE TABS                             -->
        <!-- ===================================================== -->
        <x-module-tabs module="products" />

        <!-- ===================================================== -->
        <!-- 2. KPI SUMMARY (Apple Bento Metric Cards)              -->
        <!-- ===================================================== -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-5">
            <!-- Tile 1: Total Produk -->
            <div
                class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 flex flex-col justify-between shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-[13px] font-medium text-black/50 dark:text-white/50">{{ __('products.kpi_total_products') }}</span>
                    <div
                        class="w-8 h-8 rounded-full bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                        <i data-lucide="boxes" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline justify-between">
                    <span
                        class="text-2xl sm:text-3xl font-bold tabular-nums text-black dark:text-white">{{ $products->total() }}</span>
                    <span class="text-[12px] text-black/40 dark:text-white/40">{{ __('products.kpi_catalog_label') }}</span>
                </div>
            </div>

            <!-- Tile 2: Kategori Terdaftar -->
            <div
                class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 flex flex-col justify-between shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-[13px] font-medium text-black/50 dark:text-white/50">{{ __('products.kpi_categories_count') }}</span>
                    <div
                        class="w-8 h-8 rounded-full bg-[#5856D6]/10 text-[#5856D6] dark:text-[#5E5CE6] flex items-center justify-center shrink-0">
                        <i data-lucide="folder" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline justify-between">
                    <span
                        class="text-2xl sm:text-3xl font-bold tabular-nums text-[#007AFF]">{{ $categories->count() }}</span>
                    <span class="text-[12px] text-black/40 dark:text-white/40">{{ __('products.kpi_groups_label') }}</span>
                </div>
            </div>

            <!-- Tile 3: Produk Aktif -->
            <div
                class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 flex flex-col justify-between shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-[13px] font-medium text-black/50 dark:text-white/50">{{ __('products.kpi_active_products') }}</span>
                    <div
                        class="w-8 h-8 rounded-full bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] flex items-center justify-center shrink-0">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline justify-between">
                    <span
                        class="text-2xl sm:text-3xl font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">{{ $products->where('is_active', true)->count() }}</span>
                    <span class="text-[12px] font-medium text-[#34C759] dark:text-[#30D158]">{{ __('products.kpi_ready_to_sell') }}</span>
                </div>
            </div>

            <!-- Tile 4: Paket Kombo & Pre-Order -->
            <div
                class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 flex flex-col justify-between shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-[13px] font-medium text-black/50 dark:text-white/50">{{ __('products.section_bundle_title') }}</span>
                    <div
                        class="w-8 h-8 rounded-full bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center shrink-0">
                        <i data-lucide="layers" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline justify-between">
                    <span class="text-2xl sm:text-3xl font-bold tabular-nums text-[#FF9500] dark:text-[#FF9F0A]">{{ $products->where('is_bundle', true)->count() }}</span>
                    <span class="text-[12px] text-black/40 dark:text-white/40">{{ __('products.kpi_bundles_label') }}</span>
                </div>
            </div>
        </div>

        <!-- ===================================================== -->
        <!-- 3. SEARCH & CATEGORY CONTROLS                          -->
        <!-- ===================================================== -->
        <div
            class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4 shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
            <form method="GET" action="{{ route('products.index') }}"
                class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                <!-- Search Field -->
                <div class="relative flex-1 max-w-md">
                    <i data-lucide="search" class="w-4 h-4 text-black/40 dark:text-white/40 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="{{ __('products.search_placeholder') }}"
                        class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[12px] pl-10 pr-3.5 text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/40 dark:placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <!-- Category Filter -->
                <div class="flex items-center gap-2">
                    <select name="category_id" onchange="this.form.submit()"
                        class="h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        <option value="">{{ __('products.all_categories') }}</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}"
                                {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>

                    @if (request('search') || request('category_id'))
                        <a href="{{ route('products.index') }}"
                            class="h-10 px-3.5 rounded-[12px] text-[13px] font-medium text-black/60 dark:text-white/60 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.09] flex items-center transition min-w-[44px] justify-center">
                            {{ __('products.btn_reset') }}
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- ===================================================== -->
        <!-- 4. DENSE DATA TABLE (DESKTOP & TABLET)                 -->
        <!-- ===================================================== -->
        <div
            class="hidden sm:block rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
            <div class="overflow-x-auto scrollbar-thin">
                <table class="w-full text-left text-[13px]">
                    <thead>
                        <tr
                            class="border-b border-black/[0.06] dark:border-white/[0.08] bg-black/[0.02] dark:bg-white/[0.02]">
                            <th
                                class="py-3 px-5 text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40 whitespace-nowrap">
                                {{ __('products.th_product_name_barcode') }}</th>
                            <th
                                class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40 whitespace-nowrap">
                                {{ __('products.th_category') }}</th>
                            <th
                                class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40 whitespace-nowrap">
                                {{ __('products.th_output_unit') }}</th>
                            <th
                                class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40 text-right whitespace-nowrap">
                                {{ __('products.th_effective_cost') }}</th>
                            <th
                                class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40 text-right whitespace-nowrap">
                                {{ __('products.th_selling_price') }}</th>
                            <th
                                class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40 text-center whitespace-nowrap">
                                {{ __('products.th_channels_status') }}</th>
                            <th
                                class="py-3 px-5 text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40 text-right whitespace-nowrap">
                                {{ __('products.th_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        @forelse($products as $prod)
                            @php
                                $activeModel = $prod->costModels->first();
                                $latestVer = $activeModel?->latestVersion;
                                $effectiveHpp = $latestVer
                                    ? (float) $latestVer->hpp_per_unit
                                    : (float) $prod->base_cost;
                            @endphp
                            <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.025] transition-colors">
                                <td class="py-3.5 px-5 font-medium text-black dark:text-white">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/5 overflow-hidden flex items-center justify-center shrink-0">
                                            @if ($prod->image_url)
                                                <img src="{{ $prod->image_url }}" alt="{{ $prod->name }}"
                                                    loading="lazy" class="w-full h-full object-cover"
                                                    onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');">
                                                <i data-lucide="package" class="hidden w-4 h-4 text-black/40 dark:text-white/40"></i>
                                            @else
                                                <i data-lucide="package" class="w-4 h-4 text-black/40 dark:text-white/40"></i>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="font-semibold text-black dark:text-white text-[13.5px] truncate">{{ $prod->name }}</span>
                                                @if ($prod->isBundle())
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-500/30">
                                                        {{ __('products.badge_combo', ['count' => $prod->bundleItems->count()]) }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-[11px] text-black/45 dark:text-white/45 tabular-nums">
                                                {{ $prod->code ?? ($prod->sku ?? __('products.no_barcode')) }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-black/60 dark:text-white/60">
                                    {{ $prod->category?->name ?? __('products.category_general') }}
                                </td>
                                <td class="py-3.5 px-4 text-black/70 dark:text-white/70">
                                    {{ $prod->outputUnit?->name ?? 'pcs' }} <span
                                        class="text-[11px] text-black/40 dark:text-white/40">({{ $prod->outputUnit?->code }})</span>
                                </td>
                                <td class="py-3.5 px-4 text-right tabular-nums">
                                    @if ($effectiveHpp > 0)
                                        <div class="font-semibold text-black dark:text-white">
                                            {{ $business->currency_symbol }}
                                            {{ number_format($effectiveHpp, 0, ',', '.') }}</div>
                                        @if ($latestVer)
                                            <div class="text-[10.5px] text-black/45 dark:text-white/45">
                                                {{ $latestVer->version_label }}</div>
                                        @endif
                                    @else
                                        <span class="text-black/40 dark:text-white/40 italic">{{ __('products.cost_not_calculated') }}</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right tabular-nums">
                                    @if ($prod->selling_price > 0)
                                        <div class="font-bold text-[#34C759] dark:text-[#30D158]">
                                            {{ $business->currency_symbol }}
                                            {{ number_format((float) $prod->selling_price, 0, ',', '.') }}</div>
                                        @if ($effectiveHpp > 0)
                                            <div class="text-[10.5px] text-black/50 dark:text-white/50 font-medium">
                                                {{ __('products.margin_label', ['percent' => round((($prod->selling_price - $effectiveHpp) / $prod->selling_price) * 100)]) }}
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-black/40 dark:text-white/40 italic">{{ $business->currency_symbol }} 0</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5 flex-wrap justify-center">
                                        @if (\App\Support\Context::hasPermission('products.edit') || \App\Support\Context::hasPermission('products.manage'))
                                            <button type="button"
                                                @click="toggleProductField('{{ $prod->id }}', 'show_in_website')"
                                                :disabled="productToggles['{{ $prod->id }}']?.loading === 'show_in_website'"
                                                :class="productToggles['{{ $prod->id }}']?.show_in_website ?
                                                    'bg-[#007AFF]/10 text-[#007AFF] border-[#007AFF]/30 font-semibold' :
                                                    'bg-black/[0.03] dark:bg-white/[0.04] text-black/35 dark:text-white/35 border-black/5 dark:border-white/5 opacity-60 line-through'"
                                                class="h-6 px-1.5 rounded-[6px] border text-[11px] font-medium transition-all hover:scale-105 active:scale-95 flex items-center"
                                                :title="__('products.channel_web_title')">
                                                <span>{{ __('products.channel_web') }}</span>
                                            </button>
                                            <button type="button"
                                                @click="toggleProductField('{{ $prod->id }}', 'show_in_pos')"
                                                :disabled="productToggles['{{ $prod->id }}']?.loading === 'show_in_pos'"
                                                :class="productToggles['{{ $prod->id }}']?.show_in_pos ?
                                                    'bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] border-[#34C759]/30 font-semibold' :
                                                    'bg-black/[0.03] dark:bg-white/[0.04] text-black/35 dark:text-white/35 border-black/5 dark:border-white/5 opacity-60 line-through'"
                                                class="h-6 px-1.5 rounded-[6px] border text-[11px] font-medium transition-all hover:scale-105 active:scale-95 flex items-center"
                                                :title="__('products.channel_pos_title')">
                                                <span>{{ __('products.channel_pos') }}</span>
                                            </button>
                                            <button type="button"
                                                @click="toggleProductField('{{ $prod->id }}', 'show_in_sales_order')"
                                                :disabled="productToggles['{{ $prod->id }}']
                                                    ?.loading === 'show_in_sales_order'"
                                                :class="productToggles['{{ $prod->id }}']?.show_in_sales_order ?
                                                    'bg-[#5856D6]/10 text-[#5856D6] dark:text-[#5E5CE6] border-[#5856D6]/30 font-semibold' :
                                                    'bg-black/[0.03] dark:bg-white/[0.04] text-black/35 dark:text-white/35 border-black/5 dark:border-white/5 opacity-60 line-through'"
                                                class="h-6 px-1.5 rounded-[6px] border text-[11px] font-medium transition-all hover:scale-105 active:scale-95 flex items-center"
                                                :title="__('products.channel_so_title')">
                                                <span>{{ __('products.channel_so') }}</span>
                                            </button>
                                            <button type="button"
                                                @click="toggleProductField('{{ $prod->id }}', 'show_price_on_web')"
                                                :disabled="productToggles['{{ $prod->id }}']?.loading === 'show_price_on_web'"
                                                :class="productToggles['{{ $prod->id }}']?.show_price_on_web ?
                                                    'bg-[#30B0C7]/10 text-[#0071A4] dark:text-[#70D7FF] border-[#30B0C7]/30 font-semibold' :
                                                    'bg-black/[0.03] dark:bg-white/[0.04] text-black/35 dark:text-white/35 border-black/5 dark:border-white/5 opacity-60 line-through'"
                                                class="h-6 px-1.5 rounded-[6px] border text-[11px] font-medium transition-all hover:scale-105 active:scale-95 flex items-center"
                                                :title="__('products.channel_price_title')">
                                                <span>{{ __('products.channel_price') }}</span>
                                            </button>
                                            <button type="button"
                                                @click="toggleProductField('{{ $prod->id }}', 'is_preorder')"
                                                :disabled="productToggles['{{ $prod->id }}']?.loading === 'is_preorder'"
                                                :class="productToggles['{{ $prod->id }}']?.is_preorder ?
                                                    'bg-[#FF9500]/10 text-[#FF9500] border-[#FF9500]/30 font-bold' :
                                                    'bg-black/[0.03] dark:bg-white/[0.04] text-black/35 dark:text-white/35 border-black/5 dark:border-white/5 opacity-60'"
                                                class="h-6 px-1.5 rounded-[6px] border text-[11px] font-medium transition-all hover:scale-105 active:scale-95 flex items-center"
                                                :title="__('products.channel_po_title')">
                                                <span>{{ __('products.channel_po') }}</span>
                                            </button>
                                            <button type="button"
                                                @click="toggleProductField('{{ $prod->id }}', 'is_active')"
                                                :disabled="productToggles['{{ $prod->id }}']?.loading === 'is_active'"
                                                :class="productToggles['{{ $prod->id }}']?.is_active ?
                                                    'bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] border-[#34C759]/30 font-semibold' :
                                                    'bg-[#FF3B30]/10 text-[#FF3B30] border-[#FF3B30]/30 opacity-70'"
                                                class="h-6 px-1.5 rounded-[6px] border text-[11px] font-medium transition-all hover:scale-105 active:scale-95 flex items-center"
                                                :title="__('products.channel_status_title')">
                                                <span
                                                    x-text="productToggles['{{ $prod->id }}']?.is_active ? '{{ __('products.status_active') }}' : '{{ __('products.status_inactive') }}'"></span>
                                            </button>
                                        @else
                                            <span class="h-6 px-1.5 rounded-[6px] border text-[11px] font-medium"
                                                :class="productToggles['{{ $prod->id }}']?.show_in_website ?
                                                    'bg-[#007AFF]/10 text-[#007AFF] border-[#007AFF]/30' :
                                                    'opacity-40 line-through'">{{ __('products.channel_web') }}</span>
                                            <span class="h-6 px-1.5 rounded-[6px] border text-[11px] font-medium"
                                                :class="productToggles['{{ $prod->id }}']?.show_in_pos ?
                                                    'bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] border-[#34C759]/30' :
                                                    'opacity-40 line-through'">{{ __('products.channel_pos') }}</span>
                                            <span class="h-6 px-1.5 rounded-[6px] border text-[11px] font-medium"
                                                :class="productToggles['{{ $prod->id }}']?.show_in_sales_order ?
                                                    'bg-[#5856D6]/10 text-[#5856D6] dark:text-[#5E5CE6] border-[#5856D6]/30' :
                                                    'opacity-40 line-through'">{{ __('products.channel_so') }}</span>
                                            <span class="h-6 px-1.5 rounded-[6px] border text-[11px] font-medium"
                                                :class="productToggles['{{ $prod->id }}']?.show_price_on_web ?
                                                    'bg-[#30B0C7]/10 text-[#0071A4] dark:text-[#70D7FF] border-[#30B0C7]/30' :
                                                    'opacity-40 line-through'">{{ __('products.channel_price') }}</span>
                                            <span class="h-6 px-1.5 rounded-[6px] border text-[11px] font-medium"
                                                :class="productToggles['{{ $prod->id }}']?.is_preorder ?
                                                    'bg-[#FF9500]/10 text-[#FF9500] border-[#FF9500]/30' : 'opacity-40'">{{ __('products.channel_po') }}</span>
                                            <span class="h-6 px-1.5 rounded-[6px] border text-[11px] font-medium"
                                                :class="productToggles['{{ $prod->id }}']?.is_active ?
                                                    'bg-[#34C759]/10 text-[#34C759]' : 'bg-[#FF3B30]/10 text-[#FF3B30]'"
                                                x-text="productToggles['{{ $prod->id }}']?.is_active ? '{{ __('products.status_active') }}' : '{{ __('products.status_inactive') }}'"></span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if (
                                            \App\Support\Context::hasPermission('products.view') ||
                                                \App\Support\Context::hasPermission('products.manage') ||
                                                \App\Support\Context::hasPermission('costing.manage'))
                                            <a href="{{ route('products.bom', $prod->slug) }}"
                                                class="h-8 px-2.5 rounded-[8px] text-[12px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 transition-colors flex items-center gap-1 min-w-[44px] justify-center">
                                                <span>{{ __('products.btn_bom') }}</span>
                                            </a>
                                        @endif

                                        @if (\App\Support\Context::hasPermission('costing.manage') || \App\Support\Context::hasPermission('costing.view_margin'))
                                            <a href="{{ route('calculator.index', ['product_id' => $prod->id, 'tab' => 'advanced']) }}"
                                                class="h-8 px-2.5 rounded-[8px] text-[12px] font-semibold text-[#AF52DE] bg-[#AF52DE]/10 hover:bg-[#AF52DE]/15 transition-colors flex items-center gap-1 min-w-[44px] justify-center"
                                                title="Hitung HPP & tetapkan margin harga jual di Kalkulator">
                                                <span>{{ __('products.btn_calculation') }}</span>
                                            </a>
                                        @endif

                                        @if (\App\Support\Context::hasPermission('products.edit') || \App\Support\Context::hasPermission('products.manage'))
                                            <button type="button" @click="openBranchPricesModal('{{ $prod->id }}')"
                                                class="h-8 px-2.5 rounded-[8px] text-[12px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 transition-colors flex items-center gap-1 min-w-[44px] justify-center"
                                                title="Atur Harga Khusus per Cabang">
                                                <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                                                <span>{{ __('products.btn_branch') }}</span>
                                            </button>
                                        @endif

                                        @if (\App\Support\Context::hasPermission('products.edit') || \App\Support\Context::hasPermission('products.manage'))
                                            <button type="button"
                                                @click="openEditModal({
                                    id: '{{ $prod->id }}',
                                    slug: '{{ $prod->slug }}',
                                    name: '{{ addslashes($prod->name) }}',
                                    sku: '{{ addslashes($prod->code ?? '') }}',
                                    category_id: '{{ $prod->category_id ?? '' }}',
                                    output_unit_id: '{{ $prod->output_unit_id }}',
                                    base_cost: {{ (float) $prod->base_cost }},
                                    selling_price: {{ (float) $prod->selling_price }},
                                    min_stock: {{ (float) $prod->min_stock }},
                                    weight: {{ (float) ($prod->weight ?? 200) }},
                                    length: {{ $prod->length !== null ? (float) $prod->length : "''" }},
                                    width: {{ $prod->width !== null ? (float) $prod->width : "''" }},
                                    height: {{ $prod->height !== null ? (float) $prod->height : "''" }},
                                    is_active: {{ $prod->is_active ? 'true' : 'false' }},
                                    show_in_website: {{ $prod->show_in_website ?? true ? 'true' : 'false' }},
                                    show_in_pos: {{ $prod->show_in_pos ?? true ? 'true' : 'false' }},
                                    show_in_sales_order: {{ $prod->show_in_sales_order ?? true ? 'true' : 'false' }},
                                    show_price_on_web: {{ $prod->show_price_on_web ?? true ? 'true' : 'false' }},
                                    is_preorder: {{ $prod->is_preorder ?? false ? 'true' : 'false' }},
                                    preorder_mode: '{{ $prod->preorder_mode ?? 'customer_schedule' }}',
                                    preorder_lead_days: {{ (int) ($prod->preorder_lead_days ?? 1) }},
                                    description: '{{ addslashes($prod->description ?? '') }}',
                                    image_url: '{{ addslashes($prod->image_url ?? '') }}',
                                    is_bundle: {{ $prod->isBundle() ? 'true' : 'false' }},
                                    bundle_items: {{ Js::from($prod->bundleItems->map(fn($bi) => ['child_product_id' => (string)$bi->child_product_id, 'quantity' => (float)$bi->quantity])) }},
                                    channel_prices: {{ Js::from($prod->channelPrices->pluck('price', 'channel')) }},
                                    images: {{ Js::from($prod->images->map(fn($img) => ['id' => $img->id, 'image_url' => $img->image_url, 'caption' => $img->caption])) }}
                                })"
                                                class="h-8 px-2.5 rounded-[8px] text-[12px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition-colors flex items-center min-w-[44px] justify-center"
                                                title="Edit Produk">
                                                {{ __('products.btn_edit') }}
                                            </button>
                                        @endif

                                        @if (\App\Support\Context::hasPermission('products.delete') || \App\Support\Context::hasPermission('products.manage'))
                                            <button type="button"
                                                @click="openDelete('{{ $prod->id }}', {{ Js::from($prod->name) }})"
                                                class="h-8 px-2.5 rounded-[8px] text-[12px] font-medium text-[#FF3B30] hover:bg-[#FF3B30]/8 transition-colors flex items-center min-w-[44px] justify-center"
                                                title="Hapus Produk">
                                                {{ __('products.btn_delete') }}
                                            </button>
                                            <form id="form-delete-{{ $prod->id }}" method="POST"
                                                action="{{ route('products.destroy', $prod->id) }}" class="hidden">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-14 text-center text-black/40 dark:text-white/40">
                                    {{ __('products.empty_catalog') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($products->hasPages())
                <div
                    class="px-5 py-3.5 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between text-[13px] text-black/60 dark:text-white/60">
                    {{ $products->links() }}
                </div>
            @endif
        </div>

        <!-- ===================================================== -->
        <!-- 5. MOBILE GROUPED INSET LIST (Standar iOS List View)   -->
        <!-- ===================================================== -->
        <div class="sm:hidden space-y-3">
            @forelse($products as $prod)
                @php
                    $activeModel = $prod->costModels->first();
                    $latestVer = $activeModel?->latestVersion;
                    $effectiveHpp = $latestVer ? (float) $latestVer->hpp_per_unit : (float) $prod->base_cost;
                @endphp
                <div
                    class="p-4 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] space-y-3 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <div
                                class="w-11 h-11 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 overflow-hidden flex items-center justify-center shrink-0">
                                @if ($prod->image_url)
                                    <img src="{{ $prod->image_url }}" alt="{{ $prod->name }}"
                                        class="w-full h-full object-cover">
                                @else
                                    <i data-lucide="package" class="w-5 h-5 text-black/40 dark:text-white/40"></i>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <p class="text-[15px] font-semibold text-black dark:text-white truncate">
                                        {{ $prod->name }}</p>
                                    @if ($prod->isBundle())
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-500/30">
                                            {{ __('products.badge_combo', ['count' => $prod->bundleItems->count()]) }}
                                        </span>
                                    @endif
                                    <span class="w-2 h-2 rounded-full shrink-0"
                                        :class="productToggles['{{ $prod->id }}']?.is_active ? 'bg-[#34C759]' :
                                            'bg-[#FF3B30]'"></span>
                                </div>
                                <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5 tabular-nums">
                                    {{ $prod->category?->name ?? __('products.category_general') }} · {{ $prod->outputUnit?->name ?? 'pcs' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5 shrink-0">
                            @if (
                                \App\Support\Context::hasPermission('products.view') ||
                                    \App\Support\Context::hasPermission('products.manage') ||
                                    \App\Support\Context::hasPermission('costing.manage'))
                                <a href="{{ route('products.bom', $prod->slug) }}"
                                    class="h-9 px-3 rounded-[8px] text-[12px] font-semibold text-[#007AFF] bg-[#007AFF]/10 flex items-center min-w-[44px] justify-center">
                                    {{ __('products.btn_bom') }}
                                </a>
                            @endif
                            @if (\App\Support\Context::hasPermission('products.edit') || \App\Support\Context::hasPermission('products.manage'))
                                <button type="button" @click="openBranchPricesModal('{{ $prod->id }}')"
                                    class="h-9 px-3 rounded-[8px] text-[12px] font-semibold text-[#007AFF] bg-[#007AFF]/10 flex items-center min-w-[44px] justify-center">
                                    {{ __('products.btn_branch') }}
                                </button>
                            @endif
                            @if (\App\Support\Context::hasPermission('products.edit') || \App\Support\Context::hasPermission('products.manage'))
                                <button type="button"
                                    @click="openEditModal({
                        id: '{{ $prod->id }}',
                        slug: '{{ $prod->slug }}',
                        name: '{{ addslashes($prod->name) }}',
                        sku: '{{ addslashes($prod->code ?? '') }}',
                        category_id: '{{ $prod->category_id ?? '' }}',
                        output_unit_id: '{{ $prod->output_unit_id }}',
                        base_cost: {{ (float) $prod->base_cost }},
                        selling_price: {{ (float) $prod->selling_price }},
                        min_stock: {{ (float) $prod->min_stock }},
                        weight: {{ (float) ($prod->weight ?? 200) }},
                        length: {{ $prod->length !== null ? (float) $prod->length : "''" }},
                        width: {{ $prod->width !== null ? (float) $prod->width : "''" }},
                        height: {{ $prod->height !== null ? (float) $prod->height : "''" }},
                        is_active: {{ $prod->is_active ? 'true' : 'false' }},
                        show_in_website: {{ $prod->show_in_website ?? true ? 'true' : 'false' }},
                        show_in_pos: {{ $prod->show_in_pos ?? true ? 'true' : 'false' }},
                        show_in_sales_order: {{ $prod->show_in_sales_order ?? true ? 'true' : 'false' }},
                        show_price_on_web: {{ $prod->show_price_on_web ?? true ? 'true' : 'false' }},
                        is_preorder: {{ $prod->is_preorder ?? false ? 'true' : 'false' }},
                        preorder_mode: '{{ $prod->preorder_mode ?? 'customer_schedule' }}',
                        preorder_lead_days: {{ (int) ($prod->preorder_lead_days ?? 1) }},
                        description: '{{ addslashes($prod->description ?? '') }}',
                        image_url: '{{ addslashes($prod->image_url ?? '') }}',
                        is_bundle: {{ $prod->isBundle() ? 'true' : 'false' }},
                        bundle_items: {{ Js::from($prod->bundleItems->map(fn($bi) => ['child_product_id' => (string)$bi->child_product_id, 'quantity' => (float)$bi->quantity])) }},
                        channel_prices: {{ Js::from($prod->channelPrices->pluck('price', 'channel')) }},
                        images: {{ Js::from($prod->images->map(fn($img) => ['id' => $img->id, 'image_url' => $img->image_url, 'caption' => $img->caption])) }}
                    })"
                                    class="h-9 px-3 rounded-[8px] text-[12px] font-medium text-black/70 dark:text-white/70 bg-black/[0.05] dark:bg-white/[0.08] flex items-center min-w-[44px] justify-center">
                                    {{ __('products.btn_edit') }}
                                </button>
                            @endif
                            @if (\App\Support\Context::hasPermission('products.delete') || \App\Support\Context::hasPermission('products.manage'))
                                <button type="button"
                                    @click="openDelete('{{ $prod->id }}', {{ Js::from($prod->name) }})"
                                    class="h-9 px-3 rounded-[8px] text-[12px] font-medium text-[#FF3B30] bg-[#FF3B30]/10 flex items-center min-w-[44px] justify-center">
                                    {{ __('products.btn_delete') }}
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- Price & HPP Row -->
                    <div
                        class="flex items-center justify-between pt-2 border-t border-black/[0.04] dark:border-white/[0.06] text-[13px]">
                        <span class="text-black/50 dark:text-white/50">{{ __('products.selling_price_label') }}</span>
                        <div class="text-right">
                            <span class="font-bold text-[#34C759] dark:text-[#30D158] tabular-nums text-[14px]">
                                {{ $business->currency_symbol }}
                                {{ number_format((float) $prod->selling_price, 0, ',', '.') }}
                            </span>
                            @if ($effectiveHpp > 0)
                                <span class="text-[11px] text-black/45 dark:text-white/45 block tabular-nums">
                                    HPP: {{ $business->currency_symbol }}
                                    {{ number_format($effectiveHpp, 0, ',', '.') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Quick Toggle Strip on Mobile -->
                    <div
                        class="pt-2 border-t border-black/[0.04] dark:border-white/[0.06] flex items-center gap-1.5 flex-wrap">
                        <span
                            class="text-[10px] uppercase font-semibold text-black/40 dark:text-white/40 tracking-wider mr-1">{{ __('products.saluran_label') }}</span>
                        @if (\App\Support\Context::hasPermission('products.edit') || \App\Support\Context::hasPermission('products.manage'))
                            <button type="button" @click="toggleProductField('{{ $prod->id }}', 'show_in_website')"
                                :disabled="productToggles['{{ $prod->id }}']?.loading === 'show_in_website'"
                                :class="productToggles['{{ $prod->id }}']?.show_in_website ?
                                    'bg-[#007AFF]/10 text-[#007AFF] border-[#007AFF]/30 font-semibold' :
                                    'bg-black/[0.03] dark:bg-white/[0.04] text-black/35 dark:text-white/35 border-black/5 dark:border-white/5 opacity-60 line-through'"
                                class="h-7 px-2.5 rounded-[6px] border text-[11px] font-medium min-w-[44px]">{{ __('products.channel_web') }}</button>
                            <button type="button" @click="toggleProductField('{{ $prod->id }}', 'show_in_pos')"
                                :disabled="productToggles['{{ $prod->id }}']?.loading === 'show_in_pos'"
                                :class="productToggles['{{ $prod->id }}']?.show_in_pos ?
                                    'bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] border-[#34C759]/30 font-semibold' :
                                    'bg-black/[0.03] dark:bg-white/[0.04] text-black/35 dark:text-white/35 border-black/5 dark:border-white/5 opacity-60 line-through'"
                                class="h-7 px-2.5 rounded-[6px] border text-[11px] font-medium min-w-[44px]">{{ __('products.channel_pos') }}</button>
                            <button type="button"
                                @click="toggleProductField('{{ $prod->id }}', 'show_in_sales_order')"
                                :disabled="productToggles['{{ $prod->id }}']?.loading === 'show_in_sales_order'"
                                :class="productToggles['{{ $prod->id }}']?.show_in_sales_order ?
                                    'bg-[#5856D6]/10 text-[#5856D6] dark:text-[#5E5CE6] border-[#5856D6]/30 font-semibold' :
                                    'bg-black/[0.03] dark:bg-white/[0.04] text-black/35 dark:text-white/35 border-black/5 dark:border-white/5 opacity-60 line-through'"
                                class="h-7 px-2.5 rounded-[6px] border text-[11px] font-medium min-w-[44px]">{{ __('products.channel_so') }}</button>
                            <button type="button"
                                @click="toggleProductField('{{ $prod->id }}', 'show_price_on_web')"
                                :disabled="productToggles['{{ $prod->id }}']?.loading === 'show_price_on_web'"
                                :class="productToggles['{{ $prod->id }}']?.show_price_on_web ?
                                    'bg-[#30B0C7]/10 text-[#0071A4] dark:text-[#70D7FF] border-[#30B0C7]/30 font-semibold' :
                                    'bg-black/[0.03] dark:bg-white/[0.04] text-black/35 dark:text-white/35 border-black/5 dark:border-white/5 opacity-60 line-through'"
                                class="h-7 px-2.5 rounded-[6px] border text-[11px] font-medium min-w-[44px]">{{ __('products.channel_price') }}</button>
                            <button type="button" @click="toggleProductField('{{ $prod->id }}', 'is_preorder')"
                                :disabled="productToggles['{{ $prod->id }}']?.loading === 'is_preorder'"
                                :class="productToggles['{{ $prod->id }}']?.is_preorder ?
                                    'bg-[#FF9500]/10 text-[#FF9500] border-[#FF9500]/30 font-bold' :
                                    'bg-black/[0.03] dark:bg-white/[0.04] text-black/35 dark:text-white/35 border-black/5 dark:border-white/5 opacity-60'"
                                class="h-7 px-2.5 rounded-[6px] border text-[11px] font-medium min-w-[44px]">{{ __('products.channel_po') }}</button>
                            <button type="button" @click="toggleProductField('{{ $prod->id }}', 'is_active')"
                                :disabled="productToggles['{{ $prod->id }}']?.loading === 'is_active'"
                                :class="productToggles['{{ $prod->id }}']?.is_active ?
                                    'bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] border-[#34C759]/30 font-semibold' :
                                    'bg-[#FF3B30]/10 text-[#FF3B30] border-[#FF3B30]/30 opacity-70'"
                                class="h-7 px-2.5 rounded-[6px] border text-[11px] font-medium min-w-[44px]">
                                <span
                                    x-text="productToggles['{{ $prod->id }}']?.is_active ? '{{ __('products.status_active') }}' : '{{ __('products.status_inactive') }}'"></span>
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div
                    class="p-8 text-center text-black/40 dark:text-white/40 text-[13px] bg-white dark:bg-[#1C1C1E] rounded-[16px] border border-black/[0.06] dark:border-white/[0.08]">
                    {{ __('products.empty_catalog_mobile') }}
                </div>
            @endforelse
            @if ($products->hasPages())
                <div class="p-3">
                    {{ $products->links() }}
                </div>
            @endif
        </div>

        <!-- ===================================================== -->
        <!-- 6. MODAL: TAMBAH PRODUK BARU (Full Layout XXL)        -->
        <!-- ===================================================== -->
        @if (\App\Support\Context::hasPermission('products.create') || \App\Support\Context::hasPermission('products.manage'))
            <template x-teleport="body">
                <div x-show="showAddModal" x-cloak
                    class="fixed inset-0 z-[200] flex items-center justify-center p-0 sm:p-4 md:p-6 bg-black/60 dark:bg-black/75 backdrop-blur-md"
                    @keydown.escape.window="showAddModal = false">
                    <div class="w-full max-w-full sm:max-w-[95vw] lg:max-w-5xl xl:max-w-6xl 2xl:max-w-[1250px] inset-x-0 bottom-0 sm:inset-auto sm:my-auto rounded-t-[28px] sm:rounded-[24px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] shadow-[0_25px_60px_rgba(0,0,0,0.3)] max-h-[94vh] sm:max-h-[90vh] flex flex-col overflow-hidden"
                        @click.outside="showAddModal = false">

                        <!-- Mobile Grab Bar -->
                        <div class="w-10 h-1.5 rounded-full bg-black/20 dark:bg-white/20 mx-auto my-2.5 sm:hidden shrink-0">
                        </div>

                        <!-- Sticky Top Header -->
                        <div
                            class="sticky top-0 z-20 backdrop-blur-xl bg-white/95 dark:bg-[#1C1C1E]/95 border-b border-black/[0.06] dark:border-white/[0.08] px-5 sm:px-8 py-4 sm:py-5 flex items-center justify-between gap-4 shrink-0">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <div
                                    class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <h2
                                        class="text-[18px] sm:text-[20px] font-bold text-black dark:text-white tracking-tight leading-snug truncate">
                                        {{ __('products.modal_create_title') }}</h2>
                                    <p class="text-[12px] sm:text-[13px] text-black/50 dark:text-white/50 truncate">{{ __('products.modal_create_sub') }}</p>
                                </div>
                            </div>
                            <button type="button" @click="showAddModal = false"
                                class="w-8 sm:w-9 h-8 sm:h-9 rounded-full bg-black/[0.05] dark:bg-white/[0.1] hover:bg-black/[0.1] dark:hover:bg-white/[0.15] text-black/60 dark:text-white/60 flex items-center justify-center active:scale-95 transition-all shrink-0"
                                title="{{ __('products.btn_close') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Form Content (Scrollable 12-Column Bento Grid) -->
                        <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data"
                            class="flex-1 overflow-y-auto p-5 sm:p-8 overscroll-contain sidebar-scroll flex flex-col justify-between">
                            @csrf
                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                                <!-- Kolom Kiri: Data Utama Produk (7 Kolom) -->
                                <div class="lg:col-span-7 space-y-5">
                                    <div
                                        class="p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-4">
                                        <h3 class="text-[14px] font-bold text-black dark:text-white tracking-tight">{{ __('products.section_basic_info') }}</h3>

                                        <div>
                                            <label
                                                class="block font-medium text-black/70 dark:text-white/70 mb-1 text-[13px]">{{ __('products.label_product_name') }} <span class="text-[#FF3B30]">*</span></label>
                                            <input type="text" name="name" required
                                                placeholder="{{ __('products.placeholder_product_name') }}"
                                                class="w-full h-11 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <div>
                                                <div class="flex items-center justify-between mb-1">
                                                    <label
                                                        class="font-medium text-black/70 dark:text-white/70 text-[13px]">{{ __('products.label_output_unit') }} <span class="text-[#FF3B30]">*</span></label>
                                                    <button type="button"
                                                        @click="quickAddTarget = 'add'; showAddUnitModal = true"
                                                        class="text-[12px] font-semibold text-[#007AFF] hover:underline flex items-center gap-0.5">
                                                        <span>{{ __('products.btn_quick_unit') }}</span>
                                                    </button>
                                                </div>
                                                <select name="output_unit_id" x-ref="newProductUnit" required
                                                    class="w-full h-11 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                                    <template x-for="u in unitList" :key="u.id">
                                                        <option :value="u.id" x-text="u.name + ' (' + u.code + ')'"
                                                            :selected="u.code === 'pcs'"></option>
                                                    </template>
                                                </select>
                                            </div>
                                            <div>
                                                <div class="flex items-center justify-between mb-1">
                                                    <label
                                                        class="font-medium text-black/70 dark:text-white/70 text-[13px]">{{ __('products.label_category') }}</label>
                                                    <button type="button"
                                                        @click="quickAddTarget = 'add'; showAddCategoryModal = true"
                                                        class="text-[12px] font-semibold text-[#007AFF] hover:underline flex items-center gap-0.5">
                                                        <span>{{ __('products.btn_quick_category') }}</span>
                                                    </button>
                                                </div>
                                                <select name="category_id" x-ref="newProductCategory" x-model="newSelectedCategoryId"
                                                    class="w-full h-11 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                                    <option value="">{{ __('products.no_category') }}</option>
                                                    <template x-for="c in categoryList" :key="c.id">
                                                        <option :value="c.id" x-text="c.name"></option>
                                                    </template>
                                                </select>
                                            </div>
                                        </div>

                                        <div>
                                            <label
                                                class="block font-medium text-black/70 dark:text-white/70 mb-1 text-[13px]">{{ __('products.label_costing_method') }} <span class="text-[#FF3B30]">*</span></label>
                                            <select name="costing_method" required
                                                class="w-full h-11 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                                <option value="recipe_bom">{{ __('products.method_recipe_bom') }}</option>
                                                <option value="simple">{{ __('products.method_simple') }}</option>
                                                <option value="job">{{ __('products.method_job') }}</option>
                                                <option value="process">{{ __('products.method_process') }}</option>
                                                <option value="abc">{{ __('products.method_abc') }}</option>
                                                <option value="service">{{ __('products.method_service') }}</option>
                                                <option value="retail">{{ __('products.method_retail') }}</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label
                                                class="block font-medium text-black/70 dark:text-white/70 mb-1 text-[13px]">{{ __('products.label_barcode_sku') }}</label>
                                            <div class="flex gap-2">
                                                <input type="text" name="sku" x-ref="newProductBarcode"
                                                    inputmode="numeric" autocomplete="off"
                                                    placeholder="{{ __('products.placeholder_barcode_sku') }}"
                                                    class="min-w-0 flex-1 h-11 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                                <button type="button" @click="requestProductScanner('add')"
                                                    class="shrink-0 h-11 px-3.5 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] hover:bg-[#007AFF]/15 flex items-center justify-center transition active:scale-95 min-w-[44px]"
                                                    title="{{ __('products.scan_camera_title') }}">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                        stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>

                                        <div>
                                            <label
                                                class="block font-medium text-black/70 dark:text-white/70 mb-1 text-[13px]">{{ __('products.label_description') }}</label>
                                            <textarea name="description" rows="3" placeholder="{{ __('products.placeholder_description') }}"
                                                class="w-full bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] p-3 text-[16px] sm:text-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition"></textarea>
                                        </div>
                                    </div>

                                    <!-- Bento Box: Paket Kombo / Bundling Produk -->
                                    <div class="p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-4">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <span class="w-7 h-7 rounded-[8px] bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                                                        </svg>
                                                    </span>
                                                    <h3 class="text-[14px] font-bold text-black dark:text-white tracking-tight">{{ __('products.section_bundle_title') }}</h3>
                                                </div>
                                                <p class="text-[12px] text-black/50 dark:text-white/50 mt-1">{{ __('products.section_bundle_sub') }}</p>
                                            </div>
                                            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                                <input type="hidden" name="is_bundle" value="0">
                                                <input type="checkbox" name="is_bundle" value="1" x-model="newIsBundle" class="sr-only peer">
                                                <div class="w-11 h-6 bg-black/10 peer-focus:outline-none rounded-full peer dark:bg-white/10 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-amber-500"></div>
                                            </label>
                                        </div>

                                        <!-- Active Bundle Config Section -->
                                        <div x-show="newIsBundle" x-cloak class="space-y-3 pt-3 border-t border-black/[0.05] dark:border-white/[0.08]">
                                            <div class="flex items-center justify-between">
                                                <span class="text-[12px] font-semibold text-black/70 dark:text-white/70">{{ __('products.bundle_items_list') }}</span>
                                                <button type="button" @click="addBundleItem('add')" class="h-8 px-2.5 rounded-[8px] bg-amber-500/10 text-amber-600 dark:text-amber-400 hover:bg-amber-500/20 text-[12px] font-semibold flex items-center gap-1 transition min-w-[44px]">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                                    <span>{{ __('products.btn_add_bundle_product') }}</span>
                                                </button>
                                            </div>

                                            <template x-if="newBundleItems.length === 0">
                                                <div class="p-4 rounded-[12px] border border-dashed border-black/15 dark:border-white/15 text-center text-[12px] text-black/45 dark:text-white/45">
                                                    {{ __('products.bundle_empty') }}
                                                </div>
                                            </template>

                                            <div class="space-y-2">
                                                <template x-for="(item, idx) in newBundleItems" :key="idx">
                                                    <div class="p-3 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] flex items-center gap-2.5">
                                                        <div class="flex-1 min-w-0">
                                                            <select :name="'bundle_items[' + idx + '][child_product_id]'" x-model="item.child_product_id" required class="w-full h-9 px-2.5 bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] rounded-[8px] text-[13px] text-black dark:text-white focus:ring-1 focus:ring-amber-500">
                                                                <option value="">{{ __('products.placeholder_select_child_product') }}</option>
                                                                <template x-for="p in allProductsList" :key="p.id">
                                                                    <option :value="p.id" x-text="p.name + ' (Rp ' + Number(p.price).toLocaleString('id-ID') + ')'"></option>
                                                                </template>
                                                            </select>
                                                        </div>
                                                        <div class="w-24 shrink-0">
                                                            <div class="relative">
                                                                <input type="number" min="0.01" step="any" :name="'bundle_items[' + idx + '][quantity]'" x-model="item.quantity" placeholder="Qty" required class="w-full h-9 px-2.5 bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] rounded-[8px] text-[13px] text-black dark:text-white tabular-nums font-semibold focus:ring-1 focus:ring-amber-500 text-center">
                                                            </div>
                                                        </div>
                                                        <button type="button" @click="removeBundleItem('add', idx)" class="w-9 h-9 rounded-[8px] text-[#FF3B30] hover:bg-[#FF3B30]/10 flex items-center justify-center shrink-0 transition" title="{{ __('products.btn_delete') }}">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15" /></svg>
                                                        </button>
                                                    </div>
                                                </template>
                                            </div>

                                            <div x-show="newBundleItems.length > 0" class="p-3 rounded-[12px] bg-amber-500/10 border border-amber-500/20 text-[12px] space-y-1">
                                                <div class="flex justify-between text-black/70 dark:text-white/70">
                                                    <span>{{ __('products.bundle_est_cost') }}</span>
                                                    <span class="font-bold tabular-nums text-black dark:text-white" x-text="'Rp ' + getEstimatedBundleCost(newBundleItems).toLocaleString('id-ID')"></span>
                                                </div>
                                                <div class="flex justify-between text-black/70 dark:text-white/70">
                                                    <span>{{ __('products.bundle_est_normal_value') }}</span>
                                                    <span class="line-through tabular-nums text-black/50 dark:text-white/50" x-text="'Rp ' + getEstimatedBundleValue(newBundleItems).toLocaleString('id-ID')"></span>
                                                </div>
                                                <p class="text-[11px] text-amber-700 dark:text-amber-300 pt-1 border-t border-amber-500/20">
                                                    💡 <em>{{ __('products.bundle_bottleneck_tip') }}</em>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Kolom Kanan: Finansial, Media & Saluran (5 Kolom) -->
                                <div class="lg:col-span-5 space-y-5">
                                    <!-- Bento Box 1: Harga & Biaya -->
                                    <div
                                        class="p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3.5">
                                        <h3 class="text-[14px] font-bold text-black dark:text-white tracking-tight">{{ __('products.section_pricing_title') }}</h3>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            <div>
                                                <label
                                                    class="block font-medium text-black/70 dark:text-white/70 mb-1 text-[12px]">{{ __('products.label_base_cost') }}</label>
                                                <div class="relative">
                                                    <span
                                                        class="absolute left-3 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 font-medium text-[13px]">{{ $business->currency_symbol }}</span>
                                                    <input type="number" step="any" name="base_cost" value="0"
                                                        placeholder="0"
                                                        class="w-full h-10 pl-9 pr-3 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[10px] text-[16px] sm:text-[14px] text-black dark:text-white tabular-nums font-semibold focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                                                </div>
                                            </div>
                                            <div>
                                                <label
                                                    class="block font-medium text-[#34C759] dark:text-[#30D158] mb-1 text-[12px]">{{ __('products.label_selling_price') }}</label>
                                                <div class="relative">
                                                    <span
                                                        class="absolute left-3 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 font-medium text-[13px]">{{ $business->currency_symbol }}</span>
                                                    <input type="number" step="any" name="selling_price" value="0"
                                                        placeholder="0"
                                                        class="w-full h-10 pl-9 pr-3 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[10px] text-[16px] sm:text-[14px] text-black dark:text-white tabular-nums font-bold focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                                                </div>
                                            </div>
                                        </div>

                                        <div>
                                            <label
                                                class="block font-medium text-black/70 dark:text-white/70 mb-1 text-[12px]">{{ __('products.label_min_stock') }}</label>
                                            <input type="number" step="any" name="min_stock" value="0"
                                                placeholder="0"
                                                class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[10px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                                        </div>
                                    </div>

                                    @if ($business && ($business->isFoodIndustry() || $business->hasDineInFeature()))
                                    <!-- Bento Box: Multi-Harga Saluran (F&B / Online) -->
                                    <div class="p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3.5">
                                        <div class="flex items-center justify-between">
                                            <h3 class="text-[14px] font-bold text-black dark:text-white tracking-tight">{{ __('products.section_channel_prices_title') }}</h3>
                                            <span class="text-[11px] font-medium text-black/40 dark:text-white/40">{{ __('products.section_channel_prices_badge') }}</span>
                                        </div>
                                        <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('products.section_channel_prices_desc') }}</p>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-[11px] font-medium text-black/70 dark:text-white/70 mb-1">{{ __('products.label_dine_in') }}</label>
                                                <div class="relative">
                                                    <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 text-[12px]">{{ $business->currency_symbol }}</span>
                                                    <input type="number" step="any" name="channel_prices[dine_in]" placeholder="{{ __('products.placeholder_standard_price') }}" class="w-full h-9 pl-8 pr-2.5 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[8px] text-[13px] text-black dark:text-white tabular-nums focus:ring-1 focus:ring-[#007AFF]">
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-[11px] font-medium text-black/70 dark:text-white/70 mb-1">{{ __('products.label_takeaway') }}</label>
                                                <div class="relative">
                                                    <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 text-[12px]">{{ $business->currency_symbol }}</span>
                                                    <input type="number" step="any" name="channel_prices[takeaway]" placeholder="{{ __('products.placeholder_standard_price') }}" class="w-full h-9 pl-8 pr-2.5 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[8px] text-[13px] text-black dark:text-white tabular-nums focus:ring-1 focus:ring-[#007AFF]">
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-[11px] font-medium text-[#00AA13] dark:text-[#00C819] mb-1">{{ __('products.label_gofood') }}</label>
                                                <div class="relative">
                                                    <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 text-[12px]">{{ $business->currency_symbol }}</span>
                                                    <input type="number" step="any" name="channel_prices[gofood]" placeholder="{{ __('products.placeholder_channel_markup') }}" class="w-full h-9 pl-8 pr-2.5 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[8px] text-[13px] text-black dark:text-white tabular-nums focus:ring-1 focus:ring-[#00AA13]">
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-[11px] font-medium text-[#00B14F] dark:text-[#00D05C] mb-1">{{ __('products.label_grabfood') }}</label>
                                                <div class="relative">
                                                    <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 text-[12px]">{{ $business->currency_symbol }}</span>
                                                    <input type="number" step="any" name="channel_prices[grabfood]" placeholder="{{ __('products.placeholder_channel_markup') }}" class="w-full h-9 pl-8 pr-2.5 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[8px] text-[13px] text-black dark:text-white tabular-nums focus:ring-1 focus:ring-[#00B14F]">
                                                </div>
                                            </div>
                                            <div class="sm:col-span-2">
                                                <label class="block text-[11px] font-medium text-[#EE4D2D] dark:text-[#FF5B37] mb-1">{{ __('products.label_shopeefood') }}</label>
                                                <div class="relative">
                                                    <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 text-[12px]">{{ $business->currency_symbol }}</span>
                                                    <input type="number" step="any" name="channel_prices[shopeefood]" placeholder="{{ __('products.placeholder_channel_markup') }}" class="w-full h-9 pl-8 pr-2.5 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[8px] text-[13px] text-black dark:text-white tabular-nums focus:ring-1 focus:ring-[#EE4D2D]">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif

                                    <!-- Bento Box 2: Gambar Utama & Galeri Produk -->
                                    <div
                                        class="p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-4">
                                        <div class="flex items-center justify-between">
                                            <h3 class="text-[14px] font-bold text-black dark:text-white tracking-tight">{{ __('products.section_photos_title') }}</h3>
                                            <span class="text-[11px] font-medium text-black/40 dark:text-white/40">{{ __('products.section_photos_badge') }}</span>
                                        </div>

                                        <!-- Foto Utama (Thumbnail) -->
                                        <div class="space-y-2">
                                            <div class="flex items-center justify-between">
                                                <span class="text-[12px] font-semibold text-black/80 dark:text-white/80">{{ __('products.label_main_photo') }}</span>
                                                <span class="text-[11px] text-black/40 dark:text-white/40">{{ __('products.sub_main_photo') }}</span>
                                            </div>
                                            <div x-show="newProductPreview"
                                                class="w-20 h-20 rounded-[12px] overflow-hidden border border-black/10 dark:border-white/10 relative">
                                                <img :src="newProductPreview" alt="Preview Thumbnail"
                                                    class="w-full h-full object-cover">
                                                <button type="button"
                                                    @click="newProductPreview = ''; $refs.newProductImageInput.value = ''"
                                                    class="absolute top-1 right-1 p-1 rounded-full bg-black/70 text-white hover:text-[#FF3B30] transition">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                            <input type="file" name="image" x-ref="newProductImageInput"
                                                @change="handleNewProductImage($event)" accept="image/jpeg,image/png,image/webp"
                                                class="w-full text-[12px] text-black/60 dark:text-white/60 file:mr-3 file:rounded-[8px] file:border-0 file:bg-black/[0.06] dark:file:bg-white/[0.08] file:px-3 file:py-2 file:text-[12px] file:font-semibold">
                                        </div>

                                        <!-- Galeri Foto Tambahan (Toko Online) -->
                                        <div class="pt-3 border-t border-black/[0.06] dark:border-white/[0.08] space-y-2">
                                            <div class="flex items-center justify-between">
                                                <span class="text-[12px] font-semibold text-black/80 dark:text-white/80">{{ __('products.label_gallery_photos') }}</span>
                                                <span class="text-[11px] text-[#007AFF] font-medium" x-show="newGalleryPreviews.length > 0" x-text="newGalleryPreviews.length + ' foto dipilih'"></span>
                                            </div>
                                            <template x-if="newGalleryPreviews.length > 0">
                                                <div class="flex items-center gap-2 overflow-x-auto py-1">
                                                    <template x-for="(prev, pIdx) in newGalleryPreviews" :key="pIdx">
                                                        <div class="w-16 h-16 rounded-[10px] overflow-hidden border border-black/10 dark:border-white/10 shrink-0 relative bg-black/5">
                                                            <img :src="prev" alt="Gallery Preview" class="w-full h-full object-cover">
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                            <input type="file" name="gallery_images[]" multiple x-ref="newProductGalleryInput"
                                                @change="handleNewGalleryImages($event)" accept="image/jpeg,image/png,image/webp"
                                                class="w-full text-[12px] text-black/60 dark:text-white/60 file:mr-3 file:rounded-[8px] file:border-0 file:bg-[#007AFF]/10 file:text-[#007AFF] dark:file:bg-[#007AFF]/20 file:px-3 file:py-2 file:text-[12px] file:font-semibold">
                                            <p class="text-[11px] text-black/45 dark:text-white/45">{{ __('products.sub_gallery_photos') }}</p>
                                        </div>
                                    </div>

                                    <!-- Bento Box 3: Saluran Penjualan Multi-Channel -->
                                    <div
                                        class="p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3.5">
                                        <div class="flex items-center justify-between">
                                            <h3 class="text-[14px] font-bold text-black dark:text-white tracking-tight">{{ __('products.section_sales_channels') }}</h3>
                                            <span
                                                class="text-[11px] font-medium text-black/40 dark:text-white/40">{{ __('products.badge_multichannel') }}</span>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                            <label
                                                class="flex items-center gap-2 p-2.5 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] cursor-pointer hover:bg-black/[0.02] transition">
                                                <input type="hidden" name="show_in_pos" value="0">
                                                <input type="checkbox" name="show_in_pos" value="1" checked
                                                    class="rounded-[4px] border-black/20 text-[#007AFF] focus:ring-[#007AFF]">
                                                <span class="text-[12px] font-semibold text-black/80 dark:text-white/80">{{ __('products.cb_pos_cashier') }}</span>
                                            </label>
                                            <label
                                                class="flex items-center gap-2 p-2.5 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] cursor-pointer hover:bg-black/[0.02] transition">
                                                <input type="hidden" name="show_in_sales_order" value="0">
                                                <input type="checkbox" name="show_in_sales_order" value="1" checked
                                                    class="rounded-[4px] border-black/20 text-[#007AFF] focus:ring-[#007AFF]">
                                                <span class="text-[12px] font-semibold text-black/80 dark:text-white/80">{{ __('products.cb_sales_order') }}</span>
                                            </label>
                                            <label
                                                class="flex items-center gap-2 p-2.5 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] cursor-pointer hover:bg-black/[0.02] transition">
                                                <input type="hidden" name="show_in_website" value="0">
                                                <input type="checkbox" name="show_in_website" value="1" checked
                                                    class="rounded-[4px] border-black/20 text-[#007AFF] focus:ring-[#007AFF]">
                                                <span
                                                    class="text-[12px] font-semibold text-black/80 dark:text-white/80">{{ __('products.cb_web_storefront') }}</span>
                                            </label>
                                        </div>

                                        <div class="pt-2 border-t border-black/[0.04] dark:border-white/[0.06]">
                                            <label class="flex items-start gap-2.5 cursor-pointer">
                                                <input type="hidden" name="show_price_on_web" value="0">
                                                <input type="checkbox" name="show_price_on_web" value="1" checked
                                                    class="mt-0.5 rounded-[4px] border-black/20 text-[#007AFF] focus:ring-[#007AFF]">
                                                <div>
                                                    <span
                                                        class="text-[12px] font-semibold text-black/80 dark:text-white/80 block">{{ __('products.cb_show_price_web') }}</span>
                                                    <span class="text-[11px] text-black/45 dark:text-white/45 block">{{ __('products.sub_show_price_web') }}</span>
                                                </div>
                                            </label>
                                        </div>

                                        <!-- Pre-Order Section -->
                                        <div class="pt-2 border-t border-black/[0.04] dark:border-white/[0.06] space-y-2.5">
                                            <label class="flex items-start gap-2.5 cursor-pointer">
                                                <input type="hidden" name="is_preorder" value="0">
                                                <input type="checkbox" name="is_preorder" value="1"
                                                    x-model="newIsPreorder"
                                                    class="mt-0.5 rounded-[4px] border-black/20 text-[#FF9500] focus:ring-[#FF9500]">
                                                <div>
                                                    <span
                                                        class="text-[12px] font-semibold text-black/80 dark:text-white/80 block">{{ __('products.cb_preorder_system') }}</span>
                                                    <span class="text-[11px] text-black/45 dark:text-white/45 block">{{ __('products.sub_preorder_system') }}</span>
                                                </div>
                                            </label>

                                            <div x-show="newIsPreorder" x-cloak
                                                class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 p-3 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08]">
                                                <div>
                                                    <label
                                                        class="block text-[11px] font-medium text-black/60 dark:text-white/60 mb-1">{{ __('products.label_po_scheme') }}</label>
                                                    <select name="preorder_mode" x-model="newPreorderMode"
                                                        class="w-full h-9 px-2.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] text-[12px] text-black dark:text-white focus:ring-1 focus:ring-[#FF9500]">
                                                        <option value="customer_schedule">{{ __('products.po_customer_schedule') }}</option>
                                                        <option value="merchant_batch">{{ __('products.po_merchant_batch') }}</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label
                                                        class="block text-[11px] font-medium text-black/60 dark:text-white/60 mb-1">{{ __('products.label_po_lead_days') }}</label>
                                                    <div class="flex items-center gap-1.5">
                                                        <input type="number" min="0" max="90"
                                                            name="preorder_lead_days" x-model="newPreorderLeadDays"
                                                            class="w-16 h-9 px-2 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] text-[12px] text-black dark:text-white tabular-nums font-bold focus:ring-1 focus:ring-[#FF9500]">
                                                        <span class="text-[12px] text-black/60 dark:text-white/60">{{ __('products.unit_days') }} (H-x)</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Bento Box 4: Dimensi & Berat Pengiriman Logistik (Biteship / Ekspedisi) -->
                                    <div
                                        class="p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3.5">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-4 h-4 text-[#007AFF]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                </svg>
                                                <h3 class="text-[14px] font-bold text-black dark:text-white tracking-tight">{{ __('products.dimensions_weight_title') }}</h3>
                                            </div>
                                            <span class="text-[11px] font-medium text-black/40 dark:text-white/40">{{ __('products.logistics_badge') }}</span>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                            <div>
                                                <label class="block text-[11px] font-semibold text-black/70 dark:text-white/70 mb-1">
                                                    {{ __('products.label_weight') }} <span class="text-[#FF3B30]">*</span>
                                                </label>
                                                <div class="relative">
                                                    <input type="number" step="1" min="1" name="weight" value="200" required
                                                        placeholder="250"
                                                        class="w-full h-10 px-3 bg-white dark:bg-[#1C1C1E] border border-black/[0.1] dark:border-white/[0.1] rounded-[10px] text-[13px] text-black dark:text-white tabular-nums font-semibold focus:ring-2 focus:ring-[#007AFF] focus:border-transparent">
                                                    <span class="absolute right-3 top-2.5 text-[11px] font-medium text-black/40 dark:text-white/40">{{ __('products.unit_gram') }}</span>
                                                </div>
                                                <p class="text-[10px] text-black/45 dark:text-white/45 mt-1">{{ __('products.sub_weight_required') }}</p>
                                            </div>

                                            <div>
                                                <label class="block text-[11px] font-semibold text-black/70 dark:text-white/70 mb-1">
                                                    {{ __('products.label_length') }}
                                                </label>
                                                <div class="relative">
                                                    <input type="number" step="0.1" min="0" name="length"
                                                        placeholder="15"
                                                        class="w-full h-10 px-3 bg-white dark:bg-[#1C1C1E] border border-black/[0.1] dark:border-white/[0.1] rounded-[10px] text-[13px] text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#007AFF] focus:border-transparent">
                                                    <span class="absolute right-3 top-2.5 text-[11px] font-medium text-black/40 dark:text-white/40">{{ __('products.unit_cm') }}</span>
                                                </div>
                                                <p class="text-[10px] text-black/45 dark:text-white/45 mt-1">{{ __('products.sub_volumetric_optional') }}</p>
                                            </div>

                                            <div>
                                                <label class="block text-[11px] font-semibold text-black/70 dark:text-white/70 mb-1">
                                                    {{ __('products.label_width') }}
                                                </label>
                                                <div class="relative">
                                                    <input type="number" step="0.1" min="0" name="width"
                                                        placeholder="10"
                                                        class="w-full h-10 px-3 bg-white dark:bg-[#1C1C1E] border border-black/[0.1] dark:border-white/[0.1] rounded-[10px] text-[13px] text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#007AFF] focus:border-transparent">
                                                    <span class="absolute right-3 top-2.5 text-[11px] font-medium text-black/40 dark:text-white/40">{{ __('products.unit_cm') }}</span>
                                                </div>
                                                <p class="text-[10px] text-black/45 dark:text-white/45 mt-1">{{ __('products.sub_volumetric_optional') }}</p>
                                            </div>

                                            <div>
                                                <label class="block text-[11px] font-semibold text-black/70 dark:text-white/70 mb-1">
                                                    {{ __('products.label_height') }}
                                                </label>
                                                <div class="relative">
                                                    <input type="number" step="0.1" min="0" name="height"
                                                        placeholder="5"
                                                        class="w-full h-10 px-3 bg-white dark:bg-[#1C1C1E] border border-black/[0.1] dark:border-white/[0.1] rounded-[10px] text-[13px] text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#007AFF] focus:border-transparent">
                                                    <span class="absolute right-3 top-2.5 text-[11px] font-medium text-black/40 dark:text-white/40">{{ __('products.unit_cm') }}</span>
                                                </div>
                                                <p class="text-[10px] text-black/45 dark:text-white/45 mt-1">{{ __('products.sub_volumetric_optional') }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Bento Box 5: Integrasi Marketplace Omnichannel & Taxonomy Guardrail -->
                                    <div class="p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-4">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="space-y-0.5">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <i data-lucide="shopping-bag" class="w-4 h-4 text-[#007AFF]"></i>
                                                    <h3 class="text-[14px] font-bold text-black dark:text-white tracking-tight">{{ __('products.section_marketplace_title') }}</h3>
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#007AFF]/10 text-[#007AFF]">{{ __('products.section_marketplace_badge') }}</span>
                                                </div>
                                                <p class="text-[11.5px] text-black/50 dark:text-white/50">
                                                    {{ __('products.section_marketplace_desc') }}
                                                </p>
                                            </div>
                                            <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-0.5" title="{{ __('products.marketplace_switch_title') }}">
                                                <input type="checkbox" x-model="newIsMarketplaceEnabled" class="sr-only peer">
                                                <div class="w-11 h-6 bg-black/15 peer-focus:outline-none rounded-full peer dark:bg-white/20 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#007AFF]"></div>
                                            </label>
                                        </div>

                                        <!-- Hidden State: Minimal Info for POS only -->
                                        <div x-show="!newIsMarketplaceEnabled" class="p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/[0.04] dark:border-white/[0.04] text-[11.5px] text-black/55 dark:text-white/55 flex items-center gap-2">
                                            <i data-lucide="info" class="w-4 h-4 text-black/40 dark:text-white/40 shrink-0"></i>
                                            <span>{!! __('products.marketplace_local_notice_html') !!}</span>
                                        </div>

                                        <!-- Active State: Progressive Disclosure of Taxonomy & Marketplace Mapping -->
                                        <div x-show="newIsMarketplaceEnabled" x-cloak class="space-y-3.5 pt-1">
                                            <!-- Kategori Taksonomi Marketplace Status Banner -->
                                            <div class="p-3.5 rounded-[14px] border transition-all"
                                                :class="getInheritedMarketplaceCategory(newSelectedCategoryId)
                                                    ? 'bg-[#34C759]/8 border-[#34C759]/25'
                                                    : 'bg-amber-500/8 border-amber-500/25'">
                                                <div class="flex items-start gap-2.5">
                                                    <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 mt-0.5"
                                                        :class="getInheritedMarketplaceCategory(newSelectedCategoryId) ? 'bg-[#34C759]/15 text-[#34C759]' : 'bg-amber-500/15 text-amber-600 dark:text-amber-400'">
                                                        <i :data-lucide="getInheritedMarketplaceCategory(newSelectedCategoryId) ? 'check' : 'alert-triangle'" class="w-3.5 h-3.5"></i>
                                                    </div>
                                                    <div class="space-y-1 min-w-0 flex-1 text-[12px]">
                                                        <template x-if="getInheritedMarketplaceCategory(newSelectedCategoryId)">
                                                            <div>
                                                                <p class="font-bold text-black dark:text-white flex items-center gap-1.5 flex-wrap">
                                                                    <span>{{ __('products.marketplace_mapped_title') }}</span>
                                                                    <span class="px-2 py-0.5 rounded-full bg-[#34C759]/15 text-[#34C759] text-[11px] font-mono font-bold"
                                                                        x-text="getInheritedMarketplaceCategory(newSelectedCategoryId).name + ' (ID: ' + getInheritedMarketplaceCategory(newSelectedCategoryId).id + ')'"></span>
                                                                </p>
                                                                <p class="text-black/60 dark:text-white/60 text-[11px]">
                                                                    {{ __('products.marketplace_mapped_desc') }}
                                                                </p>
                                                            </div>
                                                        </template>
                                                        <template x-if="!getInheritedMarketplaceCategory(newSelectedCategoryId)">
                                                            <div>
                                                                <p class="font-bold text-black dark:text-white">
                                                                    {{ __('products.marketplace_unmapped_title') }}
                                                                </p>
                                                                <p class="text-black/60 dark:text-white/60 text-[11px]">
                                                                    {{ __('products.marketplace_unmapped_desc') }}
                                                                </p>
                                                                <div class="pt-1.5 flex items-center gap-2 flex-wrap">
                                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-[8px] bg-amber-500/15 text-amber-700 dark:text-amber-300 font-semibold text-[11px]">
                                                                        <i data-lucide="sparkles" class="w-3 h-3"></i>
                                                                        <span x-text="'{{ __('products.marketplace_ai_suggest') }} ' + (suggestMarketplaceCategory('', newSelectedCategoryId)?.name || 'Makanan & Minuman')"></span>
                                                                    </span>
                                                                    <a href="{{ route('product-categories.index') }}" target="_blank" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-0.5">
                                                                        <span>{{ __('products.btn_manage_category_master') }}</span>
                                                                        <i data-lucide="external-link" class="w-3 h-3"></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Guardrail & Anti-Margin Bleed Info -->
                                            <div class="p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] text-[11.5px] space-y-1.5">
                                                <div class="flex items-center justify-between font-medium text-black/70 dark:text-white/70">
                                                    <span>{{ __('products.marketplace_guardrail_title') }}</span>
                                                    <span class="text-[#007AFF] font-semibold">{{ __('products.marketplace_fee_est') }}</span>
                                                </div>
                                                <p class="text-black/50 dark:text-white/50 text-[11px] leading-relaxed">
                                                    {!! __('products.marketplace_guardrail_desc_html', ['url' => route('marketplace-hub.products')]) !!}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sticky Bottom Action Footer -->
                            <div
                                class="sticky bottom-0 -mx-5 sm:-mx-8 -mb-5 sm:-mb-8 mt-6 backdrop-blur-xl bg-white/95 dark:bg-[#1C1C1E]/95 border-t border-black/[0.06] dark:border-white/[0.08] px-5 sm:px-8 py-4 flex items-center justify-end gap-3 shrink-0">
                                <button type="button" @click="showAddModal = false"
                                    class="h-11 px-5 rounded-[12px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] transition active:scale-[0.98] min-w-[44px]">
                                    {{ __('products.btn_cancel') }}
                                </button>
                                <button type="submit"
                                    class="h-11 px-6 rounded-[12px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition shadow-sm shadow-[#007AFF]/25 min-w-[44px]">
                                    {{ __('products.btn_save') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </template>
        @endif
        <!-- ===================================================== -->
        <!-- 7. MODAL: EDIT DATA PRODUK (Full Layout XXL)          -->
        <!-- ===================================================== -->
        @if (\App\Support\Context::hasPermission('products.edit') || \App\Support\Context::hasPermission('products.manage'))
            <template x-teleport="body">
                <div x-show="showEditModal" x-cloak
                    class="fixed inset-0 z-[200] flex items-center justify-center p-0 sm:p-4 md:p-6 bg-black/60 dark:bg-black/75 backdrop-blur-md"
                    @keydown.escape.window="showEditModal = false">
                    <div class="w-full max-w-full sm:max-w-[95vw] lg:max-w-5xl xl:max-w-6xl 2xl:max-w-[1250px] inset-x-0 bottom-0 sm:inset-auto sm:my-auto rounded-t-[28px] sm:rounded-[24px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] shadow-[0_25px_60px_rgba(0,0,0,0.3)] max-h-[94vh] sm:max-h-[90vh] flex flex-col overflow-hidden"
                        @click.outside="showEditModal = false">

                        <!-- Mobile Grab Bar -->
                        <div class="w-10 h-1.5 rounded-full bg-black/20 dark:bg-white/20 mx-auto my-2.5 sm:hidden shrink-0">
                        </div>

                        <!-- Sticky Top Header -->
                        <div
                            class="sticky top-0 z-20 backdrop-blur-xl bg-white/95 dark:bg-[#1C1C1E]/95 border-b border-black/[0.06] dark:border-white/[0.08] px-5 sm:px-8 py-4 sm:py-5 flex items-center justify-between gap-4 shrink-0">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <div
                                    class="w-10 h-10 rounded-[12px] bg-[#5856D6]/10 text-[#5856D6] dark:text-[#5E5CE6] flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <h2
                                        class="text-[18px] sm:text-[20px] font-bold text-black dark:text-white tracking-tight leading-snug truncate">
                                        {{ __('products.modal_edit_title') }}: <span x-text="editProduct.name" class="text-[#007AFF]"></span>
                                    </h2>
                                    <p class="text-[12px] sm:text-[13px] text-black/50 dark:text-white/50 truncate">{{ __('products.modal_edit_sub') }}</p>
                                </div>
                            </div>
                            <button type="button" @click="showEditModal = false"
                                class="w-8 sm:w-9 h-8 sm:h-9 rounded-full bg-black/[0.05] dark:bg-white/[0.1] hover:bg-black/[0.1] dark:hover:bg-white/[0.15] text-black/60 dark:text-white/60 flex items-center justify-center active:scale-95 transition-all shrink-0"
                                title="{{ __('products.btn_close') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Form Content (Scrollable 12-Column Bento Grid) -->
                        <form :action="'/products/' + editProduct.slug" method="POST" enctype="multipart/form-data"
                            class="flex-1 overflow-y-auto p-5 sm:p-8 overscroll-contain sidebar-scroll flex flex-col justify-between">
                            @csrf
                            @method('PUT')

                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                                <!-- Kolom Kiri: Data Utama Produk (7 Kolom) -->
                                <div class="lg:col-span-7 space-y-5">
                                    <div
                                        class="p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-4">
                                        <h3 class="text-[14px] font-bold text-black dark:text-white tracking-tight">{{ __('products.section_basic_info') }}</h3>

                                        <div>
                                            <label
                                                class="block font-medium text-black/70 dark:text-white/70 mb-1 text-[13px]">{{ __('products.label_product_name') }} <span class="text-[#FF3B30]">*</span></label>
                                            <input type="text" name="name" x-model="editProduct.name" required
                                                class="w-full h-11 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <div>
                                                <div class="flex items-center justify-between mb-1">
                                                    <label
                                                        class="font-medium text-black/70 dark:text-white/70 text-[13px]">{{ __('products.label_output_unit') }} <span class="text-[#FF3B30]">*</span></label>
                                                    <button type="button"
                                                        @click="quickAddTarget = 'edit'; showAddUnitModal = true"
                                                        class="text-[12px] font-semibold text-[#007AFF] hover:underline flex items-center gap-0.5">
                                                        <span>{{ __('products.btn_quick_unit') }}</span>
                                                    </button>
                                                </div>
                                                <select name="output_unit_id" x-model="editProduct.output_unit_id" required
                                                    class="w-full h-11 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                                    <template x-for="u in unitList" :key="u.id">
                                                        <option :value="u.id" x-text="u.name + ' (' + u.code + ')'"
                                                            :selected="String(u.id) === String(editProduct.output_unit_id)">
                                                        </option>
                                                    </template>
                                                </select>
                                            </div>
                                            <div>
                                                <div class="flex items-center justify-between mb-1">
                                                    <label
                                                        class="font-medium text-black/70 dark:text-white/70 text-[13px]">{{ __('products.label_category') }}</label>
                                                    <button type="button"
                                                        @click="quickAddTarget = 'edit'; showAddCategoryModal = true"
                                                        class="text-[12px] font-semibold text-[#007AFF] hover:underline flex items-center gap-0.5">
                                                        <span>{{ __('products.btn_quick_category') }}</span>
                                                    </button>
                                                </div>
                                                <select name="category_id" x-model="editProduct.category_id"
                                                    class="w-full h-11 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                                    <option value="">{{ __('products.no_category') }}</option>
                                                    <template x-for="c in categoryList" :key="c.id">
                                                        <option :value="c.id" x-text="c.name"
                                                            :selected="String(c.id) === String(editProduct.category_id)">
                                                        </option>
                                                    </template>
                                                </select>
                                            </div>
                                        </div>

                                        <div>
                                            <label
                                                class="block font-medium text-black/70 dark:text-white/70 mb-1 text-[13px]">{{ __('products.label_barcode_sku') }}</label>
                                            <div class="flex gap-2">
                                                <input type="text" name="sku" x-model="editProduct.sku"
                                                    inputmode="numeric" autocomplete="off" placeholder="8991234567890"
                                                    class="min-w-0 flex-1 h-11 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                                <button type="button" @click="requestProductScanner('edit')"
                                                    class="shrink-0 h-11 px-3.5 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] hover:bg-[#007AFF]/15 flex items-center justify-center transition active:scale-95 min-w-[44px]"
                                                    title="{{ __('products.scan_camera_title') }}">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                        stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>

                                        <div>
                                            <label
                                                class="block font-medium text-black/70 dark:text-white/70 mb-1 text-[13px]">{{ __('products.label_description') }}</label>
                                            <textarea name="description" rows="3" x-model="editProduct.description"
                                                class="w-full bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] p-3 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition"></textarea>
                                        </div>
                                    </div>

                                    <!-- Bento Box: Paket Kombo / Bundling Produk -->
                                    <div class="p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-4">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <span class="w-7 h-7 rounded-[8px] bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                                                        </svg>
                                                    </span>
                                                    <h3 class="text-[14px] font-bold text-black dark:text-white tracking-tight">{{ __('products.section_bundle_title') }}</h3>
                                                </div>
                                                <p class="text-[12px] text-black/50 dark:text-white/50 mt-1">{{ __('products.section_bundle_sub') }}</p>
                                            </div>
                                            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                                <input type="hidden" name="is_bundle" value="0">
                                                <input type="checkbox" name="is_bundle" value="1" x-model="editProduct.is_bundle" class="sr-only peer">
                                                <div class="w-11 h-6 bg-black/10 peer-focus:outline-none rounded-full peer dark:bg-white/10 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-amber-500"></div>
                                            </label>
                                        </div>

                                        <!-- Active Bundle Config Section -->
                                        <div x-show="editProduct.is_bundle" x-cloak class="space-y-3 pt-3 border-t border-black/[0.05] dark:border-white/[0.08]">
                                            <div class="flex items-center justify-between">
                                                <span class="text-[12px] font-semibold text-black/70 dark:text-white/70">{{ __('products.bundle_items_list') }}</span>
                                                <button type="button" @click="addBundleItem('edit')" class="h-8 px-2.5 rounded-[8px] bg-amber-500/10 text-amber-600 dark:text-amber-400 hover:bg-amber-500/20 text-[12px] font-semibold flex items-center gap-1 transition min-w-[44px]">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                                    <span>{{ __('products.btn_add_bundle_product') }}</span>
                                                </button>
                                            </div>

                                            <template x-if="!editProduct.bundle_items || editProduct.bundle_items.length === 0">
                                                <div class="p-4 rounded-[12px] border border-dashed border-black/15 dark:border-white/15 text-center text-[12px] text-black/45 dark:text-white/45">
                                                    {{ __('products.bundle_empty') }}
                                                </div>
                                            </template>

                                            <div class="space-y-2">
                                                <template x-for="(item, idx) in editProduct.bundle_items" :key="idx">
                                                    <div class="p-3 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] flex items-center gap-2.5">
                                                        <div class="flex-1 min-w-0">
                                                            <select :name="'bundle_items[' + idx + '][child_product_id]'" x-model="item.child_product_id" required class="w-full h-9 px-2.5 bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] rounded-[8px] text-[13px] text-black dark:text-white focus:ring-1 focus:ring-amber-500">
                                                                <option value="">{{ __('products.placeholder_select_child_product') }}</option>
                                                                <template x-for="p in allProductsList" :key="p.id">
                                                                    <option :value="p.id" :disabled="p.id === String(editProduct.id)" x-text="p.name + ' (Rp ' + Number(p.price).toLocaleString('id-ID') + ')'"></option>
                                                                </template>
                                                            </select>
                                                        </div>
                                                        <div class="w-24 shrink-0">
                                                            <div class="relative">
                                                                <input type="number" min="0.01" step="any" :name="'bundle_items[' + idx + '][quantity]'" x-model="item.quantity" placeholder="Qty" required class="w-full h-9 px-2.5 bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] rounded-[8px] text-[13px] text-black dark:text-white tabular-nums font-semibold focus:ring-1 focus:ring-amber-500 text-center">
                                                            </div>
                                                        </div>
                                                        <button type="button" @click="removeBundleItem('edit', idx)" class="w-9 h-9 rounded-[8px] text-[#FF3B30] hover:bg-[#FF3B30]/10 flex items-center justify-center shrink-0 transition" title="{{ __('products.btn_delete') }}">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15" /></svg>
                                                        </button>
                                                    </div>
                                                </template>
                                            </div>

                                            <div x-show="editProduct.bundle_items && editProduct.bundle_items.length > 0" class="p-3 rounded-[12px] bg-amber-500/10 border border-amber-500/20 text-[12px] space-y-1">
                                                <div class="flex justify-between text-black/70 dark:text-white/70">
                                                    <span>{{ __('products.bundle_est_cost') }}</span>
                                                    <span class="font-bold tabular-nums text-black dark:text-white" x-text="'Rp ' + getEstimatedBundleCost(editProduct.bundle_items).toLocaleString('id-ID')"></span>
                                                </div>
                                                <div class="flex justify-between text-black/70 dark:text-white/70">
                                                    <span>{{ __('products.bundle_est_normal_value') }}</span>
                                                    <span class="line-through tabular-nums text-black/50 dark:text-white/50" x-text="'Rp ' + getEstimatedBundleValue(editProduct.bundle_items).toLocaleString('id-ID')"></span>
                                                </div>
                                                <p class="text-[11px] text-amber-700 dark:text-amber-300 pt-1 border-t border-amber-500/20">
                                                    💡 <em>{{ __('products.bundle_bottleneck_tip') }}</em>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Kolom Kanan: Finansial, Media & Saluran (5 Kolom) -->
                                <div class="lg:col-span-5 space-y-5">
                                    <!-- Bento Box 1: Harga & Biaya -->
                                    <div
                                        class="p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3.5">
                                        <h3 class="text-[14px] font-bold text-black dark:text-white tracking-tight">{{ __('products.section_pricing_title') }}</h3>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            <div>
                                                <label
                                                    class="block font-medium text-black/70 dark:text-white/70 mb-1 text-[12px]">{{ __('products.label_base_cost') }}</label>
                                                <div class="relative">
                                                    <span
                                                        class="absolute left-3 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 font-medium text-[13px]">{{ $business->currency_symbol }}</span>
                                                    <input type="number" step="any" name="base_cost"
                                                        x-model="editProduct.base_cost"
                                                        class="w-full h-10 pl-9 pr-3 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[10px] text-[16px] sm:text-[14px] text-black dark:text-white tabular-nums font-semibold focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                                                </div>
                                            </div>
                                            <div>
                                                <label
                                                    class="block font-medium text-[#34C759] dark:text-[#30D158] mb-1 text-[12px]">{{ __('products.label_selling_price') }}</label>
                                                <div class="relative">
                                                    <span
                                                        class="absolute left-3 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 font-medium text-[13px]">{{ $business->currency_symbol }}</span>
                                                    <input type="number" step="any" name="selling_price"
                                                        x-model="editProduct.selling_price"
                                                        class="w-full h-10 pl-9 pr-3 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[10px] text-[16px] sm:text-[14px] text-black dark:text-white tabular-nums font-bold focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                                                </div>
                                            </div>
                                        </div>

                                        <div>
                                            <label
                                                class="block font-medium text-black/70 dark:text-white/70 mb-1 text-[12px]">{{ __('products.label_min_stock') }}</label>
                                            <input type="number" step="any" name="min_stock"
                                                x-model="editProduct.min_stock"
                                                class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[10px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                                        </div>
                                    </div>

                                    @if ($business && ($business->isFoodIndustry() || $business->hasDineInFeature()))
                                    <!-- Bento Box: Multi-Harga Saluran (F&B / Online) -->
                                    <div class="p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3.5">
                                        <div class="flex items-center justify-between">
                                            <h3 class="text-[14px] font-bold text-black dark:text-white tracking-tight">{{ __('products.section_channel_prices_title') }}</h3>
                                            <span class="text-[11px] font-medium text-black/40 dark:text-white/40">{{ __('products.section_channel_prices_badge') }}</span>
                                        </div>
                                        <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('products.section_channel_prices_desc') }}</p>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-[11px] font-medium text-black/70 dark:text-white/70 mb-1">{{ __('products.label_dine_in') }}</label>
                                                <div class="relative">
                                                    <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 text-[12px]">{{ $business->currency_symbol }}</span>
                                                    <input type="number" step="any" name="channel_prices[dine_in]" :value="editProduct.channel_prices?.['dine_in'] || ''" placeholder="{{ __('products.placeholder_standard_price') }}" class="w-full h-9 pl-8 pr-2.5 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[8px] text-[13px] text-black dark:text-white tabular-nums focus:ring-1 focus:ring-[#007AFF]">
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-[11px] font-medium text-black/70 dark:text-white/70 mb-1">{{ __('products.label_takeaway') }}</label>
                                                <div class="relative">
                                                    <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 text-[12px]">{{ $business->currency_symbol }}</span>
                                                    <input type="number" step="any" name="channel_prices[takeaway]" :value="editProduct.channel_prices?.['takeaway'] || ''" placeholder="{{ __('products.placeholder_standard_price') }}" class="w-full h-9 pl-8 pr-2.5 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[8px] text-[13px] text-black dark:text-white tabular-nums focus:ring-1 focus:ring-[#007AFF]">
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-[11px] font-medium text-[#00AA13] dark:text-[#00C819] mb-1">{{ __('products.label_gofood') }}</label>
                                                <div class="relative">
                                                    <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 text-[12px]">{{ $business->currency_symbol }}</span>
                                                    <input type="number" step="any" name="channel_prices[gofood]" :value="editProduct.channel_prices?.['gofood'] || ''" placeholder="{{ __('products.placeholder_channel_markup') }}" class="w-full h-9 pl-8 pr-2.5 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[8px] text-[13px] text-black dark:text-white tabular-nums focus:ring-1 focus:ring-[#00AA13]">
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-[11px] font-medium text-[#00B14F] dark:text-[#00D05C] mb-1">{{ __('products.label_grabfood') }}</label>
                                                <div class="relative">
                                                    <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 text-[12px]">{{ $business->currency_symbol }}</span>
                                                    <input type="number" step="any" name="channel_prices[grabfood]" :value="editProduct.channel_prices?.['grabfood'] || ''" placeholder="{{ __('products.placeholder_channel_markup') }}" class="w-full h-9 pl-8 pr-2.5 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[8px] text-[13px] text-black dark:text-white tabular-nums focus:ring-1 focus:ring-[#00B14F]">
                                                </div>
                                            </div>
                                            <div class="sm:col-span-2">
                                                <label class="block text-[11px] font-medium text-[#EE4D2D] dark:text-[#FF5B37] mb-1">{{ __('products.label_shopeefood') }}</label>
                                                <div class="relative">
                                                    <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 text-[12px]">{{ $business->currency_symbol }}</span>
                                                    <input type="number" step="any" name="channel_prices[shopeefood]" :value="editProduct.channel_prices?.['shopeefood'] || ''" placeholder="{{ __('products.placeholder_channel_markup') }}" class="w-full h-9 pl-8 pr-2.5 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[8px] text-[13px] text-black dark:text-white tabular-nums focus:ring-1 focus:ring-[#EE4D2D]">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif

                                    <!-- Bento Box 2: Foto Utama & Galeri Produk -->
                                    <div
                                        class="p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-4">
                                        <div class="flex items-center justify-between">
                                            <h3 class="text-[14px] font-bold text-black dark:text-white tracking-tight">{{ __('products.section_photos_title') }}</h3>
                                            <span class="text-[11px] font-medium text-black/40 dark:text-white/40">{{ __('products.section_photos_badge') }}</span>
                                        </div>

                                        <!-- Foto Utama (Thumbnail) -->
                                        <div class="space-y-2">
                                            <span class="text-[12px] font-semibold text-black/80 dark:text-white/80 block">{{ __('products.label_main_photo') }}</span>
                                            <div x-show="editProductPreview || editProduct.image_url"
                                                class="flex items-center gap-3.5">
                                                <div
                                                    class="w-16 h-16 rounded-[12px] overflow-hidden border border-black/10 dark:border-white/10 bg-black/[0.04] dark:bg-white/[0.06] relative shrink-0">
                                                    <img :src="editProductPreview || editProduct.image_url"
                                                        :alt="editProduct.name" class="w-full h-full object-cover">
                                                </div>
                                                <div class="text-[12px] text-black/50 dark:text-white/50">
                                                    <span x-show="editProductPreview"
                                                        class="text-[#007AFF] font-medium block">{{ __('products.photo_new_selected') }}</span>
                                                    <span x-show="!editProductPreview && editProduct.image_url"
                                                        class="block">{{ __('products.photo_current_active') }}</span>
                                                    <span class="text-[11px] text-black/40 dark:text-white/40">{{ __('products.photo_replace_tip') }}</span>
                                                </div>
                                            </div>
                                            <input type="file" name="image" @change="handleEditProductImage($event)"
                                                accept="image/jpeg,image/png,image/webp"
                                                class="w-full text-[12px] text-black/60 dark:text-white/60 file:mr-3 file:rounded-[8px] file:border-0 file:bg-black/[0.06] dark:file:bg-white/[0.08] file:px-3 file:py-2 file:text-[12px] file:font-semibold">
                                            <label x-show="editProduct.image_url"
                                                class="mt-1 flex items-center gap-2 text-[12px] text-black/60 dark:text-white/60 cursor-pointer">
                                                <input type="checkbox" name="remove_image" value="1"
                                                    class="rounded-[4px] border-black/20 text-[#FF3B30] focus:ring-[#FF3B30]">
                                                <span>{{ __('products.label_remove_current_image') }}</span>
                                            </label>
                                        </div>

                                        <!-- Galeri Foto Toko Online Eksisting & Baru -->
                                        <div class="pt-3 border-t border-black/[0.06] dark:border-white/[0.08] space-y-3">
                                            <div class="flex items-center justify-between">
                                                <span class="text-[12px] font-semibold text-black/80 dark:text-white/80">{{ __('products.label_gallery_photos') }}</span>
                                                <span class="text-[11px] text-black/40 dark:text-white/40" x-show="editProduct.images && editProduct.images.length > 0" x-text="editProduct.images.length + ' {{ __('products.gallery_count_active', ['count' => '']) }}'"></span>
                                            </div>

                                            <!-- Existing Gallery Items with Delete Toggle -->
                                            <template x-if="editProduct.images && editProduct.images.length > 0">
                                                <div class="space-y-1.5">
                                                    <span class="text-[11px] text-black/45 dark:text-white/45 block">{{ __('products.gallery_remove_tip') }}</span>
                                                    <div class="grid grid-cols-4 sm:grid-cols-5 gap-2">
                                                        <template x-for="gImg in editProduct.images" :key="gImg.id">
                                                            <div class="relative aspect-square rounded-[10px] overflow-hidden border border-black/10 dark:border-white/10 group">
                                                                <img :src="gImg.image_url" :alt="gImg.caption || 'Galeri'"
                                                                    class="w-full h-full object-cover transition"
                                                                    :class="removeGalleryIds.includes(gImg.id) ? 'opacity-25 grayscale' : ''">
                                                                <input type="checkbox" name="remove_gallery_ids[]" :value="gImg.id"
                                                                    :checked="removeGalleryIds.includes(gImg.id)" class="hidden">
                                                                <button type="button" @click="toggleRemoveExistingGallery(gImg.id)"
                                                                    class="absolute inset-0 flex items-center justify-center transition"
                                                                    :class="removeGalleryIds.includes(gImg.id) ? 'bg-[#FF3B30]/30' : 'bg-black/40 opacity-0 group-hover:opacity-100'">
                                                                    <span class="p-1 rounded-full text-white shadow" :class="removeGalleryIds.includes(gImg.id) ? 'bg-[#FF3B30]' : 'bg-black/70'">
                                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                                        </svg>
                                                                    </span>
                                                                </button>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                            </template>

                                            <!-- New Gallery Upload Previews -->
                                            <template x-if="editGalleryPreviews.length > 0">
                                                <div class="space-y-1">
                                                    <span class="text-[11px] font-medium text-[#007AFF]">{{ __('products.gallery_new_heading') }}</span>
                                                    <div class="flex items-center gap-2 overflow-x-auto py-1">
                                                        <template x-for="(prev, epIdx) in editGalleryPreviews" :key="epIdx">
                                                            <div class="w-14 h-14 rounded-[8px] overflow-hidden border border-[#007AFF]/30 shrink-0 relative bg-black/5">
                                                                <img :src="prev" alt="Gallery Preview" class="w-full h-full object-cover">
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                            </template>

                                            <input type="file" name="gallery_images[]" multiple @change="handleEditGalleryImages($event)"
                                                accept="image/jpeg,image/png,image/webp"
                                                class="w-full text-[12px] text-black/60 dark:text-white/60 file:mr-3 file:rounded-[8px] file:border-0 file:bg-[#007AFF]/10 file:text-[#007AFF] dark:file:bg-[#007AFF]/20 file:px-3 file:py-2 file:text-[12px] file:font-semibold">
                                            <p class="text-[11px] text-black/45 dark:text-white/45">{{ __('products.sub_gallery_photos') }}</p>
                                        </div>
                                    </div>

                                    <!-- Bento Box 3: Saluran Penjualan Multi-Channel -->
                                    <div
                                        class="p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3.5">
                                        <div class="flex items-center justify-between">
                                            <h3 class="text-[14px] font-bold text-black dark:text-white tracking-tight">{{ __('products.section_sales_channels') }}</h3>
                                            <span
                                                class="text-[11px] font-medium text-black/40 dark:text-white/40">{{ __('products.badge_multichannel') }}</span>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                            <label
                                                class="flex items-center gap-2 p-2.5 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] cursor-pointer hover:bg-black/[0.02] transition">
                                                <input type="hidden" name="show_in_pos" value="0">
                                                <input type="checkbox" name="show_in_pos" value="1"
                                                    x-model="editProduct.show_in_pos"
                                                    class="rounded-[4px] border-black/20 text-[#007AFF] focus:ring-[#007AFF]">
                                                <span class="text-[12px] font-semibold text-black/80 dark:text-white/80">{{ __('products.cb_pos_cashier') }}</span>
                                            </label>
                                            <label
                                                class="flex items-center gap-2 p-2.5 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] cursor-pointer hover:bg-black/[0.02] transition">
                                                <input type="hidden" name="show_in_sales_order" value="0">
                                                <input type="checkbox" name="show_in_sales_order" value="1"
                                                    x-model="editProduct.show_in_sales_order"
                                                    class="rounded-[4px] border-black/20 text-[#007AFF] focus:ring-[#007AFF]">
                                                <span class="text-[12px] font-semibold text-black/80 dark:text-white/80">{{ __('products.cb_sales_order') }}</span>
                                            </label>
                                            <label
                                                class="flex items-center gap-2 p-2.5 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] cursor-pointer hover:bg-black/[0.02] transition">
                                                <input type="hidden" name="show_in_website" value="0">
                                                <input type="checkbox" name="show_in_website" value="1"
                                                    x-model="editProduct.show_in_website"
                                                    class="rounded-[4px] border-black/20 text-[#007AFF] focus:ring-[#007AFF]">
                                                <span
                                                    class="text-[12px] font-semibold text-black/80 dark:text-white/80">{{ __('products.cb_web_storefront') }}</span>
                                            </label>
                                        </div>

                                        <div class="pt-2 border-t border-black/[0.04] dark:border-white/[0.06]">
                                            <label class="flex items-start gap-2.5 cursor-pointer">
                                                <input type="hidden" name="show_price_on_web" value="0">
                                                <input type="checkbox" name="show_price_on_web" value="1"
                                                    x-model="editProduct.show_price_on_web"
                                                    class="mt-0.5 rounded-[4px] border-black/20 text-[#007AFF] focus:ring-[#007AFF]">
                                                <div>
                                                    <span
                                                        class="text-[12px] font-semibold text-black/80 dark:text-white/80 block">{{ __('products.cb_show_price_web') }}</span>
                                                    <span class="text-[11px] text-black/45 dark:text-white/45 block">{{ __('products.sub_show_price_web') }}</span>
                                                </div>
                                            </label>
                                        </div>

                                        <!-- Pre-Order Section -->
                                        <div class="pt-2 border-t border-black/[0.04] dark:border-white/[0.06] space-y-2.5">
                                            <label class="flex items-start gap-2.5 cursor-pointer">
                                                <input type="hidden" name="is_preorder" value="0">
                                                <input type="checkbox" name="is_preorder" value="1"
                                                    x-model="editProduct.is_preorder"
                                                    class="mt-0.5 rounded-[4px] border-black/20 text-[#FF9500] focus:ring-[#FF9500]">
                                                <div>
                                                    <span
                                                        class="text-[12px] font-semibold text-black/80 dark:text-white/80 block">{{ __('products.cb_preorder_system') }}</span>
                                                    <span class="text-[11px] text-black/45 dark:text-white/45 block">{{ __('products.sub_preorder_system') }}</span>
                                                </div>
                                            </label>

                                            <div x-show="editProduct.is_preorder" x-cloak
                                                class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 p-3 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08]">
                                                <div>
                                                    <label
                                                        class="block text-[11px] font-medium text-black/60 dark:text-white/60 mb-1">{{ __('products.label_po_scheme') }}</label>
                                                    <select name="preorder_mode" x-model="editProduct.preorder_mode"
                                                        class="w-full h-9 px-2.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] text-[12px] text-black dark:text-white focus:ring-1 focus:ring-[#FF9500]">
                                                        <option value="customer_schedule">{{ __('products.po_customer_schedule') }}</option>
                                                        <option value="merchant_batch">{{ __('products.po_merchant_batch') }}</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label
                                                        class="block text-[11px] font-medium text-black/60 dark:text-white/60 mb-1">{{ __('products.label_po_lead_days') }}</label>
                                                    <div class="flex items-center gap-1.5">
                                                        <input type="number" min="0" max="90"
                                                            name="preorder_lead_days" x-model="editProduct.preorder_lead_days"
                                                            class="w-16 h-9 px-2 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] text-[12px] text-black dark:text-white tabular-nums font-bold focus:ring-1 focus:ring-[#FF9500]">
                                                        <span class="text-[12px] text-black/60 dark:text-white/60">{{ __('products.unit_days') }} (H-x)</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Bento Box 4: Dimensi & Berat Pengiriman Logistik (Biteship / Ekspedisi) -->
                                        <div
                                            class="p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3.5">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-2">
                                                    <svg class="w-4 h-4 text-[#007AFF]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                    </svg>
                                                    <h3 class="text-[14px] font-bold text-black dark:text-white tracking-tight">{{ __('products.dimensions_weight_title') }}</h3>
                                                </div>
                                                <span class="text-[11px] font-medium text-black/40 dark:text-white/40">{{ __('products.logistics_badge') }}</span>
                                            </div>

                                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                                <div>
                                                    <label class="block text-[11px] font-semibold text-black/70 dark:text-white/70 mb-1">
                                                        {{ __('products.label_weight') }} <span class="text-[#FF3B30]">*</span>
                                                    </label>
                                                    <div class="relative">
                                                        <input type="number" step="1" min="1" name="weight" x-model="editProduct.weight" required
                                                            placeholder="250"
                                                            class="w-full h-10 px-3 bg-white dark:bg-[#1C1C1E] border border-black/[0.1] dark:border-white/[0.1] rounded-[10px] text-[13px] text-black dark:text-white tabular-nums font-semibold focus:ring-2 focus:ring-[#007AFF] focus:border-transparent">
                                                        <span class="absolute right-3 top-2.5 text-[11px] font-medium text-black/40 dark:text-white/40">{{ __('products.unit_gram') }}</span>
                                                    </div>
                                                    <p class="text-[10px] text-black/45 dark:text-white/45 mt-1">{{ __('products.sub_weight_required') }}</p>
                                                </div>

                                                <div>
                                                    <label class="block text-[11px] font-semibold text-black/70 dark:text-white/70 mb-1">
                                                        {{ __('products.label_length') }}
                                                    </label>
                                                    <div class="relative">
                                                        <input type="number" step="0.1" min="0" name="length" x-model="editProduct.length"
                                                            placeholder="15"
                                                            class="w-full h-10 px-3 bg-white dark:bg-[#1C1C1E] border border-black/[0.1] dark:border-white/[0.1] rounded-[10px] text-[13px] text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#007AFF] focus:border-transparent">
                                                        <span class="absolute right-3 top-2.5 text-[11px] font-medium text-black/40 dark:text-white/40">{{ __('products.unit_cm') }}</span>
                                                    </div>
                                                    <p class="text-[10px] text-black/45 dark:text-white/45 mt-1">{{ __('products.sub_volumetric_optional') }}</p>
                                                </div>

                                                <div>
                                                    <label class="block text-[11px] font-semibold text-black/70 dark:text-white/70 mb-1">
                                                        {{ __('products.label_width') }}
                                                    </label>
                                                    <div class="relative">
                                                        <input type="number" step="0.1" min="0" name="width" x-model="editProduct.width"
                                                            placeholder="10"
                                                            class="w-full h-10 px-3 bg-white dark:bg-[#1C1C1E] border border-black/[0.1] dark:border-white/[0.1] rounded-[10px] text-[13px] text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#007AFF] focus:border-transparent">
                                                        <span class="absolute right-3 top-2.5 text-[11px] font-medium text-black/40 dark:text-white/40">{{ __('products.unit_cm') }}</span>
                                                    </div>
                                                    <p class="text-[10px] text-black/45 dark:text-white/45 mt-1">{{ __('products.sub_volumetric_optional') }}</p>
                                                </div>

                                                <div>
                                                    <label class="block text-[11px] font-semibold text-black/70 dark:text-white/70 mb-1">
                                                        {{ __('products.label_height') }}
                                                    </label>
                                                    <div class="relative">
                                                        <input type="number" step="0.1" min="0" name="height" x-model="editProduct.height"
                                                            placeholder="5"
                                                            class="w-full h-10 px-3 bg-white dark:bg-[#1C1C1E] border border-black/[0.1] dark:border-white/[0.1] rounded-[10px] text-[13px] text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#007AFF] focus:border-transparent">
                                                        <span class="absolute right-3 top-2.5 text-[11px] font-medium text-black/40 dark:text-white/40">{{ __('products.unit_cm') }}</span>
                                                    </div>
                                                    <p class="text-[10px] text-black/45 dark:text-white/45 mt-1">{{ __('products.sub_volumetric_optional') }}</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Product Active Toggle -->
                                        <div class="pt-2 border-t border-black/[0.04] dark:border-white/[0.06]">
                                            <label class="flex items-center gap-2.5 cursor-pointer">
                                                <input type="hidden" name="is_active" value="0">
                                                <input type="checkbox" id="edit_is_active" name="is_active" value="1"
                                                    x-model="editProduct.is_active"
                                                    class="rounded-[4px] border-black/20 text-[#34C759] focus:ring-[#34C759]">
                                                <span class="text-black/80 dark:text-white/80 font-semibold text-[13px]">{{ __('products.channel_status_title') }}</span>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Bento Box 5: Integrasi Marketplace Omnichannel & Taxonomy Guardrail -->
                                    <div class="p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-4">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="space-y-0.5">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <i data-lucide="shopping-bag" class="w-4 h-4 text-[#007AFF]"></i>
                                                    <h3 class="text-[14px] font-bold text-black dark:text-white tracking-tight">{{ __('products.section_marketplace_title') }}</h3>
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#007AFF]/10 text-[#007AFF]">{{ __('products.section_marketplace_badge') }}</span>
                                                </div>
                                                <p class="text-[11.5px] text-black/50 dark:text-white/50">
                                                    {{ __('products.section_marketplace_desc') }}
                                                </p>
                                            </div>
                                            <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-0.5" title="{{ __('products.marketplace_switch_title') }}">
                                                <input type="checkbox" x-model="editProduct.is_marketplace_enabled" class="sr-only peer">
                                                <div class="w-11 h-6 bg-black/15 peer-focus:outline-none rounded-full peer dark:bg-white/20 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#007AFF]"></div>
                                            </label>
                                        </div>

                                        <!-- Hidden State: Minimal Info for POS only -->
                                        <div x-show="!editProduct.is_marketplace_enabled" class="p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/[0.04] dark:border-white/[0.04] text-[11.5px] text-black/55 dark:text-white/55 flex items-center gap-2">
                                            <i data-lucide="info" class="w-4 h-4 text-black/40 dark:text-white/40 shrink-0"></i>
                                            <span>{!! __('products.marketplace_local_notice_html') !!}</span>
                                        </div>

                                        <!-- Active State: Progressive Disclosure of Taxonomy & Marketplace Mapping -->
                                        <div x-show="editProduct.is_marketplace_enabled" x-cloak class="space-y-3.5 pt-1">
                                            <!-- Kategori Taksonomi Marketplace Status Banner -->
                                            <div class="p-3.5 rounded-[14px] border transition-all"
                                                :class="getInheritedMarketplaceCategory(editProduct.category_id)
                                                    ? 'bg-[#34C759]/8 border-[#34C759]/25'
                                                    : 'bg-amber-500/8 border-amber-500/25'">
                                                <div class="flex items-start gap-2.5">
                                                    <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 mt-0.5"
                                                        :class="getInheritedMarketplaceCategory(editProduct.category_id) ? 'bg-[#34C759]/15 text-[#34C759]' : 'bg-amber-500/15 text-amber-600 dark:text-amber-400'">
                                                        <i :data-lucide="getInheritedMarketplaceCategory(editProduct.category_id) ? 'check' : 'alert-triangle'" class="w-3.5 h-3.5"></i>
                                                    </div>
                                                    <div class="space-y-1 min-w-0 flex-1 text-[12px]">
                                                        <template x-if="getInheritedMarketplaceCategory(editProduct.category_id)">
                                                            <div>
                                                                <p class="font-bold text-black dark:text-white flex items-center gap-1.5 flex-wrap">
                                                                    <span>{{ __('products.marketplace_mapped_title') }}</span>
                                                                    <span class="px-2 py-0.5 rounded-full bg-[#34C759]/15 text-[#34C759] text-[11px] font-mono font-bold"
                                                                        x-text="getInheritedMarketplaceCategory(editProduct.category_id).name + ' (ID: ' + getInheritedMarketplaceCategory(editProduct.category_id).id + ')'"></span>
                                                                </p>
                                                                <p class="text-black/60 dark:text-white/60 text-[11px]">
                                                                    {{ __('products.marketplace_mapped_desc') }}
                                                                </p>
                                                            </div>
                                                        </template>
                                                        <template x-if="!getInheritedMarketplaceCategory(editProduct.category_id)">
                                                            <div>
                                                                <p class="font-bold text-black dark:text-white">
                                                                    {{ __('products.marketplace_unmapped_title') }}
                                                                </p>
                                                                <p class="text-black/60 dark:text-white/60 text-[11px]">
                                                                    {{ __('products.marketplace_unmapped_desc') }}
                                                                </p>
                                                                <div class="pt-1.5 flex items-center gap-2 flex-wrap">
                                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-[8px] bg-amber-500/15 text-amber-700 dark:text-amber-300 font-semibold text-[11px]">
                                                                        <i data-lucide="sparkles" class="w-3 h-3"></i>
                                                                        <span x-text="'{{ __('products.marketplace_ai_suggest') }} ' + (suggestMarketplaceCategory(editProduct.name, editProduct.category_id)?.name || 'Makanan & Minuman')"></span>
                                                                    </span>
                                                                    <a href="{{ route('product-categories.index') }}" target="_blank" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-0.5">
                                                                        <span>{{ __('products.btn_manage_category_master') }}</span>
                                                                        <i data-lucide="external-link" class="w-3 h-3"></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Cockpit Shortcut Card -->
                                            <div class="p-3.5 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] flex items-center justify-between gap-3 shadow-2xs">
                                                <div class="space-y-0.5 min-w-0">
                                                    <span class="text-[12px] font-bold text-black dark:text-white block truncate">{{ __('products.card_marketplace_cockpit') }}</span>
                                                    <span class="text-[11px] text-black/50 dark:text-white/50 block truncate">{{ __('products.card_marketplace_cockpit_desc') }}</span>
                                                </div>
                                                <a href="{{ route('marketplace-hub.products') }}" class="h-8 px-3 rounded-[8px] text-[12px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 transition flex items-center gap-1 shrink-0 min-w-[44px]">
                                                    <span>{{ __('products.btn_open_hub') }}</span>
                                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sticky Bottom Action Footer -->
                            <div
                                class="sticky bottom-0 -mx-5 sm:-mx-8 -mb-5 sm:-mb-8 mt-6 backdrop-blur-xl bg-white/95 dark:bg-[#1C1C1E]/95 border-t border-black/[0.06] dark:border-white/[0.08] px-5 sm:px-8 py-4 flex items-center justify-end gap-3 shrink-0">
                                <button type="button" @click="showEditModal = false"
                                    class="h-11 px-5 rounded-[12px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] transition active:scale-[0.98] min-w-[44px]">
                                    {{ __('products.btn_cancel') }}
                                </button>
                                <button type="submit"
                                    class="h-11 px-6 rounded-[12px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition shadow-sm shadow-[#007AFF]/25 min-w-[44px]">
                                    {{ __('products.btn_save_changes') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </template>
        @endif

        <!-- ===================================================== -->
        <!-- 8. SUB-MODALS: KATEGORI & SATUAN (AJAX Quick-Add)     -->
        <!-- ===================================================== -->
        <template x-teleport="body">
            <div x-show="showAddCategoryModal" x-cloak
                class="fixed inset-0 z-[210] flex items-center justify-center p-4 bg-black/60 dark:bg-black/75 backdrop-blur-md">
                <div class="w-full max-w-sm rounded-[20px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] p-6 space-y-4 shadow-2xl"
                    @click.outside="showAddCategoryModal = false">
                    <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                        <div class="flex items-center gap-2.5">
                            <div
                                class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                            </div>
                            <h3 class="text-[15px] font-bold text-black dark:text-white">{{ __('products.modal_add_category_title') }}</h3>
                        </div>
                        <button type="button" @click="showAddCategoryModal = false"
                            class="w-7 h-7 rounded-full bg-black/[0.05] dark:bg-white/[0.1] hover:bg-black/[0.1] text-black/60 dark:text-white/60 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <form @submit.prevent="submitQuickCategory()" class="space-y-3.5 text-[13px]">
                        <div>
                            <label class="block font-medium text-black/70 dark:text-white/70 mb-1">{{ __('products.label_category_name') }} <span
                                    class="text-[#FF3B30]">*</span></label>
                            <input type="text" x-model="quickCategoryName" required
                                placeholder="{{ __('products.placeholder_category_name') }}"
                                class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] sm:text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                        </div>
                        <div>
                            <label class="block font-medium text-black/70 dark:text-white/70 mb-1">{{ __('products.label_category_desc') }}</label>
                            <input type="text" x-model="quickCategoryDesc" placeholder="{{ __('products.placeholder_category_desc') }}"
                                class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] sm:text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                        </div>
                        <div class="pt-2 flex justify-end gap-2 border-t border-black/[0.06] dark:border-white/[0.08]">
                            <button type="button" @click="showAddCategoryModal = false"
                                class="h-9 px-4 rounded-[10px] text-[12px] font-medium bg-black/[0.06] dark:bg-white/[0.08] text-black/80 dark:text-white/80 min-w-[44px]">{{ __('products.btn_cancel') }}</button>
                            <button type="submit" :disabled="quickCategoryLoading"
                                class="h-9 px-4 rounded-[10px] text-[12px] font-semibold bg-[#007AFF] text-white disabled:opacity-50 flex items-center gap-1.5 shadow-sm min-w-[44px]">
                                <span x-show="quickCategoryLoading">{{ __('products.saving_ellipsis') }}</span>
                                <span x-show="!quickCategoryLoading">{{ __('products.btn_save') }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        <template x-teleport="body">
            <div x-show="showAddUnitModal" x-cloak
                class="fixed inset-0 z-[210] flex items-center justify-center p-4 bg-black/60 dark:bg-black/75 backdrop-blur-md">
                <div class="w-full max-w-sm rounded-[20px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] p-6 space-y-4 shadow-2xl"
                    @click.outside="showAddUnitModal = false">
                    <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                        <div class="flex items-center gap-2.5">
                            <div
                                class="w-8 h-8 rounded-[10px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                            </div>
                            <h3 class="text-[15px] font-bold text-black dark:text-white">{{ __('products.modal_add_unit_title') }}</h3>
                        </div>
                        <button type="button" @click="showAddUnitModal = false"
                            class="w-7 h-7 rounded-full bg-black/[0.05] dark:bg-white/[0.1] hover:bg-black/[0.1] text-black/60 dark:text-white/60 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <form @submit.prevent="submitQuickUnit()" class="space-y-3.5 text-[13px]">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-medium text-black/70 dark:text-white/70 mb-1">{{ __('products.label_unit_code') }} <span
                                        class="text-[#FF3B30]">*</span></label>
                                <input type="text" x-model="quickUnitCode" required placeholder="{{ __('products.placeholder_unit_code') }}"
                                    class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[16px] sm:text-[13px] text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#007AFF]/50">
                            </div>
                            <div>
                                <label class="block font-medium text-black/70 dark:text-white/70 mb-1">{{ __('products.label_unit_category') }} <span
                                        class="text-[#FF3B30]">*</span></label>
                                <select x-model="quickUnitCategory" required
                                    class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-2.5 text-[16px] sm:text-[12px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                                    <option value="quantity">{{ __('products.unit_cat_quantity') }}</option>
                                    <option value="weight">{{ __('products.unit_cat_weight') }}</option>
                                    <option value="volume">{{ __('products.unit_cat_volume') }}</option>
                                    <option value="custom">{{ __('products.unit_cat_custom') }}</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block font-medium text-black/70 dark:text-white/70 mb-1">{{ __('products.label_unit_name') }} <span
                                    class="text-[#FF3B30]">*</span></label>
                            <input type="text" x-model="quickUnitName" required
                                placeholder="{{ __('products.placeholder_unit_name') }}"
                                class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] sm:text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                        </div>
                        <div class="pt-2 flex justify-end gap-2 border-t border-black/[0.06] dark:border-white/[0.08]">
                            <button type="button" @click="showAddUnitModal = false"
                                class="h-9 px-4 rounded-[10px] text-[12px] font-medium bg-black/[0.06] dark:bg-white/[0.08] text-black/80 dark:text-white/80 min-w-[44px]">{{ __('products.btn_cancel') }}</button>
                            <button type="submit" :disabled="quickUnitLoading"
                                class="h-9 px-4 rounded-[10px] text-[12px] font-semibold bg-[#007AFF] text-white disabled:opacity-50 flex items-center gap-1.5 shadow-sm min-w-[44px]">
                                <span x-show="quickUnitLoading">{{ __('products.saving_ellipsis') }}</span>
                                <span x-show="!quickUnitLoading">{{ __('products.btn_save') }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        <!-- ===================================================== -->
        <!-- 9. SCANNER MODALS (Apple Frosted Glass)                -->
        <!-- ===================================================== -->
        <template x-teleport="body">
            <div x-show="showProductScannerPermission" x-cloak
                class="fixed inset-0 z-[220] flex items-center justify-center p-4 bg-black/60 dark:bg-black/75 backdrop-blur-md">
                <div
                    class="w-full max-w-sm rounded-[20px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] p-6 space-y-4 shadow-2xl">
                    <div class="flex items-start gap-3.5">
                        <div
                            class="w-11 h-11 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-[16px] font-bold text-black dark:text-white">{{ __('products.modal_scanner_permission_title') }}</h3>
                            <p class="text-[12.5px] text-black/60 dark:text-white/60 mt-1 leading-snug">{{ __('products.modal_scanner_permission_desc') }}</p>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2.5 pt-2 border-t border-black/[0.06] dark:border-white/[0.08]">
                        <button type="button" @click="showProductScannerPermission = false"
                            class="h-9 px-4 rounded-[10px] text-[13px] bg-black/[0.06] dark:bg-white/[0.08] text-black/80 dark:text-white/80 min-w-[44px]">{{ __('products.btn_cancel') }}</button>
                        <button type="button" @click="confirmProductScannerAccess()"
                            class="h-9 px-4 rounded-[10px] text-[13px] font-semibold bg-[#007AFF] text-white shadow-sm min-w-[44px]">{{ __('products.btn_allow_and_start') }}</button>
                    </div>
                </div>
            </div>
        </template>

        <template x-teleport="body">
            <div x-show="showProductScanner" x-cloak @keydown.escape.window="closeProductScanner()"
                class="fixed inset-0 z-[220] flex items-center justify-center p-4 bg-black/60 dark:bg-black/75 backdrop-blur-md">
                <div class="w-full max-w-md rounded-[20px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] p-5 space-y-4 shadow-2xl"
                    @click.outside="closeProductScanner()">
                    <div class="flex items-center justify-between">
                        <h3 class="text-[15px] font-bold text-black dark:text-white">{{ __('products.modal_scanner_title') }}</h3>
                        <button type="button" @click="closeProductScanner()"
                            class="w-7 h-7 rounded-full bg-black/[0.05] dark:bg-white/[0.1] hover:bg-black/[0.1] text-black/60 dark:text-white/60 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="relative aspect-video overflow-hidden rounded-[14px] bg-black">
                        <video x-ref="productBarcodeVideo" autoplay muted playsinline
                            class="w-full h-full object-cover"></video>
                        <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
                            <div
                                class="w-[72%] h-[42%] rounded-[12px] border-2 border-[#007AFF] shadow-[0_0_15px_rgba(0,122,255,0.4)]">
                            </div>
                        </div>
                        <div x-show="productScannerStarting"
                            class="absolute inset-0 flex items-center justify-center bg-black/60 text-[13px] text-white">
                            {{ __('products.scanner_preparing') }}
                        </div>
                    </div>
                    <div x-show="productScannerError" class="p-3 rounded-[10px] bg-[#FF3B30]/10 text-[12px] text-[#FF3B30]"
                        x-text="productScannerError"></div>
                    <div class="flex justify-end pt-1">
                        <button type="button" @click="closeProductScanner()"
                            class="h-9 px-4 rounded-[10px] text-[13px] bg-black/[0.06] dark:bg-white/[0.08] text-black/80 dark:text-white/80 font-medium min-w-[44px]">{{ __('products.btn_close') }}</button>
                    </div>
                </div>
            </div>
        </template>

        <!-- ===================================================== -->
        <!-- 11. BENTO APPLE HIG MODAL: HARGA PER CABANG           -->
        <!-- ===================================================== -->
        <template x-teleport="body">
            <div x-show="showBranchPricesModal" x-cloak
                class="fixed inset-0 z-[200] flex items-center justify-center p-0 sm:p-4 bg-black/60 dark:bg-black/75 backdrop-blur-md"
                @keydown.escape.window="showBranchPricesModal = false">
                <div class="w-full max-w-full sm:max-w-2xl rounded-t-[28px] sm:rounded-[24px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] shadow-[0_25px_60px_rgba(0,0,0,0.3)] max-h-[92vh] flex flex-col overflow-hidden"
                    @click.outside="showBranchPricesModal = false">

                    <!-- Mobile Grab Bar -->
                    <div class="w-10 h-1.5 rounded-full bg-black/20 dark:bg-white/20 mx-auto my-2.5 sm:hidden shrink-0"></div>

                    <!-- Sticky Top Header -->
                    <div
                        class="sticky top-0 z-20 backdrop-blur-xl bg-white/95 dark:bg-[#1C1C1E]/95 border-b border-black/[0.06] dark:border-white/[0.08] px-5 sm:px-7 py-4 flex items-center justify-between gap-4 shrink-0">
                        <div class="flex items-center gap-3 min-w-0">
                            <div
                                class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.25a.75.75 0 01-.75-.75V3.75a.75.75 0 01.75-.75h19.5a.75.75 0 01.75.75v16.5a.75.75 0 01-.75.75h-7.5zM3.75 6.75h16.5M3.75 10.5h16.5M3.75 14.25h6" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <h2
                                    class="text-[17px] sm:text-[19px] font-bold text-black dark:text-white tracking-tight leading-snug truncate">
                                    {{ __('products.modal_branch_title') }}
                                </h2>
                                <p class="text-[12px] sm:text-[13px] text-black/60 dark:text-white/60 truncate">
                                    <span x-text="branchProduct.name" class="font-semibold text-[#007AFF]"></span>
                                    <span class="mx-1">·</span>
                                    <span>{{ __('products.label_master_price') }} <strong>Rp <span
                                                x-text="Number(branchProduct.selling_price || 0).toLocaleString('id-ID')"></span></strong></span>
                                    <span class="mx-1">·</span>
                                    <span>{{ __('products.label_hpp') }} <strong>Rp <span
                                                x-text="Number(branchProduct.base_cost || 0).toLocaleString('id-ID')"></span></strong></span>
                                </p>
                            </div>
                        </div>
                        <button type="button" @click="showBranchPricesModal = false"
                            class="w-8 h-8 rounded-full bg-black/[0.05] dark:bg-white/[0.1] hover:bg-black/[0.1] dark:hover:bg-white/[0.15] text-black/60 dark:text-white/60 flex items-center justify-center active:scale-95 transition-all shrink-0"
                            title="{{ __('products.btn_close') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Quick Actions Toolbar -->
                    <div
                        class="px-5 sm:px-7 py-2.5 bg-black/[0.02] dark:bg-white/[0.02] border-b border-black/[0.06] dark:border-white/[0.08] flex flex-wrap items-center justify-between gap-2 shrink-0">
                        <div class="flex items-center gap-1.5 text-[12px] font-semibold text-black/60 dark:text-white/60">
                            <svg class="w-3.5 h-3.5 text-[#007AFF]" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                            </svg>
                            <span>{{ __('products.quick_actions_label') }}</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <button type="button" @click="toggleAllBranchesAvailability(true)"
                                class="h-7 px-2.5 rounded-[8px] text-[11px] font-semibold text-[#34C759] bg-[#34C759]/10 hover:bg-[#34C759]/20 active:scale-95 transition-all flex items-center gap-1 min-w-[44px]">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                                <span>{{ __('products.btn_enable_all') }}</span>
                            </button>
                            <button type="button" @click="toggleAllBranchesAvailability(false)"
                                class="h-7 px-2.5 rounded-[8px] text-[11px] font-semibold text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/20 active:scale-95 transition-all flex items-center gap-1 min-w-[44px]">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                <span>{{ __('products.btn_disable_all') }}</span>
                            </button>
                            <button type="button" @click="resetAllBranchPrices()"
                                class="h-7 px-2.5 rounded-[8px] text-[11px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/20 active:scale-95 transition-all flex items-center gap-1 min-w-[44px]">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                </svg>
                                <span>{{ __('products.btn_reset_to_master') }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Modal Body (Scrollable Bento List) -->
                    <div class="flex-1 overflow-y-auto p-5 sm:p-7 space-y-4 sidebar-scroll">
                        <!-- Loading State -->
                        <div x-show="branchPricesLoading" class="py-12 text-center text-black/50 dark:text-white/50">
                            <svg class="w-8 h-8 mx-auto animate-spin text-[#007AFF] mb-3" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <p class="text-[14px]">{{ __('products.loading_branches') }}</p>
                        </div>

                        <!-- Empty State -->
                        <div x-show="!branchPricesLoading && branchList.length === 0"
                            class="py-10 text-center text-black/50 dark:text-white/50">
                            <p class="text-[14px]">{{ __('products.empty_branches') }}</p>
                        </div>

                        <!-- List of Branches -->
                        <template x-for="(branch, index) in branchList" :key="branch.location_id">
                            <div class="p-4 sm:p-5 rounded-[18px] border transition-all space-y-3.5"
                                :class="!branch.is_available ?
                                    'opacity-70 bg-black/[0.01] dark:bg-white/[0.01] border-dashed border-[#FF3B30]/30' :
                                    (branch.use_custom ?
                                        'ring-2 ring-[#007AFF]/30 bg-[#007AFF]/[0.02] border-[#007AFF]/20 dark:border-[#007AFF]/30' :
                                        'bg-black/[0.02] dark:bg-white/[0.03] border-black/[0.06] dark:border-white/[0.08]')">
                                
                                <!-- Header Bar Cabang -->
                                <div class="flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <h3 class="text-[15px] font-bold text-black dark:text-white truncate"
                                                x-text="branch.location_name"></h3>
                                            <span
                                                class="px-2 py-0.5 rounded-[6px] text-[11px] font-semibold uppercase tracking-wider"
                                                :class="branch.location_type === 'outlet' ? 'bg-[#007AFF]/10 text-[#007AFF]' :
                                                    'bg-[#FF9500]/10 text-[#FF9500]'"
                                                x-text="branch.location_type === 'outlet' ? '{{ __('products.badge_outlet') }}' : '{{ __('products.badge_warehouse') }}'"></span>
                                        </div>
                                        <p class="text-[12px] text-black/50 dark:text-white/50"
                                            x-text="'Kode: ' + (branch.location_code || '-')"></p>
                                    </div>

                                    <!-- Switch 1: Ketersediaan di Cabang Ini -->
                                    <label class="flex items-center gap-2 cursor-pointer select-none">
                                        <input type="checkbox" x-model="branch.is_available"
                                            class="w-4 h-4 rounded text-[#34C759] border-black/20 focus:ring-[#34C759]">
                                        <span class="text-[12px] font-semibold"
                                            :class="branch.is_available ? 'text-[#34C759]' : 'text-[#FF3B30]'"
                                            x-text="branch.is_available ? '{{ __('products.status_available_pos') }}' : '{{ __('products.status_disabled_branch') }}'"></span>
                                    </label>
                                </div>

                                <!-- Jika Dinonaktifkan di Cabang Ini -->
                                <div x-show="!branch.is_available" x-cloak
                                    class="p-3 rounded-[12px] bg-[#FF3B30]/5 text-[#FF3B30] text-[12px] flex items-center gap-2 border border-[#FF3B30]/10">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                    </svg>
                                    <span>{{ __('products.branch_disabled_warning') }}</span>
                                </div>

                                <!-- Jika Tersedia di Cabang Ini -->
                                <div x-show="branch.is_available" class="space-y-3">
                                    <!-- Switch 2: Gunakan Harga Khusus -->
                                    <div class="flex items-center justify-between pt-2 border-t border-black/[0.06] dark:border-white/[0.08]">
                                        <label class="flex items-center gap-2 cursor-pointer select-none">
                                            <input type="checkbox" x-model="branch.use_custom"
                                                class="w-4 h-4 rounded text-[#007AFF] border-black/20 focus:ring-[#007AFF]">
                                            <span class="text-[12px] font-medium text-black/70 dark:text-white/70">{{ __('products.cb_use_custom_price') }}</span>
                                        </label>
                                        <span x-show="branch.use_custom" class="text-[11px] font-bold text-[#007AFF] bg-[#007AFF]/10 px-2 py-0.5 rounded-[6px]">{{ __('products.badge_custom_price') }}</span>
                                    </div>

                                    <!-- Jika Mengikuti Master -->
                                    <div x-show="!branch.use_custom"
                                        class="text-[12px] text-black/50 dark:text-white/50 bg-black/[0.02] dark:bg-white/[0.02] p-2.5 rounded-[10px] flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-[#34C759] shrink-0" fill="none" stroke="currentColor"
                                                stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                            </svg>
                                            <span>{{ __('products.msg_following_master') }}</span>
                                        </div>
                                        <strong class="text-black/80 dark:text-white/80 tabular-nums">Rp <span
                                                x-text="Number(branchProduct.selling_price || 0).toLocaleString('id-ID')"></span></strong>
                                    </div>

                                    <!-- Jika Menggunakan Harga Khusus -->
                                    <div x-show="branch.use_custom" x-cloak class="space-y-3 pt-1">
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            <div>
                                                <label
                                                    class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">
                                                    {{ __('products.label_branch_selling_price') }} <span class="text-[#FF3B30]">*</span>
                                                </label>
                                                <div class="relative">
                                                    <span
                                                        class="absolute left-3.5 top-1/2 -translate-y-1/2 text-[14px] font-semibold text-black/40 dark:text-white/40">Rp</span>
                                                    <input type="number" step="any" min="0"
                                                        x-model="branch.custom_price" placeholder="0"
                                                        class="w-full h-11 pl-10 pr-3.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-black dark:text-white text-[16px] font-semibold tabular-nums focus:ring-2 focus:ring-[#007AFF] focus:border-[#007AFF] transition-all">
                                                </div>
                                            </div>
                                            <div>
                                                <label
                                                    class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">
                                                    {{ __('products.label_branch_cost_price') }}
                                                </label>
                                                <div class="relative">
                                                    <span
                                                        class="absolute left-3.5 top-1/2 -translate-y-1/2 text-[14px] font-semibold text-black/40 dark:text-white/40">Rp</span>
                                                    <input type="number" step="any" min="0"
                                                        x-model="branch.custom_cost" placeholder="0"
                                                        class="w-full h-11 pl-10 pr-3.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-black dark:text-white text-[16px] font-semibold tabular-nums focus:ring-2 focus:ring-[#007AFF] focus:border-[#007AFF] transition-all">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Estimasi Margin Laba Cabang -->
                                        <div class="p-2.5 rounded-[10px] bg-[#007AFF]/5 border border-[#007AFF]/10 text-[12px] flex items-center justify-between text-[#007AFF]">
                                            <span class="font-medium">{{ __('products.branch_profit_margin') }}</span>
                                            <span class="font-bold tabular-nums" x-text="getBranchMargin(branch) + '%'"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Sticky Bottom Footer Actions -->
                    <div
                        class="sticky bottom-0 z-20 backdrop-blur-xl bg-white/95 dark:bg-[#1C1C1E]/95 border-t border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 flex items-center justify-end gap-3 shrink-0">
                        <button type="button" @click="showBranchPricesModal = false"
                            class="h-11 px-5 rounded-[12px] text-[14px] font-medium text-black/70 dark:text-white/70 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] transition-colors min-w-[44px]">
                            {{ __('products.btn_cancel') }}
                        </button>
                        <button type="button" @click="saveBranchPrices()"
                            :disabled="branchPricesSaving || branchPricesLoading"
                            class="h-11 px-6 rounded-[12px] text-[14px] font-bold text-white bg-[#007AFF] hover:bg-[#007AFF]/90 active:scale-95 disabled:opacity-50 transition-all flex items-center gap-2 shadow-[0_4px_12px_rgba(0,122,255,0.25)] min-w-[44px]">
                            <svg x-show="branchPricesSaving" class="w-4 h-4 animate-spin" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span x-text="branchPricesSaving ? '{{ __('products.saving_branch_settings') }}' : '{{ __('products.btn_save_branch_settings') }}'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <!-- ===================================================== -->
        <!-- 10. APPLE ALERT DIALOG (Hapus Produk)                  -->
        <!-- ===================================================== -->
        @if (\App\Support\Context::hasPermission('products.delete') || \App\Support\Context::hasPermission('products.manage'))
            <template x-teleport="body">
                <div x-show="deleteModalOpen" x-cloak
                    class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/60 dark:bg-black/75 backdrop-blur-md">
                    <div class="w-full max-w-[320px] rounded-[20px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-2xl overflow-hidden text-center shadow-2xl border border-black/[0.08] dark:border-white/[0.1]"
                        @click.outside="closeDelete()">
                        <div class="px-5 pt-6 pb-4">
                            <div
                                class="w-10 h-10 rounded-full bg-[#FF3B30]/10 text-[#FF3B30] flex items-center justify-center mx-auto mb-3">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                </svg>
                            </div>
                            <h4 class="text-[17px] font-bold text-black dark:text-white tracking-tight">{{ __('products.modal_delete_product_title') }}</h4>
                            <p class="text-[13px] text-black/60 dark:text-white/60 mt-1.5 leading-snug">
                                <span x-text="deleteTarget.name" class="font-semibold text-black dark:text-white"></span>
                                {{ __('products.modal_delete_product_desc') }}
                            </p>
                            <div
                                class="mt-3 p-2.5 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.04] text-[11px] text-black/50 dark:text-white/50 text-left flex items-start gap-1.5">
                                <svg class="w-4 h-4 text-[#007AFF] shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                    stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                                </svg>
                                <span>{{ __('products.peace_of_mind_delete_product') }}</span>
                            </div>
                        </div>
                        <div
                            class="grid grid-cols-2 border-t border-black/[0.08] dark:border-white/[0.1] text-[15px] font-medium">
                            <button type="button" @click="closeDelete()"
                                class="py-3 text-[#007AFF] border-r border-black/[0.08] dark:border-white/[0.1] active:bg-black/5 dark:active:bg-white/5 transition-colors min-w-[44px]">
                                {{ __('products.btn_cancel') }}
                            </button>
                            <button type="button" @click="submitDelete()"
                                class="py-3 text-[#FF3B30] font-semibold active:bg-black/5 dark:active:bg-white/5 transition-colors min-w-[44px]">
                                {{ __('products.btn_delete_confirm') }}
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        @endif

    </div>
@endsection
