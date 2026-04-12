<?php
require 'includes/header.php';

$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$isNew  = isset($_GET['new']);
$editMode = ($editId > 0 || $isNew);

// If edit mode, load the product data
$product = null;
$productImages = [];
if ($editId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id=?");
    $stmt->execute([$editId]);
    $product = $stmt->fetch();
    if (!$product) {
        header("Location: products.php");
        exit;
    }
    $imgStmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id=? ORDER BY sort_order ASC");
    $imgStmt->execute([$editId]);
    $productImages = $imgStmt->fetchAll();
}
?>

<style>
/* ===== LIST VIEW ===== */
td.thumb-col { width: 60px; text-align: center; }
td.thumb-col img { width: 44px; height: 44px; border-radius: 8px; object-fit: cover; }

/* ===== EDIT VIEW LAYOUT ===== */
.edit-layout {
    display: grid;
    grid-template-columns: 320px 1fr;
    gap: 0;
    min-height: calc(100vh - var(--hh) - 48px);
    align-items: start;
}

/* Left Gallery Panel */
.gallery-panel {
    position: sticky;
    top: calc(var(--hh) + 24px);
    background: var(--surface);
    border-radius: var(--r);
    border: 1px solid var(--border);
    overflow: hidden;
    max-height: calc(100vh - var(--hh) - 48px);
    display: flex;
    flex-direction: column;
}
.gallery-panel-head {
    padding: 18px 20px 14px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-shrink: 0;
}
.gallery-panel-head h4 { font-size: 14px; font-weight: 700; margin: 0; color: var(--text); }
.gallery-panel-search {
    padding: 12px 16px;
    flex-shrink: 0;
    border-bottom: 1px solid var(--border);
}
.gallery-panel-search input {
    width: 100%;
    background: var(--hover);
    border: 1px solid var(--border);
    border-radius: 8px;
    color: var(--text);
    padding: 7px 12px;
    font-size: 13px;
    outline: none;
}
.gallery-panel-search input:focus { border-color: var(--accent); }
.gallery-panel-body {
    overflow-y: auto;
    flex: 1;
    padding: 12px;
}

/* Gallery grid in panel */
.gal-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
}
.gal-item {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    border: 2px solid transparent;
    cursor: pointer;
    transition: all .15s;
    aspect-ratio: 1;
    background: var(--hover);
}
.gal-item img {
    width: 100%; height: 100%; object-fit: cover; display: block;
    transition: transform .2s;
}
.gal-item:hover img { transform: scale(1.05); }
.gal-item:hover { border-color: var(--accent); }
.gal-item.attached { border-color: var(--ok); }
.gal-item.attached::after {
    content: '\ea41'; font-family: 'boxicons'; position: absolute;
    bottom: 4px; right: 4px;
    background: var(--ok); color: #fff; border-radius: 50%;
    width: 18px; height: 18px; display: flex; align-items: center;
    justify-content: center; font-size: 12px;
}
.gal-item.is-thumb-marker { border-color: var(--warn); }
.gal-item.is-thumb-marker::before {
    content: '★'; position: absolute; top: 3px; left: 4px;
    color: var(--warn); font-size: 13px; text-shadow: 0 1px 3px rgba(0,0,0,.6);
}
.gal-panel-footer {
    padding: 12px 16px;
    border-top: 1px solid var(--border);
    flex-shrink: 0;
}

