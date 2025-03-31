<?php
// Get testimonials count
$testimonials_count = 0;
$active_testimonials = 0;

$testimonials_query = "SELECT COUNT(*) as total, SUM(is_active) as active FROM testimonials";
$testimonials_result = $conn->query($testimonials_query);

if ($testimonials_result && $testimonials_result->num_rows > 0) {
    $row = $testimonials_result->fetch_assoc();
    $testimonials_count = $row['total'];
    $active_testimonials = $row['active'];
}

// Get recent testimonials
$recent_testimonials = [];
$recent_query = "SELECT * FROM testimonials ORDER BY created_at DESC LIMIT 5";
$recent_result = $conn->query($recent_query);

if ($recent_result && $recent_result->num_rows > 0) {
    while ($row = $recent_result->fetch_assoc()) {
        $recent_testimonials[] = $row;
    }
}
?>

<!-- Testimonials Overview Card -->
<div class="col-xl-3 col-md-6 mb-4">
    <div class="card border-left-info shadow h-100 py-2">
        <div class="card-body">
            <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                        Testimonials</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $testimonials_count; ?></div>
                    <div class="small mt-2">
                        <span class="font-weight-bold text-success"><?php echo $active_testimonials; ?></span> active
                    </div>
                </div>
                <div class="col-auto">
                    <i class="fas fa-quote-left fa-2x text-gray-300"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Testimonials Card -->
<div class="col-lg-6 mb-4">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Recent Testimonials</h6>
            <a href="manage_testimonials.php" class="btn btn-sm btn-primary">
                View All
            </a>
        </div>
        <div class="card-body">
            <?php if (empty($recent_testimonials)): ?>
                <p class="text-center text-muted my-3">No testimonials yet.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Rating</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_testimonials as $testimonial): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($testimonial['image_path'])): ?>
                                            <img src="<?php echo htmlspecialchars($testimonial['image_path']); ?>" 
                                                 alt="<?php echo htmlspecialchars($testimonial['name']); ?>" 
                                                 class="img-profile rounded-circle mr-2"
                                                 width="30" height="30">
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($testimonial['name']); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($testimonial['role']); ?></td>
                                    <td>
                                        <?php for ($i = 0; $i < $testimonial['rating']; $i++): ?>
                                            <i class="fas fa-star text-warning small"></i>
                                        <?php endfor; ?>
                                    </td>
                                    <td>
                                        <?php if ($testimonial['is_active']): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($testimonial['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>