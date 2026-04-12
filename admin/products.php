<?php require 'includes/header.php'; ?>
<style>
/* Image Grid for drag & drop */
#existingImages {
    display: flex; flex-wrap: wrap; gap: 10px; padding: 10px; min-height: 50px;
    background: var(--hover); border-radius: 8px; border: 1px dashed var(--border);
}
.img-card {
    position: relative; width: 80px; height: 80px; border-radius: 8px; overflow: visible;
    cursor: grab; flex-shrink: 0; transition: transform 0.15s;
}
.img-card:active { cursor: grabbing; }
.img-card.sortable-drag { opacity: 0.4; }
.img-card.sortable-ghost { opacity: 0.3; }
.img-card img.img-thumb {
    width: 80px; height: 80px; object-fit: cover; border-radius: 8px;
    border: 2px solid transparent; display: block; transition: border-color 0.2s;
}
.img-card.is-thumbnail img.img-thumb { border-color: var(--warn); box-shadow: 0 0 0 2px var(--ws); }
.img-card .badge-thumb {
    position: absolute; bottom: -8px; left: 50%; transform: translateX(-50%);
    background: var(--warn); color: #fff; font-size: 9px; font-weight: 700;
    padding: 2px 6px; border-radius: 20px; white-space: nowrap; pointer-events: none; display: none;
}
.img-card.is-thumbnail .badge-thumb { display: block; }
.img-card .img-actions {
    position: absolute; top: -8px; right: -8px; display: flex; flex-direction: column; gap: 4px;
}
.img-card .btn-del-img, .img-card .btn-set-thumb {
    width: 22px; height: 22px; border-radius: 50%; border: none; display: flex;
    align-items: center; justify-content: center; font-size: 11px; cursor: pointer; padding: 0;
}
.img-card .btn-del-img { background: var(--err); color: #fff; }
.img-card .btn-set-thumb { background: var(--warn); color: #fff; font-size: 10px; }

/* Custom Buttons Editor */
#customButtonsContainer { display: flex; flex-direction: column; gap: 12px; }
.btn-row { background: var(--hover); border: 1px solid var(--border); border-radius: 10px; padding: 12px; display: flex; flex-direction: column; gap: 8px; }
.btn-row-top { display: flex; gap: 8px; align-items: center; }
.btn-row-opts { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; }
.btn-opt-group { display: flex; align-items: center; gap: 6px; }
.btn-opt-label { font-size: 11px; color: var(--mut); font-weight: 600; text-transform: uppercase; }
.btn-chip-group { display: flex; gap: 4px; }
.btn-chip {
    padding: 4px 10px; border-radius: 20px; border: 1px solid var(--border); background: transparent;
    color: var(--text); font-size: 11px; cursor: pointer; transition: all .12s;
}
.btn-chip.active { background: var(--accent); border-color: var(--accent); color: #fff; }
.cb-color { width: 30px; height: 30px; border-radius: 6px; border: 1px solid var(--border); cursor: pointer; padding: 2px; background: transparent; }
.btn-preview-swatch { display: inline-block; padding: 6px 18px; font-size: 12px; font-weight: 600; font-family: inherit; pointer-events: none; transition: all .15s; }

/* Image Picker Component */
#imgPickerPanel {
    display: none; position: fixed; z-index: 1060; background: var(--surface);
    border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 12px 40px rgba(0,0,0,.55);
    padding: 16px; min-width: 280px; font-family: 'Plus Jakarta Sans', sans-serif;
}
#imgPickerPanel.open { display: block; }
.ip-preview { width: 100%; height: 110px; object-fit: contain; background: var(--hover); border-radius: 8px; margin-bottom: 12px; }
.ip-label { font-size: 11px; font-weight: 600; text-transform: uppercase; color: var(--mut); margin-bottom: 6px; }
.ip-btn-group { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 12px; }
.ip-btn {
    flex: 1; min-width: 50px; padding: 6px 4px; border: 1px solid var(--border); border-radius: 6px;
    background: transparent; color: var(--text); font-size: 12px; cursor: pointer; text-align: center;
}
.ip-btn.active { background: var(--accent); border-color: var(--accent); color: #fff; }

/* Thumbnail column */
td.thumb-col { width: 60px; text-align: center; }
td.thumb-col img { width: 44px; height: 44px; border-radius: 8px; object-fit: cover; }
</style>

<div class="page-header d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1>Product Catalog</h1>
        <div class="bc">Manage your digital products, details, pricing, and images.</div>
    </div>
    <button class="btn btn-primary" onclick="openModal()"><i class='bx bx-plus me-1'></i> Add Product</button>
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
                    <!-- Populated by AJAX -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Main UI Modal -->
<div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalTitle">Add New Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="productForm">
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="id" id="prod_id" value="0">
                    
                    <div class="row g-4">
                        <div class="col-lg-8">
                            <div class="mb-3">
                                <label class="form-label text-muted fw-semibold">Product Title</label>
                                <input type="text" name="title" id="prod_title" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label text-muted fw-semibold">Description</label>
                                <div id="editor-container"></div>
                                <input type="hidden" name="description" id="prod_desc">
                            </div>
                            
                            <!-- Insert Image to Description (Internal tool) -->
                            <div class="mb-3 p-3" id="insertImgGroup" style="display:none; background: var(--hover); border-radius: 8px; border: 1px dashed var(--border);">
                                <label class="form-label text-muted" style="font-size: 13px;">Insert Gallery Image into Description</label>
                                <div id="insertImageGrid" class="d-flex flex-wrap gap-2"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label text-muted fw-semibold">Demo URL</label>
                                <input type="url" name="demo_link" id="prod_link" class="form-control" placeholder="https://...">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label text-muted fw-semibold">YouTube URL <small class="text-muted fw-normal">(Optional embed)</small></label>
                                <input type="url" name="youtube_url" id="prod_youtube" class="form-control" placeholder="https://youtube.com/watch?v=...">
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <div class="card-c mb-4">
                                <div class="cb">
                                    <div class="mb-3">
                                        <label class="form-label text-muted fw-semibold">Base Price (Rp)</label>
                                        <input type="number" name="price" id="prod_price" class="form-control" required>
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label text-muted fw-semibold">Promo Price (Rp) <small class="text-muted fw-normal">(Optional)</small></label>
                                        <input type="number" name="promo_price" id="prod_promo" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="card-c mb-4">
                                <div class="cb">
                                    <label class="form-label text-muted fw-semibold d-block">Gallery Images</label>
                                    <small class="text-muted d-block mb-3" style="font-size:12px;">Drag to reorder. First image is default.</small>
                                    
                                    <div id="existingImages" class="mb-3"></div>
                                    
                                    <div>
                                        <button type="button" class="btn btn-outline-primary w-100" onclick="openGalleryPickerModal()">
                                            <i class='bx bx-images me-1'></i> Pick from Gallery
                                        </button>
                                        <input type="hidden" name="gallery_images" id="prod_gallery_images" value="[]">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> <!-- Row ends -->
                    
                    <hr style="border-color: var(--border); margin: 30px 0;">
                    
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <label class="form-label text-muted fw-semibold mb-0">Custom Buttons <small class="fw-normal">(Extra links on product page)</small></label>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addButtonRow()"><i class='bx bx-plus'></i> Add Button</button>
                        </div>
                        <div id="customButtonsContainer"></div>
                        <input type="hidden" name="custom_buttons" id="prod_custom_buttons" value="[]">
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveProduct()" id="btnSaveProd">Save Product</button>
            </div>
        </div>
    </div>
</div>

<!-- Gallery Picker Modal -->
<style>
.gp-item {
    position: relative; border-radius: 8px; overflow: hidden; border: 2px solid transparent; cursor: pointer;
    transition: all 0.2s;
}
.gp-item img { width: 100%; height: 100px; object-fit: cover; display: block; }
.gp-item.selected { border-color: var(--accent); box-shadow: 0 0 0 2px var(--as); }
.gp-item.selected::after {
    content: '\eb31'; font-family: 'boxicons'; position: absolute; top: 4px; right: 4px;
    background: var(--accent); color: #fff; border-radius: 50%; width: 20px; height: 20px;
    display: flex; align-items: center; justify-content: center; font-size: 14px;
}
</style>
<div class="modal fade" id="galleryPickerModal" tabindex="-1" aria-hidden="true" style="z-index:1065;">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="background:var(--surface);">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold">Select Images from Gallery</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-dark" style="background:var(--bg) !important;">
                <div id="gpLoading" class="text-center py-5 text-muted"><i class="bx bx-loader-alt bx-spin fs-1"></i></div>
                <div id="gpGrid" style="display:none; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 12px;"></div>
            </div>
            <div class="modal-footer border-top-0 justify-content-between">
                <div class="text-muted"><span id="gpSelectedCount">0</span> selected</div>
                <div>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="confirmGallerySelection()">Attach Selected</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Insert Picker Panel -->
<div id="imgPickerPanel">
    <img id="ipPreview" class="ip-preview" src="" alt="">

    <div class="ip-label">Size</div>
    <div class="ip-btn-group" id="ipSizeGroup">
        <button class="ip-btn" data-val="25%">25%</button>
        <button class="ip-btn active" data-val="50%">50%</button>
        <button class="ip-btn" data-val="75%">75%</button>
        <button class="ip-btn" data-val="100%">Full</button>
    </div>

    <div class="ip-label">Alignment</div>
    <div class="ip-btn-group" id="ipAlignGroup">
        <button class="ip-btn active" data-val="center">&#8679; Center</button>
        <button class="ip-btn" data-val="left">&#8678; Left</button>
        <button class="ip-btn" data-val="right">Right &#8680;</button>
        <button class="ip-btn" data-val="inline">Inline</button>
    </div>

    <div class="d-flex gap-2 mt-2">
        <button class="btn btn-outline-secondary btn-sm w-100" onclick="closeImgPicker()">Cancel</button>
        <button class="btn btn-primary btn-sm w-100" onclick="confirmInsertImage()">Insert</button>
    </div>
</div>

<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>
let dtTable;
let _currentProductId = 0;
let _sortable = null;
let $modal;

// Initialize Quill
var quill = new Quill('#editor-container', {
    theme: 'snow',
    placeholder: 'Write complete product description here...',
    modules: {
        toolbar: {
            container: [
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'color': [] }, { 'background': [] }],
                ['link', 'image', 'clean']
            ],
            handlers: {
                image: function() {
                    var url = prompt('Insert image URL:');
                    if(url) {
                        var range = quill.getSelection();
                        quill.insertEmbed(range ? range.index : 0, 'image', url);
                    }
                }
            }
        }
    }
});

$(document).ready(function() {
    $modal = new bootstrap.Modal(document.getElementById('productModal'));
    
    // Destroy default datatable from header if it exists here to prevent double init
    if($.fn.DataTable.isDataTable('#productsTable')){
        $('#productsTable').DataTable().destroy();
    }
    
    dtTable = $('#productsTable').DataTable({
        pageLength: 10,
        language: { search: "Cari Product:", lengthMenu: "Tampil _MENU_ data" },
        ajax: {
            url: 'api_products.php?action=get_all',
            dataSrc: 'data'
        },
        order: [[1, 'asc']],
        columns: [
            { 
                data: 'first_image', 
                className: 'thumb-col px-4',
                render: function(data) {
                    if(data) return `<img src="../assets/uploads/${data}">`;
                    return `<div style="width:44px; height:44px; border-radius:8px; background:var(--hover); display:flex; align-items:center; justify-content:center; color:var(--mut);"><i class='bx bx-image-alt fs-4'></i></div>`;
                }
            },
            { data: 'title', render: function(data) { return `<span class="fw-semibold text-white">${data}</span>`; } },
            { 
                data: 'price', 
                render: function(data) { return `Rp ${Number(data).toLocaleString('id-ID')}`; } 
            },
            { 
                data: 'promo_price', 
                render: function(data) { 
                    if(data && data > 0) return `<span class="bd bd-ok">Rp ${Number(data).toLocaleString('id-ID')}</span>`;
                    return '<span class="text-muted">-</span>';
                } 
            },
            {
                data: 'id',
                className: 'text-end px-4',
                orderable: false,
                render: function(data) {
                    return `
                        <button class="btn btn-sm btn-outline-secondary me-1" onclick="editProduct(${data})" title="Edit"><i class='bx bx-edit'></i></button>
                        <button class="btn btn-sm btn-danger-outline" onclick="deleteProduct(${data})" title="Delete"><i class='bx bx-trash'></i></button>
                    `;
                }
            }
        ]
    });
});

function openModal() {
    _currentProductId = 0;
    $('#productForm')[0].reset();
    quill.root.innerHTML = '';
    $('#prod_id').val('0');
    $('#modalTitle').text('Add New Product');
    $('#existingImages').empty();
    $('#prod_gallery_images').val('[]');
    $('#insertImageGrid').empty();
    $('#insertImgGroup').hide();
    $('#customButtonsContainer').empty();
    $('#prod_custom_buttons').val('[]');
    $modal.show();
}

function closeModal() {
    $modal.hide();
}

function editProduct(id) {
    _currentProductId = id;
    $('#productForm')[0].reset();
    $('#prod_id').val(id);
    $('#modalTitle').text('Edit Product');
    $('#existingImages, #insertImageGrid, #customButtonsContainer').empty();
    $('#prod_gallery_images').val('[]');
    $('#insertImgGroup').hide();
    
    $.ajax({
        url: `api_products.php?action=get_single&id=${id}`,
        method: 'GET',
        success: function(json) {
            if(json.status === 'success') {
                let p = json.data;
                $('#prod_title').val(p.title);
                quill.root.innerHTML = p.description || '';
                $('#prod_price').val(p.price);
                $('#prod_promo').val(p.promo_price || '');
                $('#prod_link').val(p.demo_link || '');
                $('#prod_youtube').val(p.youtube_url || '');
                
                if(p.images && p.images.length > 0) renderImageGrid(p.images, p.id);
                if(p.custom_buttons && p.custom_buttons.length > 0) {
                    p.custom_buttons.forEach(btn => addButtonRow(btn.label, btn.url, btn.bg, btn.color, btn.shape, btn.variant));
                }
                $('#prod_custom_buttons').val(JSON.stringify(p.custom_buttons || []));
                
                $modal.show();
            } else {
                Toast.fire({ icon: 'error', title: 'Product not found!' });
            }
        }
    });
}

function saveProduct() {
    // prepare form
    $('#prod_desc').val(quill.root.innerHTML);
    syncButtons();
    
    let fd = new FormData($('#productForm')[0]);
    let $btn = $('#btnSaveProd');
    $btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Saving...');
    
    $.ajax({
        url: 'api_products.php',
        method: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        success: function(json) {
            $btn.prop('disabled', false).text('Save Product');
            if(json.status === 'success') {
                Toast.fire({ icon: 'success', title: 'Product saved successfully!' });
                dtTable.ajax.reload(null, false);
                $modal.hide();
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: json.message, background: 'var(--surface)', color: 'var(--text)' });
            }
        },
        error: function() {
            $btn.prop('disabled', false).text('Save Product');
        }
    });
}