/* Right Product Form Panel */
.product-form-panel {
    background: var(--surface);
    border-radius: var(--r);
    border: 1px solid var(--border);
    margin-left: 20px;
    overflow: hidden;
}
.product-form-panel .pfp-head {
    padding: 18px 24px 14px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.product-form-panel .pfp-body {
    padding: 24px;
}
.product-form-panel .pfp-footer {
    padding: 16px 24px;
    border-top: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
}

/* Attached images order strip */
.attached-strip {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    min-height: 64px;
    padding: 8px;
    background: var(--hover);
    border-radius: 8px;
    border: 1px dashed var(--border);
}
.attach-card {
    position: relative;
    width: 60px; height: 60px;
    border-radius: 6px;
    overflow: visible;
    cursor: grab;
    flex-shrink: 0;
    transition: opacity .15s;
}
.attach-card:active { cursor: grabbing; }
.attach-card.sortable-drag { opacity: .3; }
.attach-card.sortable-ghost { opacity: .25; }
.attach-card img {
    width: 60px; height: 60px; object-fit: cover; border-radius: 6px;
    border: 2px solid transparent; display: block;
}
.attach-card.is-thumbnail img { border-color: var(--warn); }
.attach-card .ac-badge {
    position: absolute; bottom: -6px; left: 50%; transform: translateX(-50%);
    background: var(--warn); color: #fff; font-size: 8px; font-weight: 700;
    padding: 1px 5px; border-radius: 20px; white-space: nowrap; display: none;
    pointer-events: none;
}
.attach-card.is-thumbnail .ac-badge { display: block; }
.attach-card .ac-rm {
    position: absolute; top: -6px; right: -6px;
    width: 18px; height: 18px; border-radius: 50%;
    background: var(--err); color: #fff; border: none;
    display: flex; align-items: center; justify-content: center;
    font-size: 10px; cursor: pointer; padding: 0;
}
.attach-empty { width: 100%; text-align: center; color: var(--mut); font-size: 12px; padding: 8px 0; }

/* Form inputs */
.fc { background: var(--hover) !important; border: 1px solid var(--border) !important; color: var(--text) !important; border-radius: 8px !important; }
.fc:focus { border-color: var(--accent) !important; box-shadow: 0 0 0 3px var(--as) !important; }
.ql-toolbar.ql-snow { background: var(--card); border: 1px solid var(--border) !important; border-radius: 8px 8px 0 0 !important; }
.ql-container.ql-snow { background: var(--hover); border: 1px solid var(--border) !important; border-top: none !important; border-radius: 0 0 8px 8px !important; color: var(--text); min-height: 200px; }
.ql-editor { min-height: 200px; color: var(--text); }
.ql-toolbar.ql-snow .ql-stroke { stroke: var(--sub); }
.ql-toolbar.ql-snow .ql-fill { fill: var(--sub); }
.ql-toolbar.ql-snow .ql-picker { color: var(--sub); }

/* Custom Btn Editor */
#customButtonsContainer { display: flex; flex-direction: column; gap: 10px; margin-top: 2px; }
.btn-row { background: var(--hover); border: 1px solid var(--border); border-radius: 10px; padding: 12px; }
.btn-chip-group { display: flex; gap: 4px; }
.btn-chip { padding: 4px 10px; border-radius: 20px; border: 1px solid var(--border); background: transparent; color: var(--text); font-size: 11px; cursor: pointer; transition: all .12s; }
.btn-chip.active { background: var(--accent); border-color: var(--accent); color: #fff; }
.cb-color { width: 28px; height: 28px; border-radius: 6px; border: 1px solid var(--border); cursor: pointer; padding: 2px; background: transparent; }
.btn-preview-swatch { display: inline-block; padding: 5px 16px; font-size: 11px; font-weight: 600; font-family: inherit; pointer-events: none; }
.sec-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--mut); margin-bottom: 12px; }

