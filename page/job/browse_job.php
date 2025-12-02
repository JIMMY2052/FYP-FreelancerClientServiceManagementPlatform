<?php
session_start();

// only freelancers can browse projects
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'freelancer') {
    header('Location: /index.php');
    exit();
}

$_title = 'Browse Jobs';
include '../../_head.php';
require_once '../config.php';

$conn = getDBConnection();

// Get freelancer ID
$freelancerID = $_SESSION['user_id'];

// read filters from GET
$q = trim($_GET['q'] ?? '');
$min_budget = isset($_GET['min_budget']) && $_GET['min_budget'] !== '' ? floatval($_GET['min_budget']) : null;
$max_budget = isset($_GET['max_budget']) && $_GET['max_budget'] !== '' ? floatval($_GET['max_budget']) : null;
$sort = $_GET['sort'] ?? 'newest';

// build query - only show available jobs that the freelancer hasn't applied to
$sql = "SELECT j.JobID, j.ClientID, j.Title, j.Description, j.Budget, j.Deadline, j.Status, j.PostDate,
               c.CompanyName, c.ProfilePicture
        FROM job j
        INNER JOIN client c ON j.ClientID = c.ClientID
        WHERE j.Status = 'available'
        AND j.PostDate <= NOW()
        AND j.JobID NOT IN (
            SELECT JobID FROM job_application WHERE FreelancerID = ?
        )";

$params = [];
$types = '';

// keyword search - search by title and client company name, partial match
if ($q !== '') {
    $sql .= " AND (j.Title LIKE ? OR c.CompanyName LIKE ?)";
    $like = '%' . $q . '%';
    $params[] = $like;
    $params[] = $like;
    $types .= 'ss';
}

// budget range
if ($min_budget !== null) {
    $sql .= " AND Budget >= ?";
    $params[] = $min_budget;
    $types .= 'd';
}
if ($max_budget !== null) {
    $sql .= " AND Budget <= ?";
    $params[] = $max_budget;
    $types .= 'd';
}

// sort
if ($sort === 'highest') {
    $sql .= " ORDER BY Budget DESC, PostDate DESC";
} else { // newest (default)
    // limit
    $sql .= " LIMIT 100";

    // Prepare params array with freelancerID as first parameter
    $allParams = [$freelancerID];
    $allTypes = 'i';

    // Add filter params
    foreach ($params as $param) {
        $allParams[] = $param;
    }
    $allTypes .= $types;

    $stmt = $conn->prepare($sql);
    // bind dynamically
    $bind_names = [$allTypes];
    for ($i = 0; $i < count($allParams); $i++) {
        $bind_name = 'bind' . $i;
        $$bind_name = $allParams[$i];
        $bind_names[] = &$$bind_name;
    }
    call_user_func_array([$stmt, 'bind_param'], $bind_names);
    $stmt->execute();
    call_user_func_array([$stmt, 'bind_param'], $bind_names);
}
$stmt->execute();
$result = $stmt->get_result();
$jobs = $result->fetch_all(MYSQLI_ASSOC);

?>