function deleteProduct(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This product and all its assets will be deleted permanently!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: 'var(--err)',
        cancelButtonColor: 'var(--mut)',
        confirmButtonText: 'Yes, delete it!',
        background: 'var(--surface)', color: 'var(--text)'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('api_products.php', { action: 'delete', id: id }, function(json) {
                if(json.status === 'success') {
                    Toast.fire({ icon: 'success', title: 'Product deleted' });
                    dtTable.ajax.reload(null, false);
                }
            });
        }
    });
}

// Gallery & Images
function renderImageGrid(images, productId) {
    let $cont = $('#existingImages');
    let $insGrid = $('#insertImageGrid');
    $cont.empty(); $insGrid.empty();

    images.forEach(img => {
        if(img.is_mock) return;
        
        let thumbCls = img.is_thumbnail == 1 ? ' is-thumbnail' : '';
        let cardHTML = `
            <div class="img-card${thumbCls}" data-id="${img.id}">
                <img class="img-thumb" src="../assets/uploads/${img.image_path}">
                <div class="badge-thumb">★ Thumb</div>
                <div class="img-actions">
                    <button type="button" class="btn-del-img" title="Remove" onclick="deleteImage(${img.id}, ${productId})"><i class='bx bx-x'></i></button>
                    <button type="button" class="btn-set-thumb" title="Set Thumbnail" onclick="setThumbnail(${img.id}, ${productId})"><i class='bx bxs-star'></i></button>
                </div>
            </div>
        `;
        $cont.append(cardHTML);

        // For Quill Insert
        let insHTML = `
            <div style="width:60px;height:60px;border-radius:6px;overflow:hidden;cursor:pointer;border:1px solid var(--border);position:relative;" 
                 onclick="openImgPicker('../assets/uploads/${img.image_path}', this)"
                 onmouseenter="$(this).find('.ins-overlay').css({opacity:1, background:'rgba(0,0,0,0.5)'})"
                 onmouseleave="$(this).find('.ins-overlay').css({opacity:0, background:'rgba(0,0,0,0)'})">
                <img src="../assets/uploads/${img.image_path}" style="width:100%;height:100%;object-fit:cover;">
                <div class="ins-overlay" style="position:absolute;inset:0;background:rgba(0,0,0,0);transition:all .15s;display:flex;align-items:center;justify-content:center;font-size:24px;opacity:0;color:#fff;"><i class='bx bx-plus'></i></div>
            </div>
        `;
        $insGrid.append(insHTML);
    });

    $('#insertImgGroup').toggle(images.length > 0);

    if(_sortable) _sortable.destroy();
    _sortable = Sortable.create($cont[0], {
        animation: 150,
        ghostClass: 'sortable-ghost',
        dragClass: 'sortable-drag',
        onEnd: function() {
            let order = [];
            $cont.find('.img-card').each(function() { order.push($(this).data('id')); });
            $.post('api_products.php', { action: 'reorder_images', product_id: productId, order: order });
        }
    });
}