/* Gallery panel upload btn */
.gal-upload-btn { position: relative; overflow: hidden; }
.gal-upload-btn input[type=file] { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
</style>

<?php if (!$editMode): ?>
<!-- ===== LIST VIEW ===== -->
<div class="page-header d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1>Product Catalog</h1>
        <div class="bc">Manage your digital products, details, pricing, and images.</div>
    </div>
    <a href="products.php?new" class="btn btn-primary"><i class='bx bx-plus me-1'></i> Add Product</a>
</div>

<div class="card-c">
    <div class="cb p-0">
        <div class="table-responsive">
            <table class="tbl datatable" id="productsTable" style="width:100%">
                <thead>
                    <tr>
                        <th class="thumb-col px-4">Img</th>
                        <th>Product Title</th>
                        <th>Price</th>
                        <th>Promo</th>
                        <th class="text-end px-4">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <!-- AJAX populated -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>
$(document).ready(function() {
    if (!$.fn.DataTable.isDataTable('#productsTable')) {
        $('#productsTable').DataTable({
            pageLength: 10,
            language: { search: "Cari Product:", lengthMenu: "Tampil _MENU_ data" },
            ajax: { url: 'api_products.php?action=get_all', dataSrc: 'data' },
            order: [[1, 'asc']],
            columns: [
                {
                    data: 'first_image',
                    className: 'thumb-col px-4',
                    render: d => d
                        ? `<img src="../assets/uploads/${d}" style="width:44px;height:44px;border-radius:8px;object-fit:cover;">`
                        : `<div style="width:44px;height:44px;border-radius:8px;background:var(--hover);display:flex;align-items:center;justify-content:center;color:var(--mut);"><i class='bx bx-image-alt fs-5'></i></div>`
                },
                { data: 'title', render: d => `<span class="fw-semibold">${d}</span>` },
                { data: 'price', render: d => `Rp ${Number(d).toLocaleString('id-ID')}` },
                { data: 'promo_price', render: d => (d && d > 0) ? `<span class="bd bd-ok">Rp ${Number(d).toLocaleString('id-ID')}</span>` : '<span class="text-muted">-</span>' },
                {
                    data: 'id', className: 'text-end px-4', orderable: false,
                    render: id => `
                        <a href="products.php?edit=${id}" class="btn btn-sm btn-outline-secondary me-1" title="Edit"><i class='bx bx-edit'></i></a>
                        <button class="btn btn-sm" style="border:1px solid var(--err);color:var(--err);" onclick="deleteProduct(${id})" title="Delete"><i class='bx bx-trash'></i></button>
                    `
                }
            ]
        });
    }
});

function deleteProduct(id) {
    Swal.fire({
        title: 'Delete this product?',
        text: "This action is permanent and cannot be undone.",
        icon: 'warning', showCancelButton: true,
        confirmButtonColor: 'var(--err)', cancelButtonColor: 'var(--mut)',
        confirmButtonText: 'Yes, delete', background: 'var(--surface)', color: 'var(--text)'
    }).then(r => {
        if (r.isConfirmed) {
            $.post('api_products.php', { action: 'delete', id }, json => {
                if (json.status === 'success') {
                    Toast.fire({ icon: 'success', title: 'Product deleted' });
                    $('#productsTable').DataTable().ajax.reload(null, false);
                }
            });
        }
    });
}
</script>

<?php else: ?>
<!-- ===== EDIT / CREATE VIEW ===== -->
<div class="page-header d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center gap-3">
        <a href="products.php" class="btn btn-outline-secondary btn-sm"><i class='bx bx-arrow-back'></i></a>
        <div>
            <h1><?= $editId ? 'Edit Product' : 'Add New Product' ?></h1>
            <div class="bc"><?= $editId ? 'ID #'.$editId.' — '.(htmlspecialchars($product['title'] ?? '')) : 'Fill in product details and select images from the gallery.' ?></div>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="products.php" class="btn btn-outline-secondary">Discard</a>
        <button type="button" class="btn btn-primary" id="btnSaveProd" onclick="saveProduct()"><i class='bx bx-save me-1'></i> Save Product</button>
    </div>
</div>

<div class="edit-layout">
    <!-- LEFT: Gallery Panel -->
    <div class="gallery-panel">
        <div class="gallery-panel-head">
            <h4><i class='bx bx-images me-2' style="color:var(--accent);"></i>Media Gallery</h4>
            <button class="btn btn-sm btn-outline-secondary gal-upload-btn" title="Upload new images">
                <i class='bx bx-upload'></i> Upload
                <input type="file" id="galUploadInput" accept="image/*" multiple onchange="handleGalleryUpload(this.files)">
            </button>
        </div>
        <div class="gallery-panel-search">
            <input type="text" id="galSearchInput" placeholder="Search images..." oninput="filterGallery(this.value)">
        </div>
        <div class="gallery-panel-body">
            <div class="gal-grid" id="galGrid">
                <div style="grid-column:1/-1;text-align:center;padding:20px;color:var(--mut);"><i class='bx bx-loader-alt bx-spin fs-3'></i></div>
            </div>
        </div>
        <div class="gal-panel-footer">
            <div class="text-muted" style="font-size:12px;"><span id="galCount">0</span> images in gallery · <span id="attachedCount">0</span> attached</div>
        </div>
    </div>

    <!-- RIGHT: Product Form -->
    <div class="product-form-panel">
        <div class="pfp-head">
            <div class="sec-label mb-0" style="margin:0;">Product Details</div>
        </div>
        <div class="pfp-body">
            <form id="productForm">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" id="prod_id" value="<?= $editId ?>">
                <input type="hidden" name="gallery_images" id="prod_gallery_images" value="[]">
                <input type="hidden" name="custom_buttons" id="prod_custom_buttons" value="<?= htmlspecialchars($product['custom_buttons'] ?? '[]') ?>">

                <div class="row g-4">
                    <!-- Left column of form -->
                    <div class="col-lg-8">
                        <!-- Title -->
                        <div class="mb-3">
                            <label class="form-label sec-label">Product Title</label>
                            <input type="text" name="title" id="prod_title" class="form-control fc" value="<?= htmlspecialchars($product['title'] ?? '') ?>" placeholder="Enter product title..." required>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label class="form-label sec-label">Description</label>
                            <div id="editor-container"></div>
                            <input type="hidden" name="description" id="prod_desc">
                        </div>

                        <!-- Attached images for description insert -->  
                        <div class="mb-3" id="insertImgGroup" style="<?= empty($productImages) ? 'display:none' : '' ?>">
                            <label class="form-label sec-label">Insert Images into Description</label>
                            <div id="insertImageGrid" class="d-flex flex-wrap gap-2"></div>
                        </div>

                        <!-- Demo URL -->
                        <div class="mb-3">
                            <label class="form-label sec-label">Demo URL</label>
                            <input type="url" name="demo_link" id="prod_link" class="form-control fc" value="<?= htmlspecialchars($product['demo_link'] ?? '') ?>" placeholder="https://...">
                        </div>

                        <!-- YouTube URL -->
                        <div class="mb-3">
                            <label class="form-label sec-label">YouTube URL <small class="fw-normal" style="color:var(--mut);text-transform:none;">(Optional embed)</small></label>
                            <input type="url" name="youtube_url" id="prod_youtube" class="form-control fc" value="<?= htmlspecialchars($product['youtube_url'] ?? '') ?>" placeholder="https://youtube.com/watch?v=...">
                        </div>
                    </div>

                    <!-- Right column of form -->
                    <div class="col-lg-4">
                        <!-- Pricing -->
                        <div class="card-c mb-4" style="background:var(--card);">
                            <div class="cb">
                                <div class="sec-label">Pricing</div>
                                <div class="mb-3">
                                    <label class="form-label" style="font-size:12px;color:var(--sub);">Base Price (Rp)</label>
                                    <input type="number" name="price" id="prod_price" class="form-control fc" value="<?= $product['price'] ?? '' ?>" required>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label" style="font-size:12px;color:var(--sub);">Promo Price <small style="color:var(--mut);">(Optional)</small></label>
                                    <input type="number" name="promo_price" id="prod_promo" class="form-control fc" value="<?= $product['promo_price'] ?? '' ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Attached images strip -->
                        <div class="card-c" style="background:var(--card);">
                            <div class="cb">
                                <div class="sec-label">Product Images</div>
                                <p style="font-size:11px;color:var(--mut);margin-bottom:10px;">Click an image in the gallery to attach. Drag to reorder. ★ = Thumbnail.</p>
                                <div id="existingImages" class="attached-strip">
                                    <div class="attach-empty">No images attached yet</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr style="border-color:var(--border);margin:28px 0;">

                <!-- Custom Buttons -->
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="sec-label mb-0">Custom Action Buttons</div>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addButtonRow()"><i class='bx bx-plus'></i> Add Button</button>
                    </div>
                    <div id="customButtonsContainer"></div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Image Insert Picker Panel -->
<div id="imgPickerPanel" style="display:none;position:fixed;z-index:1060;background:var(--surface);border:1px solid var(--border);border-radius:12px;box-shadow:0 12px 40px rgba(0,0,0,.55);padding:16px;min-width:260px;font-family:'Plus Jakarta Sans',sans-serif;">
    <img id="ipPreview" style="width:100%;height:100px;object-fit:contain;background:var(--hover);border-radius:8px;margin-bottom:12px;" src="" alt="">
    <div style="font-size:11px;font-weight:700;text-transform:uppercase;color:var(--mut);margin-bottom:6px;">Size</div>
    <div class="ip-btn-group d-flex gap-1 mb-3" id="ipSizeGroup">
        <button class="btn-chip" data-val="25%">25%</button>
        <button class="btn-chip active" data-val="50%">50%</button>
        <button class="btn-chip" data-val="75%">75%</button>
        <button class="btn-chip" data-val="100%">Full</button>
    </div>
    <div style="font-size:11px;font-weight:700;text-transform:uppercase;color:var(--mut);margin-bottom:6px;">Alignment</div>
    <div class="d-flex gap-1 mb-3" id="ipAlignGroup">
        <button class="btn-chip active" data-val="center">Center</button>
        <button class="btn-chip" data-val="left">Left</button>
        <button class="btn-chip" data-val="right">Right</button>
        <button class="btn-chip" data-val="inline">Inline</button>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary btn-sm w-100" onclick="closeImgPicker()">Cancel</button>
        <button class="btn btn-primary btn-sm w-100" onclick="confirmInsertImage()">Insert</button>
    </div>
</div>

<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>
// ===== QUILL EDITOR =====
var quill = new Quill('#editor-container', {
    theme: 'snow',
    placeholder: 'Write complete product description here...',
    modules: {
        toolbar: {
            container: [
                ['bold','italic','underline','strike'],
                [{'list':'ordered'},{'list':'bullet'}],
                [{'color':[]},{'background':[]}],
                ['link','image','clean']
            ],
            handlers: {
                image: function() {
                    var url = prompt('Insert image URL:');
                    if(url) { var r = quill.getSelection(); quill.insertEmbed(r ? r.index : 0, 'image', url); }
                }
            }
        }
    }
});

<?php if ($product && $product['description']): ?>
quill.root.innerHTML = <?= json_encode($product['description']) ?>;
<?php endif; ?>

// ===== GALLERY STATE =====
let allGalleryImgs = [];
// attached: array of {name, thumb}
let attachedImages = <?= json_encode(array_map(fn($i) => ['name' => $i['image_path'], 'thumb' => (int)$i['is_thumbnail']], $productImages)) ?>;
let _sortable = null;

$(document).ready(function() {
    loadGallery();
    renderAttachedStrip();
    initCustomButtons();

    // picker btn group clicks
    $('#ipSizeGroup, #ipAlignGroup').on('click', '.btn-chip', function() {
        $(this).closest('div').find('.btn-chip').removeClass('active');
        $(this).addClass('active');
    });
});

// ===== LOAD GALLERY =====
function loadGallery() {
    $.get('api_gallery.php?action=get_all', function(json) {
        if (json.status === 'success') {
            allGalleryImgs = json.data;
            renderGallery(allGalleryImgs);
            $('#galCount').text(json.data.length);
        }
    });
}

function renderGallery(imgs) {
    let $g = $('#galGrid');
    $g.empty();
    if (!imgs.length) {
        $g.html('<div style="grid-column:1/-1;text-align:center;padding:20px;color:var(--mut);font-size:12px;">No images uploaded yet.</div>');
        return;
    }
    imgs.forEach(img => {
        let isAttached = attachedImages.some(a => a.name === img.name);
        let isThumb = attachedImages.some(a => a.name === img.name && a.thumb);
        let cls = isAttached ? (isThumb ? 'gal-item attached is-thumb-marker' : 'gal-item attached') : 'gal-item';
        $g.append(`
            <div class="${cls}" data-name="${img.name}" title="${img.name}" onclick="toggleAttach('${img.name}')">
                <img src="${img.url}" loading="lazy">
            </div>
        `);
    });
    updateAttachedCount();
}

function filterGallery(q) {
    if (!q.trim()) { renderGallery(allGalleryImgs); return; }
    renderGallery(allGalleryImgs.filter(i => i.name.toLowerCase().includes(q.toLowerCase())));
}

// ===== TOGGLE ATTACH =====
function toggleAttach(name) {
    let idx = attachedImages.findIndex(a => a.name === name);
    if (idx >= 0) {
        // Detach
        attachedImages.splice(idx, 1);
    } else {
        // Attach — first attached auto-thumb
        let isFirst = attachedImages.length === 0;
        attachedImages.push({ name, thumb: isFirst ? 1 : 0 });
    }
    renderAttachedStrip();
    refreshGalleryMarkers();
    syncJSON();
}

// ===== ATTACHED STRIP =====
function renderAttachedStrip() {
    let $s = $('#existingImages');
    $s.empty();
    if (!attachedImages.length) {
        $s.html('<div class="attach-empty">Click an image on the left to attach it</div>');
        updateAttachedCount();
        renderInsertGrid();
        return;
    }
    attachedImages.forEach((img, idx) => {
        let cls = img.thumb ? 'attach-card is-thumbnail' : 'attach-card';
        $s.append(`
            <div class="${cls}" data-name="${img.name}">
                <img src="../assets/uploads/${img.name}" title="Click ★ to set thumbnail">
                <div class="ac-badge">★ Thumb</div>
                <button type="button" class="ac-rm" onclick="detachImg('${img.name}')" title="Remove"><i class='bx bx-x'></i></button>
            </div>
        `);
    });

    updateAttachedCount();
    renderInsertGrid();

    // Sortable
    if (_sortable) _sortable.destroy();
    _sortable = Sortable.create($s[0], {
        animation: 150,
        filter: '.ac-rm',
        ghostClass: 'sortable-ghost',
        dragClass: 'sortable-drag',
        onEnd: function() {
            let newOrder = [];
            $s.find('.attach-card').each(function() { newOrder.push($(this).data('name')); });
            // Re-sync attachedImages array order
            let map = {};
            attachedImages.forEach(a => { map[a.name] = a; });
            attachedImages = newOrder.map(n => map[n]).filter(Boolean);
            // First one is auto-thumbnail if no thumb set
            ensureThumb();
            renderAttachedStrip();
            refreshGalleryMarkers();
            syncJSON();
        }
    });

    // Click to set thumb
    $s.find('.attach-card').on('click', function(e) {
        if ($(e.target).closest('.ac-rm').length) return;
        let name = $(this).data('name');
        attachedImages.forEach(a => { a.thumb = (a.name === name) ? 1 : 0; });
        renderAttachedStrip();
        refreshGalleryMarkers();
        syncJSON();
    });
}

function detachImg(name) {
    attachedImages = attachedImages.filter(a => a.name !== name);
    ensureThumb();
    renderAttachedStrip();
    refreshGalleryMarkers();
    syncJSON();
}

function ensureThumb() {
    if (attachedImages.length && !attachedImages.some(a => a.thumb)) {
        attachedImages[0].thumb = 1;
    }
}

function refreshGalleryMarkers() {
    $('#galGrid .gal-item').each(function() {
        let name = $(this).data('name');
        let isAttached = attachedImages.some(a => a.name === name);
        let isThumb = attachedImages.some(a => a.name === name && a.thumb);
        $(this).removeClass('attached is-thumb-marker');
        if (isAttached) {
            $(this).addClass('attached');
            if (isThumb) $(this).addClass('is-thumb-marker');
        }
    });
    updateAttachedCount();
}

function updateAttachedCount() {
    $('#attachedCount').text(attachedImages.length);
}

function renderInsertGrid() {
    let $ig = $('#insertImageGrid');
    $ig.empty();
    if (!attachedImages.length) { $('#insertImgGroup').hide(); return; }
    $('#insertImgGroup').show();
    attachedImages.forEach(img => {
        $ig.append(`
            <div style="width:52px;height:52px;border-radius:6px;overflow:hidden;cursor:pointer;border:1px solid var(--border);position:relative;"
                 onclick="openImgPicker('../assets/uploads/${img.name}', this)"
                 onmouseenter="$(this).find('.ins-ov').css({opacity:1})"
                 onmouseleave="$(this).find('.ins-ov').css({opacity:0})">
                <img src="../assets/uploads/${img.name}" style="width:100%;height:100%;object-fit:cover;">
                <div class="ins-ov" style="position:absolute;inset:0;background:rgba(0,0,0,.5);display:flex;align-items:center;justify-content:center;font-size:20px;opacity:0;transition:.15s;color:#fff;"><i class='bx bx-plus'></i></div>
            </div>
        `);
    });
}

function syncJSON() {
    $('#prod_gallery_images').val(JSON.stringify(attachedImages));
}

// ===== GALLERY UPLOAD =====
function handleGalleryUpload(files) {
    if (!files || !files.length) return;
    let fd = new FormData();
    fd.append('action', 'upload');
    for (let i = 0; i < files.length; i++) fd.append('files[]', files[i]);
    $.ajax({
        url: 'api_gallery.php', method: 'POST', data: fd,
        processData: false, contentType: false,
        success: json => {
            if (json.status === 'success') {
                Toast.fire({ icon: 'success', title: json.message });
                loadGallery();
            } else {
                Toast.fire({ icon: 'error', title: json.message });
            }
        }
    });
    // Reset file input so same file can be re-uploaded
    document.getElementById('galUploadInput').value = '';
}

// ===== SAVE PRODUCT =====
function saveProduct() {
    $('#prod_desc').val(quill.root.innerHTML);
    syncButtons();
    syncJSON();

    let fd = new FormData($('#productForm')[0]);
    let $btn = $('#btnSaveProd');
    $btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Saving...');

    $.ajax({
        url: 'api_products.php', method: 'POST', data: fd,
        processData: false, contentType: false,
        success: function(json) {
            $btn.prop('disabled', false).html('<i class="bx bx-save me-1"></i> Save Product');
            if (json.status === 'success') {
                Toast.fire({ icon: 'success', title: 'Product saved!' });
                // Redirect back to list after short delay
                setTimeout(() => { window.location.href = 'products.php'; }, 1200);
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: json.message, background: 'var(--surface)', color: 'var(--text)' });
            }
        },
        error: function() {
            $btn.prop('disabled', false).html('<i class="bx bx-save me-1"></i> Save Product');
        }
    });
}

