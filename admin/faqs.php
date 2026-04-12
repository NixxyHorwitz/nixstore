<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    session_start();
    if (!isset($_SESSION['admin_logged_in'])) {
        http_response_code(403); echo json_encode(['status' => 'error', 'message' => 'Unauthorized']); exit;
    }
    require '../config/database.php';
    require '../includes/functions.php';

    header('Content-Type: application/json');
    $action = $_POST['action'];

    if ($action === 'get_all') {
        $faqs = get_all_faqs($pdo);
        echo json_encode(['data' => $faqs]);
        exit;
    }

    if ($action === 'get_single') {
        $id = (int)($_POST['id'] ?? 0);
        $faq = get_faq($pdo, $id);
        echo json_encode(['status' => 'success', 'data' => $faq]);
        exit;
    }

    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $question = trim($_POST['question'] ?? '');
        $answer = trim($_POST['answer'] ?? '');
        $order = (int)($_POST['display_order'] ?? 0);
        if ($id > 0) {
            $pdo->prepare("UPDATE faqs SET question=?, answer=?, display_order=? WHERE id=?")->execute([$question, $answer, $order, $id]);
        } else {
            $pdo->prepare("INSERT INTO faqs (question, answer, display_order) VALUES (?, ?, ?)")->execute([$question, $answer, $order]);
        }
        echo json_encode(['status' => 'success', 'message' => 'FAQ berhasil disimpan!']);
        exit;
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare("DELETE FROM faqs WHERE id=?")->execute([$id]);
        echo json_encode(['status' => 'success', 'message' => 'FAQ dihapus.']);
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Unknown action']);
    exit;
}

require 'includes/header.php';
?>

<div class="page-header d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1>FAQ Manager</h1>
        <div class="bc">Manage the frequently asked questions displayed on your site.</div>
    </div>
    <button class="btn btn-primary" onclick="openModal()"><i class='bx bx-plus me-1'></i> Add FAQ</button>
</div>

<div class="card-c">
    <div class="cb p-0">
        <div class="table-responsive">
            <table class="tbl datatable" id="faqTable" style="width:100%">
                <thead>
                    <tr>
                        <th class="px-4" style="width:50px;">#</th>
                        <th>Question</th>
                        <th style="width:100px;">Sort Order</th>
                        <th class="text-end px-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Populated via AJAX -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="faqModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalTitle">Add FAQ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="faqForm">
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="id" id="faq_id" value="0">
                    
                    <div class="mb-3">
                        <label class="form-label text-muted fw-semibold">Question</label>
                        <input type="text" name="question" id="faq_question" class="form-control" required placeholder="Question title...">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted fw-semibold">Answer</label>
                        <textarea name="answer" id="faq_answer" class="form-control" rows="5" required placeholder="Answer in detail..."></textarea>
                    </div>
                    
                    <div class="mb-0">
                        <label class="form-label text-muted fw-semibold">Display Order</label>
                        <input type="number" name="display_order" id="faq_order" class="form-control" value="0" min="0">
                        <small class="text-muted" style="font-size:12px;">Lower number = appears first.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveFaq()" id="btnSaveFaq">Save FAQ</button>
            </div>
        </div>
    </div>
</div>

<script>
let dtTable;
let $modal;

$(document).ready(function() {
    $modal = new bootstrap.Modal(document.getElementById('faqModal'));
    
    if($.fn.DataTable.isDataTable('#faqTable')){
        $('#faqTable').DataTable().destroy();
    }
    
    dtTable = $('#faqTable').DataTable({
        pageLength: 10,
        ajax: {
            url: 'faqs.php',
            type: 'POST',
            data: { action: 'get_all' },
            dataSrc: 'data'
        },
        order: [[2, 'asc'], [0, 'asc']],
        columns: [
            { data: 'id', className: 'px-4' },
            { data: 'question', render: function(data) { return `<span class="fw-semibold text-white">${data}</span>`; } },
            { data: 'display_order' },
            {
                data: 'id',
                className: 'text-end px-4',
                orderable: false,
                render: function(data) {
                    return `
                        <button class="btn btn-sm btn-outline-secondary me-1" onclick="editFaq(${data})" title="Edit"><i class='bx bx-edit'></i></button>
                        <button class="btn btn-sm btn-danger-outline" onclick="deleteFaq(${data})" title="Delete"><i class='bx bx-trash'></i></button>
                    `;
                }
            }
        ]
    });
});

function openModal() {
    $('#faqForm')[0].reset();
    $('#faq_id').val('0');
    $('#faq_order').val('0');
    $('#modalTitle').text('Add FAQ');
    $modal.show();
}

function editFaq(id) {
    $('#faqForm')[0].reset();
    $('#faq_id').val(id);
    $('#modalTitle').text('Edit FAQ');
    
    $.ajax({
        url: 'faqs.php',
        method: 'POST',
        data: { action: 'get_single', id: id },
        success: function(json) {
            if(json.status === 'success' && json.data) {
                let f = json.data;
                $('#faq_question').val(f.question);
                $('#faq_answer').val(f.answer);
                $('#faq_order').val(f.display_order);
                $modal.show();
            } else {
                Toast.fire({ icon: 'error', title: 'Data not found.' });
            }
        }
    });
}

function saveFaq() {
    let $btn = $('#btnSaveFaq');
    let fd = new FormData($('#faqForm')[0]);
    
    $btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Saving...');
    
    $.ajax({
        url: 'faqs.php',
        method: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        success: function(json) {
            $btn.prop('disabled', false).text('Save FAQ');
            if(json.status === 'success') {
                Toast.fire({ icon: 'success', title: json.message });
                dtTable.ajax.reload(null, false);
                $modal.hide();
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: json.message, background: 'var(--surface)', color: 'var(--text)' });
            }
        },
        error: function() {
            $btn.prop('disabled', false).text('Save FAQ');
            Toast.fire({ icon: 'error', title: 'Network error.' });
        }
    });
}

function deleteFaq(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This FAQ will be deleted permanently!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: 'var(--err)',
        cancelButtonColor: 'var(--mut)',
        confirmButtonText: 'Yes, delete it!',
        background: 'var(--surface)', color: 'var(--text)'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('faqs.php', { action: 'delete', id: id }, function(json) {
                if(json.status === 'success') {
                    Toast.fire({ icon: 'success', title: 'FAQ deleted' });
                    dtTable.ajax.reload(null, false);
                }
            });
        }
    });
}
</script>

<?php require 'includes/footer.php'; ?>
