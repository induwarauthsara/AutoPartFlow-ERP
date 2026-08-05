<section class="page-header">
    <h1>Dashboard</h1>
    <p>Welcome to AutoPartFlow ERP — manage your auto parts inventory.</p>
</section>

<div class="stats-grid">
    <div class="stat-card">
        <span class="stat-label">Total Parts</span>
        <span class="stat-value"><?= (int) $totalParts ?></span>
    </div>
    <div class="stat-card">
        <span class="stat-label">System</span>
        <span class="stat-value stat-value-sm">MVC Ready</span>
    </div>
</div>

<section class="card">
    <h2>Quick Actions</h2>
    <div class="actions">
        <a href="<?= url('parts') ?>" class="btn btn-primary">View Parts</a>
        <a href="<?= url('parts/create') ?>" class="btn btn-secondary">Add New Part</a>
    </div>
</section>

<section class="card">
    <h2>Architecture Overview</h2>
    <ul class="architecture-list">
        <li><strong>Model</strong> — Database logic in <code>app/Models/</code></li>
        <li><strong>View</strong> — HTML templates in <code>app/Views/</code></li>
        <li><strong>Controller</strong> — Request handling in <code>app/Controllers/</code></li>
        <li><strong>Router</strong> — URL routing in <code>app/Core/Router.php</code></li>
    </ul>
</section>