// ===== IMAGE INSERT PICKER =====
let _ipUrl = '';
function openImgPicker(url, el) {
    _ipUrl = url;
    $('#ipPreview').attr('src', url);
    let r = el.getBoundingClientRect();
    let left = r.left + window.scrollX;
    let top = r.bottom + window.scrollY + 8;
    if (left + 270 > window.innerWidth) left = window.innerWidth - 280;
    $('#imgPickerPanel').css({ display:'block', left, top });
    setTimeout(() => { $(document).on('click.ip', function(e) { if (!$(e.target).closest('#imgPickerPanel').length) closeImgPicker(); }); }, 20);
}
function closeImgPicker() {
    $('#imgPickerPanel').hide();
    $(document).off('click.ip');
}
function confirmInsertImage() {
    let size = $('#ipSizeGroup .active').data('val') || '50%';
    let align = $('#ipAlignGroup .active').data('val') || 'center';
    let style = `max-width:${size};height:auto;`;
    if (align === 'left') style += 'float:left;margin:0 14px 10px 0;';
    else if (align === 'right') style += 'float:right;margin:0 0 10px 14px;';
    else if (align === 'center') style += 'display:block;margin:10px auto;';
    else style += 'display:inline;margin:0 4px;';
    let html = `<img src="${_ipUrl}" style="${style}width:${size};">`;
    let range = quill.getSelection() || { index: quill.getLength() };
    quill.clipboard.dangerouslyPasteHTML(range.index, html, 'user');
    closeImgPicker();
}

