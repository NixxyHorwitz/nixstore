<?php require 'includes/header.php'; ?>
<style>
/* Gallery Styles */
#uploadZone {
    border: 2px dashed var(--border);
    border-radius: 12px;
    padding: 40px;
    text-align: center;
    background: var(--hover);
    color: var(--mut);
    cursor: pointer;
    transition: all 0.2s;
}
#uploadZone.dragover {
    background: var(--surface);
    border-color: var(--accent);
    color: var(--accent);
}
.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
    gap: 16px;
    margin-top: 24px;
}
.gallery-item {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    background: var(--surface);
    border: 1px solid var(--border);
    transition: all 0.2s;
}
.gallery-item:hover {
    border-color: var(--accent);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}
.gallery-item img {
    width: 100%;
    height: 130px;
    object-fit: cover;
    display: block;
}
.gallery-item-info {
    padding: 8px;
    font-size: 11px;
    color: var(--sub);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    background: var(--card);
}
.gallery-actions {
    position: absolute;
    top: 6px;
    right: 6px;
    opacity: 0;
    transition: opacity 0.2s;
}
.gallery-item:hover .gallery-actions {
    opacity: 1;
}
.btn-del-gal {
    width: 28px; height: 28px;
    border-radius: 8px;
    background: var(--err);
    color: #fff;
    border: none;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
}
.empty-state { text-align: center; padding: 40px; color: var(--mut); }
</style>

<div class="page-header d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1>Media Gallery</h1>
        <div class="bc">Upload and manage images used across your digital products.</div>
    </div>
</div>

<div class="card-c mb-4">
    <div class="cb">
        <input type="file" id="galFileInput" accept="image/*" multiple style="display:none;">
        <div id="uploadZone" onclick="document.getElementById('galFileInput').click()">
            <i class='bx bx-cloud-upload' style="font-size:48px; margin-bottom:10px;"></i>
            <h5 style="color:var(--text); margin-bottom:4px;">Drag & Drop or Click to Upload</h5>
            <div style="font-size:12px;">Supports JPG, PNG, GIF, WEBP up to 5MB</div>
        </div>
    </div>
</div>

<div class="card-c">
    <div class="ch border-0">
        <h3 style="font-size:16px;font-weight:600;margin:0;">Uploaded Media</h3>
    </div>
    <div class="cb pt-0">
        <div class="gallery-grid" id="galleryGrid">
            <!-- Populated via AJAX -->
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    loadGallery();

    // Drag and drop handlers
    let $dropZone = $('#uploadZone');
    $dropZone.on('dragover', function(e) {
        e.preventDefault(); e.stopPropagation();
        $(this).addClass('dragover');
    });
    $dropZone.on('dragleave', function(e) {
        e.preventDefault(); e.stopPropagation();
        $(this).removeClass('dragover');
    });
    $dropZone.on('drop', function(e) {
        e.preventDefault(); e.stopPropagation();
        $(this).removeClass('dragover');
        let files = e.originalEvent.dataTransfer.files;
        handleFiles(files);
    });

    $('#galFileInput').on('change', function(e) {
        handleFiles(this.files);
    });
});

function loadGallery() {
    let $grid = $('#galleryGrid');
    $grid.html('<div class="empty-state"><i class="bx bx-loader-alt bx-spin" style="font-size:24px;"></i><div class="mt-2">Loading...</div></div>');
    
    $.get('api_gallery.php?action=get_all', function(json) {
        if(json.status === 'success') {
            if(json.data.length === 0) {
                $grid.html('<div class="empty-state"><i class="bx bx-images" style="font-size:48px;"></i><div class="mt-2">No media found. Upload some images!</div></div>');
                return;
            }
            $grid.empty();
            $grid.css('display','grid');
            json.data.forEach(img => {
                let sizeCb = (img.size / 1024).toFixed(1) + ' KB';
                let html = `
                    <div class="gallery-item" data-name="${img.name}">
                        <img src="${img.url}" loading="lazy">
                        <div class="gallery-item-info" title="${img.name}">
                            <div class="fw-bold text-white text-truncate">${img.name}</div>
                            ${sizeCb}
                        </div>
                        <div class="gallery-actions">
                            <button class="btn-del-gal" onclick="deleteTargetImage('${img.name}')"><i class='bx bx-trash'></i></button>
                        </div>
                    </div>
                `;
                $grid.append(html);
            });
        }
    });
}

function handleFiles(files) {
    if(!files || files.length === 0) return;
    
    let fd = new FormData();
    fd.append('action', 'upload');
    for(let i=0; i<files.length; i++) {
        fd.append('files[]', files[i]);
    }
    
    let $uz = $('#uploadZone');
    $uz.css('pointer-events','none').html('<i class="bx bx-loader-alt bx-spin" style="font-size:48px; margin-bottom:10px;"></i><h5 style="color:var(--text); margin-bottom:4px;">Uploading...</h5>');

    $.ajax({
        url: 'api_gallery.php',
        method: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        success: function(json) {
            $uz.css('pointer-events','auto').html('<i class="bx bx-cloud-upload" style="font-size:48px; margin-bottom:10px;"></i><h5 style="color:var(--text); margin-bottom:4px;">Drag & Drop or Click to Upload</h5><div style="font-size:12px;">Supports JPG, PNG, GIF, WEBP up to 5MB</div>');
            if(json.status === 'success') {
                Toast.fire({ icon: 'success', title: json.message });
                loadGallery();
            } else {
                Swal.fire({ icon: 'error', title: 'Upload Failed', text: json.message, background: 'var(--surface)', color: 'var(--text)' });
            }
        },
        error: function() {
            $uz.css('pointer-events','auto').html('<i class="bx bx-cloud-upload" style="font-size:48px; margin-bottom:10px;"></i><h5 style="color:var(--text); margin-bottom:4px;">Drag & Drop or Click to Upload</h5><div style="font-size:12px;">Supports JPG, PNG, GIF, WEBP up to 5MB</div>');
        }
    });
}

function deleteTargetImage(name) {
    Swal.fire({
        title: 'Delete Image?',
        text: "This image might be used in some products. Deleting it will break those product images. Continue?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: 'var(--err)',
        cancelButtonColor: 'var(--mut)',
        confirmButtonText: 'Yes, delete it',
        background: 'var(--surface)', color: 'var(--text)'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('api_gallery.php', { action: 'delete', name: name }, function(json) {
                if(json.status === 'success') {
                    Toast.fire({ icon: 'success', title: json.message });
                    loadGallery();
                } else {
                    Toast.fire({ icon: 'error', title: json.message });
                }
            });
        }
    });
}
</script>
<?php require 'includes/footer.php'; ?>
