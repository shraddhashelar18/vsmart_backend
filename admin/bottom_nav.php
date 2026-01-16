<?php
// $activePage must be set before including this file
?>

<style>
.bottom-nav {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: #ffffff;
    border-top: 1px solid #ddd;
    display: flex;
    justify-content: space-around;
    padding: 10px 0;
}
.bottom-nav a {
    text-decoration: none;
    color: #888;
    font-size: 14px;
    text-align: center;
}
.bottom-nav a.active {
    color: #009846;
    font-weight: bold;
}
</style>

<div class="bottom-nav">
    <a href="dashboard.php" class="<?= $activePage == 'dashboard' ? 'active' : '' ?>">
        🏠<br>Dashboard
    </a>
    <a href="reports.php" class="<?= $activePage == 'reports' ? 'active' : '' ?>">
        📄<br>Reports
    </a>
    <a href="settings.php" class="<?= $activePage == 'settings' ? 'active' : '' ?>">
        ⚙️<br>Settings
    </a>
</div>