// ===== CUSTOM BUTTONS =====
function initCustomButtons() {
    let raw = $('#prod_custom_buttons').val();
    try { let arr = JSON.parse(raw); if (Array.isArray(arr)) arr.forEach(b => addButtonRow(b.label, b.url, b.bg, b.color, b.shape, b.variant)); } catch(e) {}
}

function addButtonRow(label='', url='', bg='#3b82f6', color='#ffffff', shape='rounded', variant='solid') {
    let shapes = [{v:'pill',l:'Pill'},{v:'rounded',l:'Rounded'},{v:'square',l:'Square'}];
    let variants = [{v:'solid',l:'Solid'},{v:'outline',l:'Outline'},{v:'ghost',l:'Ghost'}];
    let sc = shapes.map(s => `<button type="button" class="btn-chip${shape===s.v?' active':''}" data-val="${s.v}">${s.l}</button>`).join('');
    let vc = variants.map(v => `<button type="button" class="btn-chip${variant===v.v?' active':''}" data-val="${v.v}">${v.l}</button>`).join('');
    let html = `
        <div class="btn-row">
            <div class="row g-2 align-items-center mb-2">
                <div class="col-sm-5"><input type="text" class="form-control fc cb-label" placeholder="Button Label" value="${label}"></div>
                <div class="col-sm-6"><input type="text" class="form-control fc cb-url" placeholder="https://..." value="${url}"></div>
                <div class="col-sm-1 text-end"><button type="button" class="btn btn-sm btn-outline-danger" onclick="$(this).closest('.btn-row').remove(); syncButtons()"><i class='bx bx-x'></i></button></div>
            </div>
            <div class="d-flex flex-wrap gap-3 align-items-center">
                <div class="d-flex align-items-center gap-2"><span style="font-size:10px;font-weight:700;text-transform:uppercase;color:var(--mut);">Shape</span><div class="btn-chip-group cb-shape-group">${sc}</div></div>
                <div class="d-flex align-items-center gap-2"><span style="font-size:10px;font-weight:700;text-transform:uppercase;color:var(--mut);">Style</span><div class="btn-chip-group cb-variant-group">${vc}</div></div>
                <div class="d-flex align-items-center gap-2"><span style="font-size:10px;font-weight:700;text-transform:uppercase;color:var(--mut);">BG</span><input type="color" class="cb-color cb-bg" value="${bg}"></div>
                <div class="d-flex align-items-center gap-2"><span style="font-size:10px;font-weight:700;text-transform:uppercase;color:var(--mut);">Text</span><input type="color" class="cb-color cb-text" value="${color}"></div>
                <div class="ms-auto"><span class="btn-preview-swatch">Preview</span></div>
            </div>
        </div>`;
    let $row = $(html);
    $('#customButtonsContainer').append($row);
    $row.find('.btn-chip').on('click', function() { $(this).siblings().removeClass('active'); $(this).addClass('active'); updateBtnPreview($row); syncButtons(); });
    $row.find('input').on('input', function() { updateBtnPreview($row); syncButtons(); });
    updateBtnPreview($row);
    syncButtons();
}

