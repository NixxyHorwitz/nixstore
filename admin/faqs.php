<?php
// ── AJAX handler harus di paling atas, SEBELUM header output HTML ──
// Cek dulu apakah ini request AJAX (ada POST 'action')
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Bootstrap session + DB + functions tanpa output HTML
    session_start();
    if (!isset($_SESSION['admin_logged_in'])) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }
    require '../config/database.php';
    require '../includes/functions.php';

    header('Content-Type: application/json');
    $action = $_POST['action'];

    if ($action === 'get_single') {
        $id  = (int)($_POST['id'] ?? 0);
        $faq = get_faq($pdo, $id);
        echo json_encode(['status' => 'success', 'data' => $faq]);
        exit;
    }

    if ($action === 'save') {
        $id       = (int)($_POST['id'] ?? 0);
        $question = trim($_POST['question'] ?? '');
        $answer   = trim($_POST['answer'] ?? '');
        $order    = (int)($_POST['display_order'] ?? 0);
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

// ── Normal page load ──
require 'includes/header.php';
$faqs = get_all_faqs($pdo);
?>

<div class="flex justify-between items-center mb-4">
    <h2 style="font-size: 20px; font-weight: 600;">FAQ Manager</h2>
    <button class="btn btn-primary" onclick="openModal()">+ Tambah FAQ</button>
</div>

<div class="card card-p0">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="padding-left:30px; width:50px;">#</th>
                    <th>Pertanyaan</th>
                    <th style="width:80px;">Urutan</th>
                    <th class="text-right" style="padding-right:30px;">Aksi</th>
                </tr>
            </thead>
            <tbody id="faqTableBody">
                <?php if (empty($faqs)): ?>
                <tr><td colspan="4" style="text-align:center;padding:40px;color:#666;">Belum ada FAQ.</td></tr>
                <?php else: ?>
                <?php foreach($faqs as $f): ?>
                <tr id="row_<?= $f['id'] ?>">
                    <td style="padding-left:30px; color:#555;"><?= $f['id'] ?></td>
                    <td style="font-weight:500; color:#fff; max-width:500px;"><?= htmlspecialchars($f['question']) ?></td>
                    <td><span style="background:var(--bg-base);padding:4px 10px;border-radius:6px;font-size:12px;color:var(--text-muted);"><?= $f['display_order'] ?></span></td>
                    <td class="text-right" style="padding-right:30px;">
                        <div class="flex items-center gap-2" style="justify-content:flex-end;">
                            <button class="btn btn-outline btn-sm" onclick="editFaq(<?= $f['id'] ?>)">Edit</button>
                            <button class="btn btn-danger-outline btn-sm" onclick="deleteFaq(<?= $f['id'] ?>)">Hapus</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal-overlay" id="faqModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Tambah FAQ</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="faq_id" value="0">
            <div class="form-group">
                <label class="form-label">Pertanyaan</label>
                <input type="text" id="faq_question" class="form-input" placeholder="Masukkan pertanyaan..." required>
            </div>
            <div class="form-group">
                <label class="form-label">Jawaban</label>
                <textarea id="faq_answer" class="form-input" rows="5" placeholder="Masukkan jawaban lengkap..."></textarea>
            </div>
            <div class="form-group mb-2">
                <label class="form-label">Urutan Tampil</label>
                <input type="number" id="faq_order" class="form-input" value="0" min="0">
                <span class="form-hint">Angka kecil = tampil lebih dulu. Angka sama = urutkan by ID.</span>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal()">Batal</button>
            <button class="btn btn-primary" onclick="saveFaq()">Simpan FAQ</button>
        </div>
    </div>
</div>

<!-- Toast -->
<div class="toast-container">
    <div class="toast" id="liveToast"><span class="toast-msg" id="toastMessage"></span></div>
</div>

<script>
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

const modal = document.getElementById('faqModal');

function openModal() {
    document.getElementById('faq_id').value = '0';
    document.getElementById('faq_question').value = '';
    document.getElementById('faq_answer').value = '';
    document.getElementById('faq_order').value = '0';
    document.getElementById('modalTitle').innerText = 'Tambah FAQ';
    modal.classList.add('show');
}

function closeModal() {
    modal.classList.remove('show');
}

async function editFaq(id) {
    const fd = new FormData();
    fd.append('action', 'get_single');
    fd.append('id', id);
    try {
        const res = await fetch('faqs.php', { method: 'POST', body: fd });
        const json = await res.json();
        if (json.status === 'success' && json.data) {
            const f = json.data;
            document.getElementById('faq_id').value = f.id;
            document.getElementById('faq_question').value = f.question;
            document.getElementById('faq_answer').value = f.answer;
            document.getElementById('faq_order').value = f.display_order;
            document.getElementById('modalTitle').innerText = 'Edit FAQ';
            modal.classList.add('show');
        }
    } catch(e) {
        showToast('Gagal memuat data FAQ.', true);
        console.error(e);
    }
}

async function saveFaq() {
    const fd = new FormData();
    fd.append('action', 'save');
    fd.append('id', document.getElementById('faq_id').value);
    fd.append('question', document.getElementById('faq_question').value);
    fd.append('answer', document.getElementById('faq_answer').value);
    fd.append('display_order', document.getElementById('faq_order').value);
    try {
        const res = await fetch('faqs.php', { method: 'POST', body: fd });
        const json = await res.json();
        if (json.status === 'success') {
            closeModal();
            showToast(json.message);
            setTimeout(() => location.reload(), 900);
        } else {
            showToast(json.message, true);
        }
    } catch(e) {
        showToast('Terjadi error saat menyimpan.', true);
        console.error(e);
    }
}

async function deleteFaq(id) {
    if (!confirm('Hapus FAQ ini secara permanen?')) return;
    const fd = new FormData();
    fd.append('action', 'delete');
    fd.append('id', id);
    try {
        const res = await fetch('faqs.php', { method: 'POST', body: fd });
        const json = await res.json();
        if (json.status === 'success') {
            document.getElementById('row_' + id)?.remove();
            showToast(json.message);
        }
    } catch(e) {
        showToast('Gagal menghapus FAQ.', true);
        console.error(e);
    }
}
</script>

<?php require 'includes/footer.php'; ?>
