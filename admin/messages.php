<?php
// admin/messages.php
require_once __DIR__ . '/includes/admin_init.php';

// Handle POST BEFORE header output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        set_flash('danger', 'Token CSRF tidak valid.');
        header('Location: messages.php');
        exit;
    }

    $action = $_POST['action'] ?? '';

    // MARK READ / UNREAD
    if ($action === 'mark_read') {
        $id     = (int)($_POST['message_id'] ?? 0);
        $status = (int)($_POST['status'] ?? 1);
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE messages SET is_read = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            set_flash('success', $status ? 'Pesan ditandai telah dibaca.' : 'Pesan ditandai belum dibaca.');
        }
    }

    // DELETE MESSAGE
    if ($action === 'delete') {
        $id = (int)($_POST['message_id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
            $stmt->execute([$id]);
            set_flash('success', 'Pesan berhasil dihapus.');
        }
    }

    header('Location: messages.php');
    exit;
}

$messages = $pdo->query("SELECT * FROM messages ORDER BY sent_at DESC")->fetchAll();

$currentPage = 'messages';
$pageTitle = 'Messages / Inbox';
$pageSubtitle = 'Kelola pesan dan pertanyaan masuk dari pengunjung formulir kontak';

require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-0 text-dark">Daftar Pesan Masuk (<?= count($messages) ?>)</h5>
    </div>
</div>

<div class="admin-card">
    <?php if (empty($messages)): ?>
        <div class="text-center py-5 text-muted">Belum ada pesan yang masuk.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table admin-table align-middle">
                <thead>
                    <tr>
                        <th style="width: 100px;">Status</th>
                        <th>Pengirim</th>
                        <th>Subjek</th>
                        <th>Waktu Masuk</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages as $msg): ?>
                        <tr class="<?= $msg['is_read'] ? '' : 'table-light' ?>">
                            <td>
                                <?php if ($msg['is_read']): ?>
                                    <span class="badge bg-secondary-subtle text-secondary">Dibaca</span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger fw-bold"><i class="bi bi-envelope-fill me-1"></i>Baru</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= e($msg['sender_name']) ?></div>
                                <div class="small text-muted"><?= e($msg['sender_email']) ?></div>
                            </td>
                            <td class="text-dark fw-medium"><?= e($msg['subject']) ?></td>
                            <td class="small text-muted"><?= date('d M Y, H:i', strtotime($msg['sent_at'])) ?></td>
                            <td class="text-end">
                                <button class="btn btn-outline-secondary btn-sm py-1 px-2.5 me-1"
                                    onclick="viewMessage(<?= htmlspecialchars(json_encode($msg), ENT_QUOTES, 'UTF-8') ?>)">
                                    <i class="bi bi-eye"></i> Baca
                                </button>
                                
                                <form method="POST" action="messages.php" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                    <input type="hidden" name="action" value="mark_read">
                                    <input type="hidden" name="message_id" value="<?= $msg['id'] ?>">
                                    <input type="hidden" name="status" value="<?= $msg['is_read'] ? 0 : 1 ?>">
                                    <button type="submit" class="btn btn-outline-secondary btn-sm py-1 px-2 me-1" title="Ubah status baca">
                                        <i class="bi <?= $msg['is_read'] ? 'bi-envelope' : 'bi-envelope-check' ?>"></i>
                                    </button>
                                </form>

                                <form method="POST" action="messages.php" class="d-inline" onsubmit="return confirm('Hapus pesan ini permanen?');">
                                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="message_id" value="<?= $msg['id'] ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm py-1 px-2">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Modal View Message -->
<div class="modal fade" id="viewMessageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header p-4 border-bottom">
                <div>
                    <h5 class="modal-title fw-bold font-heading text-dark mb-1" id="vMsgSubject"></h5>
                    <div class="small text-muted">
                        Dari: <strong class="text-dark" id="vMsgSender"></strong> &lt;<span id="vMsgEmail"></span>&gt;
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-muted small mb-2" id="vMsgDate"></div>
                <div class="p-3 bg-light rounded-3 border mb-4">
                    <p class="text-dark mb-0" id="vMsgContent" style="white-space: pre-wrap; line-height: 1.8;"></p>
                </div>

                <a href="#" id="vMsgReply" class="btn btn-admin-primary btn-sm">
                    <i class="bi bi-reply-fill me-1"></i> Balas via Email
                </a>
            </div>
            <div class="modal-footer p-3 border-top">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewMessage(msg) {
    document.getElementById('vMsgSubject').innerText = msg.subject || 'Tanpa Subjek';
    document.getElementById('vMsgSender').innerText = msg.sender_name;
    document.getElementById('vMsgEmail').innerText = msg.sender_email;
    document.getElementById('vMsgDate').innerText = 'Diterima pada: ' + msg.sent_at;
    document.getElementById('vMsgContent').innerText = msg.message;
    document.getElementById('vMsgReply').href = 'mailto:' + encodeURIComponent(msg.sender_email) + '?subject=' + encodeURIComponent('Re: ' + (msg.subject || ''));

    // Automatically mark read via background POST if currently unread
    if (parseInt(msg.is_read) === 0) {
        const formData = new FormData();
        formData.append('csrf_token', '<?= $csrf_token ?>');
        formData.append('action', 'mark_read');
        formData.append('message_id', msg.id);
        formData.append('status', 1);
        fetch('messages.php', { method: 'POST', body: formData });
    }

    new bootstrap.Modal(document.getElementById('viewMessageModal')).show();
}
</script>

<?php
require_once __DIR__ . '/includes/admin_footer.php';
?>