function updateBtnPreview($row) {
    let lbl = $row.find('.cb-label').val() || 'Label';
    let bg = $row.find('.cb-bg').val();
    let clr = $row.find('.cb-text').val();
    let shape = $row.find('.cb-shape-group .active').data('val');
    let variant = $row.find('.cb-variant-group .active').data('val');
    let radius = shape==='pill' ? '50px' : shape==='rounded' ? '8px' : '4px';
    let $sw = $row.find('.btn-preview-swatch');
    if (variant==='solid') $sw.css({background:bg, color:clr, border:`1px solid ${bg}`, borderRadius:radius});
    else if (variant==='outline') $sw.css({background:'transparent', color:bg, border:`1px solid ${bg}`, borderRadius:radius});
    else $sw.css({background:`${bg}22`, color:bg, border:'1px solid transparent', borderRadius:radius});
    $sw.text(lbl);
}

function syncButtons() {
    let btns = [];
    $('.btn-row').each(function() {
        let l = $(this).find('.cb-label').val().trim();
        let u = $(this).find('.cb-url').val().trim();
        if (l || u) btns.push({ label:l, url:u, bg:$(this).find('.cb-bg').val(), color:$(this).find('.cb-text').val(), shape:$(this).find('.cb-shape-group .active').data('val')||'rounded', variant:$(this).find('.cb-variant-group .active').data('val')||'solid' });
    });
    $('#prod_custom_buttons').val(JSON.stringify(btns));
}
</script>
<?php endif; ?>
<?php require 'includes/footer.php'; ?>