<div class="container">
    <div class="browse-header">
        <h1 class="section-title">Browse Projects</h1>
    </div>

    <form method="get" class="search-filters">
        <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" placeholder="Search by job title or client name..." class="filter-input">
        <input type="number" name="min_budget" value="<?php echo ($min_budget !== null) ? htmlspecialchars($min_budget) : ''; ?>" placeholder="Min budget" class="filter-input">
        <input type="number" name="max_budget" value="<?php echo ($max_budget !== null) ? htmlspecialchars($max_budget) : ''; ?>" placeholder="Max budget" class="filter-input">
        <select name="sort" class="filter-select">
            <option value="newest" <?php if ($sort === 'newest') echo 'selected'; ?>>Newest</option>
            <option value="highest" <?php if ($sort === 'highest') echo 'selected'; ?>>Highest Budget</option>
        </select>
        <button type="submit" class="filter-search">Search</button>
        <a href="/page/job/browse_job.php" class="filter-reset">Reset</a>
    </form>

    <p class="results-count"><?php echo count($jobs); ?> result(s)</p>

    <?php if (empty($jobs)): ?>
        <div class="no-projects">
            <p>No projects found. Try adjusting your search criteria.</p>
        </div>
    <?php else: ?>
        <div class="browse-projects-grid">
            <?php foreach ($jobs as $job): ?>
                <div class="project-card">
                    <div class="client-info">
                        <div class="client-avatar">
                            <?php
                            $profilePic = $job['ProfilePicture'];

                            // Add leading slash if missing
                            if ($profilePic && !empty($profilePic) && strpos($profilePic, 'http') !== 0) {
                                if (strpos($profilePic, '/') !== 0) {
                                    $profilePic = '/' . $profilePic;
                                }
                            }

                            // Check if picture exists and display it or fallback to initial
                            if ($profilePic && !empty($profilePic)):
                            ?>
                                <img src="<?php echo htmlspecialchars($profilePic); ?>" alt="<?php echo htmlspecialchars($job['CompanyName']); ?>" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <?php endif; ?>
                            <div class="avatar-placeholder" style="<?= ($profilePic && !empty($profilePic)) ? 'display:none;' : 'display:flex;' ?>">
                                <?php echo strtoupper(substr($job['CompanyName'], 0, 1)); ?>
                            </div>
                        </div>
                        <div class="client-details">
                            <span class="client-name"><?php echo htmlspecialchars($job['CompanyName']); ?></span>
                        </div>
                    </div>

                    <div class="project-header">
                        <h3 class="project-title"><?php echo htmlspecialchars($job['Title']); ?></h3>
                        <span class="project-budget">$<?php echo number_format($job['Budget'], 2); ?></span>
                    </div>

                    <p class="project-description"><?php echo nl2br(htmlspecialchars(mb_strimwidth($job['Description'], 0, 300, '...'))); ?></p>

                    <div class="project-meta">
                        <span class="meta-item">Posted: <?php echo date('M d, Y', strtotime($job['PostDate'])); ?></span>
                        <span class="meta-item">Deadline: <?php echo date('M d, Y', strtotime($job['Deadline'])); ?></span>
                    </div>

                    <div class="project-actions">
                        <a href="job_details.php?id=<?php echo $job['JobID']; ?>" class="btn-small">View Details</a>
                        <a href="answer_questions.php?job_id=<?php echo $job['JobID']; ?>" class="btn-small">Apply</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    /* Browse Projects Page */
    body {
        overflow-x: hidden;
    }

    .container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 2rem;
        box-sizing: border-box;
    }

    .browse-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 24px;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #f0f0f0;
    }

    .browse-header h1 {
        margin: 0;
    }

    /* Search Filters */
    .search-filters {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 25px;
        padding: 20px;
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        box-sizing: border-box;
    }

    .filter-input {
        flex: 1;
        min-width: 200px;
        padding: 11px 16px;
        border-radius: 12px;
        border: 1px solid #ddd;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        box-sizing: border-box;
    }

    .filter-input:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
    }

    .filter-select {
        padding: 11px 16px;
        border-radius: 12px;
        border: 1px solid #ddd;
        font-size: 0.9rem;
        background: white;
        transition: all 0.3s ease;
        box-sizing: border-box;
    }

    .filter-select:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
    }

    .filter-search {
        padding: 11px 24px;
        border-radius: 16px;
        background: rgb(159, 232, 112);
        color: #333;
        border: none;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.9rem;
        box-sizing: border-box;
    }

    .filter-search:hover {
        background: rgb(140, 210, 90);
        box-shadow: 0 4px 8px rgba(159, 232, 112, 0.3);
    }

    .filter-reset {
        padding: 11px 24px;
        border-radius: 16px;
        background: #eee;
        color: #333;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-block;
        font-size: 0.9rem;
        box-sizing: border-box;
    }

    .filter-reset:hover {
        background: #ddd;
    }

    /* Results Count */
    .results-count {
        color: #666;
        margin-bottom: 20px;
        font-weight: 500;
    }

    /* Projects Grid */
    .browse-projects-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }

    /* Project Card */
    .project-card {
        background: white;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        box-sizing: border-box;
    }

    .project-card:hover {
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
        transform: translateY(-3px);
    }

    .client-info {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid #e9ecef;
    }

    .client-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        overflow: hidden;
        flex-shrink: 0;
        background: #f0f0f0;
        position: relative;
    }

    .client-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .avatar-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #16a34a, #15803d);
        color: white;
        font-weight: 800;
        font-size: 16px;
        text-transform: uppercase;
        position: absolute;
        top: 0;
        left: 0;
        border-radius: 50%;
    }

    .client-details {
        flex: 1;
        min-width: 0;
    }

    .client-name {
        font-size: 0.9rem;
        font-weight: 600;
        color: #2c3e50;
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .project-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 12px;
    }

    .project-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: #2c3e50;
        margin: 0;
        line-height: 1.3;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    .project-budget {
        background: rgb(159, 232, 112);
        color: #333;
        padding: 6px 12px;
        border-radius: 16px;
        font-size: 0.85rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .project-description {
        color: #666;
        font-size: 0.9rem;
        line-height: 1.5;
        margin: 0 0 15px 0;
        flex-grow: 1;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    .project-meta {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid #e9ecef;
    }

    .meta-item {
        color: #999;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .project-actions {
        display: flex;
        gap: 10px;
    }

    .project-actions .btn-small {
        flex: 1;
        text-align: center;
        padding: 11px 16px;
        border-radius: 16px;
        background: rgb(159, 232, 112);
        color: #333;
        border: none;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        box-sizing: border-box;
    }

    .project-actions .btn-small:hover {
        background: rgb(140, 210, 90);
    }

    .project-actions .btn-contact {
        background: #1ab394;
        color: white;
    }

    .project-actions .btn-contact:hover {
        background: #158a74;
    }

    /* No Projects Found */
    .no-projects {
        text-align: center;
        padding: 60px 40px;
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 2px dashed #dee2e6;
    }

    .no-projects p {
        font-size: 1.1rem;
        color: #666;
        margin: 0;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .container {
            padding: 0 1rem;
        }

        .browse-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }

        .search-filters {
            flex-direction: column;
            padding: 15px;
        }

        .filter-input {
            min-width: 100%;
        }

        .filter-select {
            width: 100%;
        }

        .filter-search,
        .filter-reset {
            width: 100%;
            text-align: center;
        }

        .browse-projects-grid {
            grid-template-columns: 1fr;
        }

        .project-header {
            flex-direction: column;
        }

        .project-budget {
            align-self: flex-start;
        }

        .project-actions {
            flex-direction: column;
        }

        .project-actions .btn-small {
            width: 100%;
        }
    }

    @media (max-width: 480px) {
        .container {
            padding: 0 0.5rem;
        }

        .browse-header h1 {
            font-size: 1.5rem;
        }

        .search-filters {
            padding: 12px;
        }

        .filter-input,
        .filter-select {
            padding: 10px 12px;
            font-size: 0.85rem;
        }

        .project-card {
            padding: 15px;
        }

        .project-title {
            font-size: 1rem;
        }

        .no-projects {
            padding: 40px 20px;
        }
    }
</style>

<?php
$stmt->close();
$conn->close();
include '../../_foot.php';
?>