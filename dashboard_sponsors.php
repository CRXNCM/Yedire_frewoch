<?php
// Get sponsors count
$total_sponsors = 0;
$active_sponsors = 0;

// Count sponsors
$sponsors_result = $conn->query("SELECT COUNT(*) as count FROM sponsors");
if ($sponsors_result && $sponsors_result->num_rows > 0) {
    $row = $sponsors_result->fetch_assoc();
    $total_sponsors = $row['count'];
}

// Count active sponsors
$active_sponsors_result = $conn->query("SELECT COUNT(*) as count FROM sponsors WHERE is_active = 1");
if ($active_sponsors_result && $active_sponsors_result->num_rows > 0) {
    $row = $active_sponsors_result->fetch_assoc();
    $active_sponsors = $row['count'];
}

// Get recent sponsors
$recent_sponsors = [];
$recent_sponsors_query = "SELECT * FROM sponsors ORDER BY created_at DESC LIMIT 5";
$recent_sponsors_result = $conn->query($recent_sponsors_query);

if ($recent_sponsors_result && $recent_sponsors_result->num_rows > 0) {
    while ($row = $recent_sponsors_result->fetch_assoc()) {
        $recent_sponsors[] = $row;
    }
}
?>

<div class="col-md-4">
    <div class="card stat-card bg-secondary text-white">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="card-title">Sponsors</h6>
                    <h2 class="mb-0"><?php echo $total_sponsors; ?></h2>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-handshake"></i>
                </div>
            </div>
            <a href="manage_sponsors.php" class="text-white">
                <small>
                    <?php if ($active_sponsors > 0): ?>
                        <?php echo $active_sponsors; ?> Active Sponsor<?php echo $active_sponsors > 1 ? 's' : ''; ?>
                    <?php else: ?>
                        Manage Sponsors
                    <?php endif; ?>
                    <i class="fas fa-arrow-right"></i>
                </small>
            </a>
        </div>
    </div>
</div>

<!-- Recent Sponsors Card -->
<div class="col-lg-6 mb-4">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Recent Sponsors</h6>
            <a href="manage_sponsors.php" class="btn btn-sm btn-primary">
                View All
            </a>
        </div>
        <div class="card-body">
            <?php if (empty($recent_sponsors)): ?>
                <p class="text-center text-muted my-3">No sponsors yet.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Logo</th>
                                <th>Name</th>
                                <th>Website</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_sponsors as $sponsor): ?>
                                <tr>
                                    <td class="text-center">
                                        <?php if (!empty($sponsor['logo_path'])): ?>
                                            <img src="<?php echo htmlspecialchars($sponsor['logo_path']); ?>" 
                                                 alt="<?php echo htmlspecialchars($sponsor['name']); ?>" 
                                                 class="img-fluid"
                                                 style="max-height: 30px; max-width: 60px;">
                                        <?php else: ?>
                                            <span class="text-muted">No logo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($sponsor['name']); ?></td>
                                    <td>
                                        <?php if (!empty($sponsor['website_url'])): ?>
                                            <a href="<?php echo htmlspecialchars($sponsor['website_url']); ?>" target="_blank">
                                                <i class="fas fa-external-link-alt"></i> Link
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($sponsor['is_active']): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($sponsor['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>