function deleteImage(imgId, productId) {
    if(confirm('Delete this image?')) {
        $.post('api_products.php', { action: 'delete_image', img_id: imgId, product_id: productId }, function(json) {
            if(json.status === 'success') {
                $.get(`api_products.php?action=get_single&id=${productId}`, function(res) {
                    if(res.status === 'success') renderImageGrid(res.data.images || [], productId);
                });
                dtTable.ajax.reload(null, false);
            }
        });
    }
}

function setThumbnail(imgId, productId) {
    $.post('api_products.php', { action: 'set_thumbnail', img_id: imgId, product_id: productId }, function(json) {
        if(json.status === 'success') {
            $('.img-card').removeClass('is-thumbnail');
            $(`.img-card[data-id="${imgId}"]`).addClass('is-thumbnail');
            Toast.fire({ icon: 'success', title: 'Thumbnail updated' });
            dtTable.ajax.reload(null, false);
        }
    });
}

// Image Insert Picker
let _ipUrl = '';
function openImgPicker(url, triggerEl) {
    _ipUrl = url;
    $('#ipPreview').attr('src', url);
    let rect = triggerEl.getBoundingClientRect();
    let left = rect.left + window.scrollX;
    let top = rect.bottom + window.scrollY + 8;
    if(left + 280 > window.innerWidth) left = window.innerWidth - 290;
    
    $('#imgPickerPanel').css({left: left, top: top}).addClass('open');
    setTimeout(() => { $(document).on('click.ip', _ipOutsideClick); }, 10);
}
function _ipOutsideClick(e) {
    if (!$(e.target).closest('#imgPickerPanel').length) closeImgPicker();
}
function closeImgPicker() {
    $('#imgPickerPanel').removeClass('open');
    $(document).off('click.ip');
}
function confirmInsertImage() {
    let size = $('#ipSizeGroup .active').data('val') || '50%';
    let align = $('#ipAlignGroup .active').data('val') || 'center';
    let style = `max-width:${size};height:auto;`;
    if(align === 'left') style += 'float:left;margin:0 14px 10px 0;';
    else if(align === 'right') style += 'float:right;margin:0 0 10px 14px;';
    else if(align === 'center') style += 'display:block;margin:10px auto;';
    else style += 'display:inline;margin:0 4px;';

    let html = `<img src="${_ipUrl}" style="${style}width:${size};">`;
    let range = quill.getSelection() || { index: quill.getLength() };
    quill.clipboard.dangerouslyPasteHTML(range.index, html, 'user');
    closeImgPicker();
    Toast.fire({ icon: 'success', title: 'Image inserted' });
}

