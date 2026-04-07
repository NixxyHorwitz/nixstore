<?php require 'includes/header.php'; ?>
<!-- Quill CSS -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<!-- SortableJS -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<style>
.ql-toolbar.ql-snow { background: #f3f4f6; border-radius: 6px 6px 0 0; border: none !important; }
.ql-container.ql-snow { background: #ffffff; color: #111; border-radius: 0 0 6px 6px; font-family: 'Outfit', sans-serif; font-size: 14px; border: none !important; min-height: 200px; }
.ql-editor { min-height: 200px; }

/* Image Grid for drag & drop */
#existingImages {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    padding: 10px;
    min-height: 50px;
    background: rgba(255,255,255,0.04);
    border-radius: 8px;
    border: 1px dashed var(--border-color, #333);
}
.img-card {
    position: relative;
    width: 80px;
    height: 80px;
    border-radius: 8px;
    overflow: visible;
    cursor: grab;
    flex-shrink: 0;
    transition: transform 0.15s, box-shadow 0.15s;
}
.img-card:active { cursor: grabbing; }
.img-card.sortable-drag { opacity: 0.4; }
.img-card.sortable-ghost { opacity: 0.3; }
.img-card img.img-thumb {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
    border: 2px solid transparent;
    display: block;
    transition: border-color 0.2s;
}
.img-card.is-thumbnail img.img-thumb {
    border-color: #f59e0b;
    box-shadow: 0 0 0 2px #f59e0b55;
}
.img-card .badge-thumb {
    position: absolute;
    bottom: -8px;
    left: 50%;
    transform: translateX(-50%);
    background: #f59e0b;
    color: #000;
    font-size: 9px;
    font-weight: 700;
    padding: 1px 6px;
    border-radius: 20px;
    white-space: nowrap;
    pointer-events: none;
    display: none;
}
.img-card.is-thumbnail .badge-thumb { display: block; }
.img-card .img-actions {
    position: absolute;
    top: -8px;
    right: -8px;
    display: flex;
    flex-direction: column;
    gap: 3px;
}
.img-card .btn-del-img, .img-card .btn-set-thumb {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    cursor: pointer;
    line-height: 1;
    padding: 0;
}
.img-card .btn-del-img { background: var(--danger, #ef4444); color: #fff; }
.img-card .btn-set-thumb { background: #f59e0b; color: #000; font-size: 9px; font-weight: 700; }
.drag-hint {
    font-size: 11px;
    color: #888;
    margin-top: 6px;
}

/* Custom Buttons Editor */
#customButtonsContainer {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.btn-row {
    background: rgba(255,255,255,0.04);
    border: 1px solid var(--border-color, #333);
    border-radius: 10px;
    padding: 12px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.btn-row-top {
    display: flex;
    gap: 8px;
    align-items: center;
}
.btn-row-top input[type="text"] {
    flex: 1;
    background: var(--input-bg, #1a1a2e);
    border: 1px solid var(--border-color, #333);
    border-radius: 6px;
    color: var(--text-primary, #fff);
    padding: 7px 10px;
    font-size: 13px;
    font-family: inherit;
}
.btn-row-top input[type="text"]:focus { outline: none; border-color: var(--accent, #6c63ff); }
.btn-row-remove {
    background: none;
    border: 1px solid var(--danger, #ef4444);
    color: var(--danger, #ef4444);
    border-radius: 6px;
    padding: 4px 10px;
    cursor: pointer;
    font-size: 12px;
    white-space: nowrap;
    flex-shrink: 0;
}
.btn-row-opts {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
}
.btn-opt-group {
    display: flex;
    align-items: center;
    gap: 5px;
}
.btn-opt-label {
    font-size: 10px;
    color: #888;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .4px;
    white-space: nowrap;
}
.btn-chip-group {
    display: flex;
    gap: 4px;
}
.btn-chip {
    padding: 3px 9px;
    border-radius: 20px;
    border: 1px solid var(--border-color, #444);
    background: transparent;
    color: var(--text-primary, #eee);
    font-size: 11px;
    font-family: inherit;
    cursor: pointer;
    transition: background .12s, border-color .12s;
    white-space: nowrap;
}
.btn-chip.active { background: var(--accent,#6c63ff); border-color: var(--accent,#6c63ff); color:#fff; }
.btn-chip:hover:not(.active) { background: rgba(255,255,255,.07); }
.btn-color-pair {
    display: flex;
    align-items: center;
    gap: 4px;
}
.btn-color-pair label { font-size: 10px; color: #888; white-space: nowrap; }
.cb-color {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    border: 1px solid var(--border-color,#444);
    cursor: pointer;
    padding: 2px;
    background: transparent;
}
.btn-preview {
    margin-top: 2px;
    display: flex;
    justify-content: flex-start;
}
.btn-preview-swatch {
    display: inline-block;
    padding: 5px 16px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    font-family: inherit;
    pointer-events: none;
    border: 2px solid transparent;
    transition: all .15s;
}
#addBtnRow {
    font-size: 12px;
    padding: 5px 12px;
}


/* ── Image Insert Picker ────────────────────────── */
#imgPickerPanel {
    display: none;
    position: fixed;
    z-index: 99999;
    background: var(--card-bg, #1e1e2e);
    border: 1px solid var(--border-color, #333);
    border-radius: 12px;
    box-shadow: 0 12px 40px rgba(0,0,0,.55);
    padding: 14px 16px;
    min-width: 260px;
    font-family: 'Outfit', sans-serif;
}
#imgPickerPanel.open { display: block; }
.ip-preview {
    width: 100%;
    height: 100px;
    object-fit: contain;
    background: rgba(255,255,255,0.04);
    border-radius: 8px;
    margin-bottom: 12px;
    display: block;
}
.ip-label {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: #888;
    margin-bottom: 6px;
}
.ip-btn-group {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    margin-bottom: 10px;
}
.ip-btn {
    flex: 1;
    min-width: 48px;
    padding: 5px 4px;
    border: 1px solid var(--border-color,#444);
    border-radius: 6px;
    background: transparent;
    color: var(--text-primary,#eee);
    font-size: 12px;
    font-family: inherit;
    cursor: pointer;
    text-align: center;
    transition: background .15s, border-color .15s;
}
.ip-btn:hover  { background: rgba(255,255,255,.08); }
.ip-btn.active { background: var(--accent,#6c63ff); border-color: var(--accent,#6c63ff); color:#fff; }
.ip-actions {
    display: flex;
    gap: 8px;
    margin-top: 4px;
}
.ip-actions button { flex:1; }

</style>

<div class="flex justify-between items-center mb-4">
    <h2 style="font-size: 20px; font-weight: 600;">Product Library</h2>
    <button class="btn btn-primary" onclick="openModal()">+ Add Product</button>
</div>

<div class="card card-p0">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 80px; padding-left: 30px;">Thumbnail</th>
                    <th>Product Title</th>
                    <th>Price</th>
                    <th>Promo</th>
                    <th class="text-right" style="padding-right: 30px;">Actions</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <tr><td colspan="5" style="text-align:center; padding: 40px; color:#666;">Loading data...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal-overlay" id="customModal">
    <div class="modal-box" style="max-width: 680px;">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Add Product</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="productForm">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" id="prod_id" value="0">
                <div class="form-group">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" id="prod_title" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <div id="editor-container"></div>
                    <input type="hidden" name="description" id="prod_desc">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Price</label>
                        <input type="number" name="price" id="prod_price" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Promo Price</label>
                        <input type="number" name="promo_price" id="prod_promo" class="form-input">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Demo Link</label>
                    <input type="text" name="demo_link" id="prod_link" class="form-input" placeholder="https://...">
                </div>
                <div class="form-group">
                    <label class="form-label">YouTube URL <span style="font-weight:400;color:#888;font-size:12px;">(embed di overview produk)</span></label>
                    <input type="text" name="youtube_url" id="prod_youtube" class="form-input" placeholder="https://www.youtube.com/watch?v=...">
                </div>
                
                <!-- Gallery Images -->
                <div class="form-group mb-2">
                    <label class="form-label" style="display:flex;align-items:center;gap:8px;">
                        Gallery Images
                        <span style="font-size:11px;color:#888;font-weight:400;">— drag untuk urutkan | ⭐ untuk set thumbnail</span>
                    </label>
                    <div id="existingImages"></div>
                    <div class="drag-hint">Drag gambar untuk atur urutan. Format kartu pertama = tampil di overview.</div>
                    <div style="margin-top:10px;">
                        <input type="file" name="images[]" id="file_input" class="form-input" accept="image/*" multiple>
                        <span class="form-hint">Pilih multiple dengan Ctrl/Cmd.</span>
                    </div>
                </div>

                <!-- Insert image to description -->
                <div class="form-group" id="insertImgGroup" style="display:none;">
                    <label class="form-label">Insert Image ke Deskripsi</label>
                    <div id="insertImageGrid" style="display:flex;flex-wrap:wrap;gap:8px;padding:8px;background:rgba(255,255,255,0.04);border-radius:8px;border:1px dashed var(--border-color,#333);"></div>
                    <span class="form-hint">Klik gambar untuk insert ke editor deskripsi.</span>
                </div>

                <!-- Custom Extra Buttons -->
                <div class="form-group">
                    <label class="form-label">Custom Buttons <span style="font-weight:400;color:#888;font-size:12px;">(tombol tambahan di halaman produk)</span></label>
                    <div id="customButtonsContainer"></div>
                    <button type="button" id="addBtnRow" class="btn btn-outline" onclick="addButtonRow()">+ Tambah Button</button>
                    <input type="hidden" name="custom_buttons" id="prod_custom_buttons" value="[]">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal()">Cancel</button>
            <button class="btn btn-primary" onclick="saveProduct()">Save Product</button>
        </div>
    </div>
</div>

<!-- ── Toast ───────────────────────────────────────────────────── -->
<div class="toast-container" id="toastContainer">
    <div class="toast" id="liveToast">
        <span class="toast-msg" id="toastMessage">Done!</span>
    </div>
</div>

<!-- ── Image Insert Picker Panel ─────────────────────────────── -->
<div id="imgPickerPanel">
    <img id="ipPreview" class="ip-preview" src="" alt="">

    <div class="ip-label">Ukuran</div>
    <div class="ip-btn-group" id="ipSizeGroup">
        <button class="ip-btn" data-val="25%">25%</button>
        <button class="ip-btn active" data-val="50%">50%</button>
        <button class="ip-btn" data-val="75%">75%</button>
        <button class="ip-btn" data-val="100%">Full</button>
    </div>

    <div class="ip-label">Tata Letak</div>
    <div class="ip-btn-group" id="ipAlignGroup">
        <button class="ip-btn active" data-val="center">&#8679; Tengah</button>
        <button class="ip-btn" data-val="left">&#8678; Kiri</button>
        <button class="ip-btn" data-val="right">Kanan &#8680;</button>
        <button class="ip-btn" data-val="inline">Inline</button>
    </div>

    <div class="ip-actions">
        <button class="btn btn-outline btn-sm" onclick="closeImgPicker()">Batal</button>
        <button class="btn btn-primary btn-sm" onclick="confirmInsertImage()">Insert</button>
    </div>
</div>


<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>
// ─── Quill Setup ──────────────────────────────────────────────────────────────
var quill = new Quill('#editor-container', {
    theme: 'snow',
    placeholder: 'Ketik deskripsi lengkap produk...',
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
                    // Custom image handler — prompt URL or pick from gallery
                    var url = prompt('Masukkan URL gambar:');
                    if(url) {
                        var range = quill.getSelection();
                        quill.insertEmbed(range ? range.index : 0, 'image', url);
                    }
                }
            }
        }
    }
});

// ─── State ────────────────────────────────────────────────────────────────────
let _currentProductId = 0;
let _sortable = null;

// ─── Toast ────────────────────────────────────────────────────────────────────
let _toastTimeout;
function showToast(msg, isError = false) {
    const toast = document.getElementById('liveToast');
    toast.className = 'toast ' + (isError ? 'error' : 'success');
    document.getElementById('toastMessage').innerText = msg;
    toast.classList.remove('show');
    void toast.offsetWidth; 
    toast.classList.add('show');
    clearTimeout(_toastTimeout);
    _toastTimeout = setTimeout(() => toast.classList.remove('show'), 4000);
}

// ─── Modal ────────────────────────────────────────────────────────────────────
const modal = document.getElementById('customModal');

function openModal() {
    _currentProductId = 0;
    document.getElementById('productForm').reset();
    quill.root.innerHTML = '';
    document.getElementById('prod_id').value = '0';
    document.getElementById('modalTitle').innerText = 'Add New Product';
    document.getElementById('existingImages').innerHTML = '';
    document.getElementById('insertImageGrid').innerHTML = '';
    document.getElementById('insertImgGroup').style.display = 'none';
    document.getElementById('customButtonsContainer').innerHTML = '';
    document.getElementById('prod_custom_buttons').value = '[]';
    modal.classList.add('show');
}

function closeModal() {
    modal.classList.remove('show');
    if(_sortable) { _sortable.destroy(); _sortable = null; }
}

// ─── Products Table ───────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => { loadProducts(); });

async function loadProducts() {
    try {
        let res = await fetch('api_products.php?action=get_all');
        let json = await res.json();
        let tbody = document.getElementById('tableBody');
        
        if(!json.data || json.data.length === 0){
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding: 40px; color:#666;">No products found.</td></tr>';
            return;
        }

        let html = '';
        json.data.forEach(p => {
            let img = p.first_image
                ? `<img src="../assets/uploads/${p.first_image}" style="width:40px;height:40px;border-radius:6px;object-fit:cover;">`
                : `<span style="color:#666;font-size:12px;">No Pic</span>`;
            let price = `Rp ${Number(p.price).toLocaleString('id-ID')}`;
            let promo = p.promo_price > 0
                ? `<span style="color:var(--success);">Rp ${Number(p.promo_price).toLocaleString('id-ID')}</span>`
                : '-';
            
            html += `<tr>
                <td style="padding-left: 30px;">${img}</td>
                <td style="font-weight:500; color:#fff;">${p.title}</td>
                <td>${price}</td>
                <td>${promo}</td>
                <td class="text-right" style="padding-right: 30px;">
                    <div class="flex items-center gap-2" style="justify-content:flex-end;">
                        <button class="btn btn-outline btn-sm" onclick="editProduct(${p.id})">Edit</button>
                        <button class="btn btn-danger-outline btn-sm" onclick="deleteProduct(${p.id})">Del</button>
                    </div>
                </td>
            </tr>`;
        });
        tbody.innerHTML = html;
    } catch(err) {
        console.error(err);
    }
}

// ─── Edit Product ─────────────────────────────────────────────────────────────
async function editProduct(id) {
    _currentProductId = id;
    document.getElementById('productForm').reset();
    document.getElementById('prod_id').value = id;
    document.getElementById('modalTitle').innerText = 'Edit Product';
    document.getElementById('existingImages').innerHTML = '';
    document.getElementById('insertImageGrid').innerHTML = '';
    document.getElementById('insertImgGroup').style.display = 'none';
    document.getElementById('customButtonsContainer').innerHTML = '';
    
    let res = await fetch(`api_products.php?action=get_single&id=${id}`);
    let json = await res.json();
    if(json.status === 'success'){
        let p = json.data;
        document.getElementById('prod_title').value = p.title;
        quill.root.innerHTML = p.description || '';
        document.getElementById('prod_desc').value = p.description || '';
        document.getElementById('prod_price').value = p.price;
        document.getElementById('prod_promo').value = p.promo_price || '';
        document.getElementById('prod_link').value = p.demo_link || '';
        document.getElementById('prod_youtube').value = p.youtube_url || '';
        
        // Render images with drag & thumbnail
        if(p.images && p.images.length > 0){
            renderImageGrid(p.images, p.id);
        }

        // Custom buttons
        if(p.custom_buttons && p.custom_buttons.length > 0){
            p.custom_buttons.forEach(btn => addButtonRow(btn.label, btn.url));
        }
        document.getElementById('prod_custom_buttons').value = JSON.stringify(p.custom_buttons || []);

        modal.classList.add('show');
    }
}

// ─── Image Grid with Drag & Thumbnail ─────────────────────────────────────────
function renderImageGrid(images, productId) {
    let container = document.getElementById('existingImages');
    let insertGrid = document.getElementById('insertImageGrid');
    container.innerHTML = '';
    insertGrid.innerHTML = '';

    images.forEach(img => {
        if(img.is_mock) return; // skip legacy mock

        let card = document.createElement('div');
        card.className = 'img-card' + (img.is_thumbnail ? ' is-thumbnail' : '');
        card.dataset.id = img.id;

        card.innerHTML = `
            <img class="img-thumb" src="../assets/uploads/${img.image_path}" alt="">
            <div class="badge-thumb">★ Thumb</div>
            <div class="img-actions">
                <button type="button" class="btn-del-img" title="Hapus" onclick="deleteImage(${img.id}, ${productId})">&times;</button>
                <button type="button" class="btn-set-thumb" title="Set Thumbnail" onclick="setThumbnail(${img.id}, ${productId})">★</button>
            </div>
        `;
        container.appendChild(card);

        // Insert grid
        let ins = document.createElement('div');
        ins.style.cssText = 'width:60px;height:60px;border-radius:6px;overflow:hidden;cursor:pointer;border:1px solid var(--border-color,#333);position:relative;';
        ins.innerHTML = `<img src="../assets/uploads/${img.image_path}" style="width:100%;height:100%;object-fit:cover;">
            <div style="position:absolute;inset:0;background:rgba(0,0,0,0);transition:background .15s;display:flex;align-items:center;justify-content:center;font-size:18px;opacity:0;" class="ins-overlay">+</div>`;
        ins.title = 'Insert ke deskripsi';
        ins.onmouseenter = e => { e.currentTarget.querySelector('.ins-overlay').style.cssText='background:rgba(0,0,0,.45);opacity:1;color:#fff;'; };
        ins.onmouseleave = e => { e.currentTarget.querySelector('.ins-overlay').style.cssText='background:rgba(0,0,0,0);opacity:0;'; };
        ins.onclick = () => openImgPicker('../assets/uploads/' + img.image_path, ins);
        insertGrid.appendChild(ins);

    });

    document.getElementById('insertImgGroup').style.display = images.length > 0 ? '' : 'none';

    // Init SortableJS
    if(_sortable) _sortable.destroy();
    _sortable = Sortable.create(container, {
        animation: 150,
        ghostClass: 'sortable-ghost',
        dragClass: 'sortable-drag',
        onEnd: async function() {
            let order = [...container.querySelectorAll('.img-card')].map(el => el.dataset.id);
            let fd = new FormData();
            fd.append('action', 'reorder_images');
            fd.append('product_id', productId);
            order.forEach((id, i) => fd.append('order[]', id));
            await fetch('api_products.php', { method: 'POST', body: fd });
        }
    });
}

// ─── Image Insert Picker ──────────────────────────────────────────────────────────
let _ipUrl = '';

function openImgPicker(url, triggerEl) {
    _ipUrl = url;
    document.getElementById('ipPreview').src = url;

    // Position panel near the trigger element
    const panel = document.getElementById('imgPickerPanel');
    panel.classList.add('open');
    const rect = triggerEl.getBoundingClientRect();
    const pw = 270;
    let left = rect.left + window.scrollX;
    let top  = rect.bottom + window.scrollY + 8;
    // Clamp so it doesn't go off-screen
    if (left + pw > window.innerWidth - 16) left = window.innerWidth - pw - 16;
    panel.style.left = left + 'px';
    panel.style.top  = top  + 'px';

    // Close picker on outside click
    setTimeout(() => {
        document.addEventListener('click', _ipOutsideClick);
    }, 10);
}

function _ipOutsideClick(e) {
    const panel = document.getElementById('imgPickerPanel');
    if (!panel.contains(e.target)) closeImgPicker();
}

function closeImgPicker() {
    document.getElementById('imgPickerPanel').classList.remove('open');
    document.removeEventListener('click', _ipOutsideClick);
}

function confirmInsertImage() {
    const sizeBtn  = document.querySelector('#ipSizeGroup  .ip-btn.active');
    const alignBtn = document.querySelector('#ipAlignGroup .ip-btn.active');
    const size  = sizeBtn  ? sizeBtn.dataset.val  : '50%';
    const align = alignBtn ? alignBtn.dataset.val : 'center';

    let style = `max-width:${size};height:auto;`;
    if (align === 'left')        style += 'float:left;margin:0 14px 10px 0;';
    else if (align === 'right')  style += 'float:right;margin:0 0 10px 14px;';
    else if (align === 'center') style += 'display:block;margin:10px auto;';
    else                         style += 'display:inline;margin:0 4px;';

    const html  = `<img src="${_ipUrl}" style="${style}width:${size};">`;
    const range = quill.getSelection() || { index: quill.getLength() };
    quill.clipboard.dangerouslyPasteHTML(range.index, html, 'user');
    closeImgPicker();
    showToast('Gambar diinsert!');
}

// Toggle active state on picker button groups
document.addEventListener('DOMContentLoaded', () => {
    ['ipSizeGroup', 'ipAlignGroup'].forEach(groupId => {
        document.getElementById(groupId).addEventListener('click', e => {
            const btn = e.target.closest('.ip-btn');
            if (!btn) return;
            btn.closest('.ip-btn-group').querySelectorAll('.ip-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            e.stopPropagation();
        });
    });
});

// ─── Thumbnail ────────────────────────────────────────────────────────────────
async function setThumbnail(imgId, productId) {
    let fd = new FormData();
    fd.append('action', 'set_thumbnail');
    fd.append('img_id', imgId);
    fd.append('product_id', productId);
    let res = await fetch('api_products.php', { method: 'POST', body: fd });
    let json = await res.json();
    if(json.status === 'success'){
        document.querySelectorAll('.img-card').forEach(c => c.classList.remove('is-thumbnail'));
        let card = document.querySelector(`.img-card[data-id="${imgId}"]`);
        if(card) card.classList.add('is-thumbnail');
        showToast('Thumbnail diset!');
        loadProducts();
    }
}


// ─── Delete Image ─────────────────────────────────────────────────────────────
async function deleteImage(img_id, product_id) {
    if(confirm('Hapus foto ini?')) {
        let fd = new FormData();
        fd.append('action', 'delete_image');
        fd.append('img_id', img_id);
        fd.append('product_id', product_id);
        let res = await fetch('api_products.php', { method: 'POST', body: fd });
        let json = await res.json();
        if(json.status === 'success') {
            let resP = await fetch(`api_products.php?action=get_single&id=${product_id}`);
            let jsonP = await resP.json();
            if(jsonP.status === 'success' && jsonP.data.images){
                renderImageGrid(jsonP.data.images, product_id);
            }
            loadProducts();
        }
    }
}

// ─── Custom Buttons ───────────────────────────────────────────────────────────
function addButtonRow(label = '', url = '', bg = '#6c63ff', color = '#ffffff', shape = 'rounded', variant = 'solid') {
    let container = document.getElementById('customButtonsContainer');
    let row = document.createElement('div');
    row.className = 'btn-row';

    const shapes   = [{v:'pill',l:'Pill'},{v:'rounded',l:'Rounded'},{v:'square',l:'Kotak'}];
    const variants = [{v:'solid',l:'Solid'},{v:'outline',l:'Outline'},{v:'ghost',l:'Ghost'}];

    const shapeChips   = shapes.map(s  => `<button type="button" class="btn-chip${shape===s.v?' active':''}" data-val="${s.v}">${s.l}</button>`).join('');
    const variantChips = variants.map(v => `<button type="button" class="btn-chip${variant===v.v?' active':''}" data-val="${v.v}">${v.l}</button>`).join('');

    row.innerHTML = `
        <div class="btn-row-top">
            <input type="text" placeholder="Label (cth: Lihat Template)" value="${label}" class="cb-label">
            <input type="text" placeholder="URL (https://...)" value="${url}" class="cb-url">
            <button type="button" class="btn-row-remove" onclick="this.closest('.btn-row').remove(); syncButtons()">✕</button>
        </div>
        <div class="btn-row-opts">
            <div class="btn-opt-group">
                <span class="btn-opt-label">Shape</span>
                <div class="btn-chip-group cb-shape-group">${shapeChips}</div>
            </div>
            <div class="btn-opt-group">
                <span class="btn-opt-label">Style</span>
                <div class="btn-chip-group cb-variant-group">${variantChips}</div>
            </div>
            <div class="btn-color-pair">
                <label>BG</label>
                <input type="color" class="cb-color cb-bg" value="${bg}">
            </div>
            <div class="btn-color-pair">
                <label>Teks</label>
                <input type="color" class="cb-color cb-text" value="${color}">
            </div>
        </div>
        <div class="btn-preview">
            <span class="btn-preview-swatch">Preview</span>
        </div>
    `;

    row.querySelectorAll('.btn-chip').forEach(chip => {
        chip.addEventListener('click', () => {
            chip.closest('.btn-chip-group').querySelectorAll('.btn-chip').forEach(c => c.classList.remove('active'));
            chip.classList.add('active');
            updatePreview(row); syncButtons();
        });
    });
    row.querySelector('.cb-label').addEventListener('input', () => { syncButtons(); updatePreview(row); });
    row.querySelector('.cb-url').addEventListener('input', syncButtons);
    row.querySelector('.cb-bg').addEventListener('input',   () => { updatePreview(row); syncButtons(); });
    row.querySelector('.cb-text').addEventListener('input', () => { updatePreview(row); syncButtons(); });

    container.appendChild(row);
    updatePreview(row);
    syncButtons();
}

function updatePreview(row) {
    const label   = row.querySelector('.cb-label').value || 'Label';
    const bg      = row.querySelector('.cb-bg').value;
    const clr     = row.querySelector('.cb-text').value;
    const shape   = row.querySelector('.cb-shape-group   .btn-chip.active')?.dataset?.val || 'rounded';
    const variant = row.querySelector('.cb-variant-group .btn-chip.active')?.dataset?.val || 'solid';
    const swatch  = row.querySelector('.btn-preview-swatch');
    const radius  = shape === 'pill' ? '50px' : shape === 'rounded' ? '10px' : '4px';

    if (variant === 'solid') {
        swatch.style.cssText = `background:${bg};color:${clr};border-color:${bg};border-radius:${radius};`;
    } else if (variant === 'outline') {
        swatch.style.cssText = `background:transparent;color:${bg};border-color:${bg};border-radius:${radius};`;
    } else {
        swatch.style.cssText = `background:${bg}22;color:${bg};border-color:transparent;border-radius:${radius};`;
    }
    swatch.textContent = label;
}

function syncButtons() {
    let rows = document.querySelectorAll('#customButtonsContainer .btn-row');
    let btns = [];
    rows.forEach(row => {
        let label   = row.querySelector('.cb-label')?.value.trim();
        let url     = row.querySelector('.cb-url')?.value.trim();
        let bg      = row.querySelector('.cb-bg')?.value || '#6c63ff';
        let clr     = row.querySelector('.cb-text')?.value || '#ffffff';
        let shape   = row.querySelector('.cb-shape-group   .btn-chip.active')?.dataset?.val || 'rounded';
        let variant = row.querySelector('.cb-variant-group .btn-chip.active')?.dataset?.val || 'solid';
        if(label || url) btns.push({ label, url, bg, color: clr, shape, variant });
    });
    document.getElementById('prod_custom_buttons').value = JSON.stringify(btns);
}

// ─── Save Product ─────────────────────────────────────────────────────────────
async function saveProduct() {
    syncButtons();
    document.getElementById('prod_desc').value = quill.root.innerHTML;
    let form = document.getElementById('productForm');
    let fd = new FormData(form);
    let res = await fetch('api_products.php', { method: 'POST', body: fd });
    let json = await res.json();
    if(json.status === 'success') {
        closeModal(); showToast(json.message); loadProducts();
    } else {
        showToast(json.message, true);
    }
}

// ─── Delete Product ───────────────────────────────────────────────────────────
async function deleteProduct(id) {
    if(confirm('Delete product forever?')) {
        let fd = new FormData();
        fd.append('action', 'delete');
        fd.append('id', id);
        let res = await fetch('api_products.php', { method: 'POST', body: fd });
        let json = await res.json();
        if(json.status === 'success') { showToast(json.message); loadProducts(); }
    }
}
</script>
<?php require 'includes/footer.php'; ?>
