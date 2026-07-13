<?php
$role      = $_SESSION['role'] ?? '';
$user_name = $_SESSION['user_name'] ?? 'User';

$role_labels = [
    'admin'    => ['label' => 'Administrator',     'icon' => 'fa-user-shield',          'color' => '#1a3c6e'],
    'incharge' => ['label' => 'Academic In-Charge', 'icon' => 'fa-user-tie',             'color' => '#065f46'],
    'teacher'  => ['label' => 'Teacher',            'icon' => 'fa-chalkboard-teacher',   'color' => '#7c3aed'],
    'student'  => ['label' => 'Student',            'icon' => 'fa-user-graduate',        'color' => '#92400e'],
];
$info = $role_labels[$role] ?? ['label' => 'User', 'icon' => 'fa-user', 'color' => '#374151'];
?>
<nav class="top-navbar">
    <div class="navbar-left">
        <button class="sidebar-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        <a href="/index.php" class="navbar-brand">
            <i class="fas fa-clock"></i>
            <span>CTT <strong>System</strong></span>
        </a>
    </div>
    <div class="navbar-right">
        <div class="navbar-time" id="navTime"></div>
        <div class="user-dropdown" onclick="toggleDropdown()">
            <div class="user-avatar">
                <?= strtoupper(substr($user_name, 0, 1)) ?>
            </div>
            <div class="user-info d-none d-md-block">
                <div class="user-name"><?= htmlspecialchars($user_name) ?></div>
                <div class="user-role"><i class="fas <?= $info['icon'] ?> me-1"></i><?= $info['label'] ?></div>
            </div>
            <i class="fas fa-chevron-down ms-2 text-muted" style="font-size:0.75rem;"></i>
            <div class="dropdown-menu-custom" id="userDropdown">
                <div class="dropdown-header">
                    <div class="fw-700"><?= htmlspecialchars($user_name) ?></div>
                    <div style="font-size:0.8rem; color:#9ca3af;"><?= $info['label'] ?></div>
                </div>
                <a href="/<?= $role ?>/profile.php"><i class="fas fa-user-circle me-2"></i>My Profile</a>
                <a href="/<?= $role ?>/change_password.php"><i class="fas fa-key me-2"></i>Change Password</a>
                <div class="dropdown-divider"></div>
                <a href="/logout.php" class="text-danger"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
            </div>
        </div>
    </div>
</nav>
<script>
function toggleDropdown() {
    document.getElementById('userDropdown').classList.toggle('show');
}
document.addEventListener('click', function(e) {
    if (!e.target.closest('.user-dropdown')) {
        document.getElementById('userDropdown')?.classList.remove('show');
    }
});
function updateTime() {
    const el = document.getElementById('navTime');
    if (!el) return;
    const now = new Date();
    el.textContent = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
}
setInterval(updateTime, 1000); updateTime();
</script>