$('.ip-btn').click(function(e) {
    $(this).siblings().removeClass('active');
    $(this).addClass('active');
    e.stopPropagation();
});

// Custom Buttons
function addButtonRow(label='', url='', bg='#3b82f6', color='#ffffff', shape='rounded', variant='solid') {
    let shapes = [{v:'pill',l:'Pill'},{v:'rounded',l:'Rounded'},{v:'square',l:'Square'}];
    let variants = [{v:'solid',l:'Solid'},{v:'outline',l:'Outline'},{v:'ghost',l:'Ghost'}];
    
    let shapeChips = shapes.map(s => `<button type="button" class="btn-chip${shape===s.v?' active':''}" data-val="${s.v}">${s.l}</button>`).join('');
    let variantChips = variants.map(v => `<button type="button" class="btn-chip${variant===v.v?' active':''}" data-val="${v.v}">${v.l}</button>`).join('');

    let html = `
        <div class="btn-row">
            <div class="row g-2 align-items-center">
                <div class="col-sm-5"><input type="text" class="form-control cb-label" placeholder="Button Label" value="${label}"></div>
                <div class="col-sm-6"><input type="text" class="form-control cb-url" placeholder="https://..." value="${url}"></div>
                <div class="col-sm-1 text-end"><button type="button" class="btn btn-sm btn-outline-danger" onclick="$(this).closest('.btn-row').remove(); syncButtons()"><i class='bx bx-x'></i></button></div>
            </div>
            <div class="btn-row-opts mt-2">
                <div class="btn-opt-group">
                    <span class="btn-opt-label">Shape</span>
                    <div class="btn-chip-group cb-shape-group">${shapeChips}</div>
                </div>
                <div class="btn-opt-group">
                    <span class="btn-opt-label">Style</span>
                    <div class="btn-chip-group cb-variant-group">${variantChips}</div>
                </div>
                <div class="btn-opt-group">
                    <span class="btn-opt-label">BG Filter</span>
                    <input type="color" class="cb-color cb-bg" value="${bg}">
                </div>
                <div class="btn-opt-group">
                    <span class="btn-opt-label">Text Color</span>
                    <input type="color" class="cb-color cb-text" value="${color}">
                </div>
                <div class="ms-auto"><span class="btn-preview-swatch">Preview</span></div>
            </div>
        </div>
    `;
    
    let $row = $(html);
    $('#customButtonsContainer').append($row);
    
    $row.find('.btn-chip').click(function() {
        $(this).siblings().removeClass('active');
        $(this).addClass('active');
        updateBtnPreview($row); syncButtons();
    });
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
    let radius = shape === 'pill' ? '50px' : shape === 'rounded' ? '8px' : '4px';
    let $swatch = $row.find('.btn-preview-swatch');

    if(variant === 'solid') $swatch.css({background: bg, color: clr, border: `1px solid ${bg}`, borderRadius: radius});
    else if(variant === 'outline') $swatch.css({background: 'transparent', color: bg, border: `1px solid ${bg}`, borderRadius: radius});
    else $swatch.css({background: `${bg}22`, color: bg, border: '1px solid transparent', borderRadius: radius});
    $swatch.text(lbl);
}

function syncButtons() {
    let btns = [];
    $('.btn-row').each(function() {
        let label = $(this).find('.cb-label').val().trim();
        let url = $(this).find('.cb-url').val().trim();
        if(label || url) {
            btns.push({
                label: label,
                url: url,
                bg: $(this).find('.cb-bg').val(),
                color: $(this).find('.cb-text').val(),
                shape: $(this).find('.cb-shape-group .active').data('val') || 'rounded',
                variant: $(this).find('.cb-variant-group .active').data('val') || 'solid'
            });
        }
    });
    $('#prod_custom_buttons').val(JSON.stringify(btns));
}

// Gallery Picker Modal Integration
let gpModal;
function openGalleryPickerModal() {
    if(!gpModal) gpModal = new bootstrap.Modal(document.getElementById('galleryPickerModal'));
    $('#gpGrid').hide().empty();
    $('#gpLoading').show();
    $('#gpSelectedCount').text('0');
    gpModal.show();
    
    $.get('api_gallery.php?action=get_all', function(json) {
        $('#gpLoading').hide();
        if(json.status === 'success') {
            $('#gpGrid').css('display', 'grid');
            json.data.forEach(img => {
                let html = `
                    <div class="gp-item" data-name="${img.name}" onclick="$(this).toggleClass('selected'); $('#gpSelectedCount').text($('.gp-item.selected').length)">
                        <img src="${img.url}" loading="lazy">
                    </div>
                `;
                $('#gpGrid').append(html);
            });
        }
    });
}

function confirmGallerySelection() {
    let selected = [];
    $('.gp-item.selected').each(function() {
        let name = $(this).data('name');
        selected.push(name);
        
        // Append visually to existingImages
        let mockId = 'ns_' + Math.floor(Math.random()*10000);
        let cardHTML = `
            <div class="img-card new-attached" data-name="${name}">
                <img class="img-thumb" src="../assets/uploads/${name}">
                <div class="img-actions">
                    <button type="button" class="btn-del-img" title="Remove" onclick="$(this).closest('.img-card').remove(); syncGalleryJSON();"><i class='bx bx-x'></i></button>
                </div>
            </div>
        `;
        $('#existingImages').append(cardHTML);
    });
    
    syncGalleryJSON();
    gpModal.hide();
}

function syncGalleryJSON() {
    let attached = [];
    $('.img-card.new-attached').each(function() {
        attached.push($(this).data('name'));
    });
    $('#prod_gallery_images').val(JSON.stringify(attached));
}
</script>
<?php require 'includes/footer.php'; ?>
