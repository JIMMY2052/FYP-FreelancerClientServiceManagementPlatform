<?php
session_start();

$_title = 'Search Results';
include '../_head.php';
require_once 'config.php';

$conn = getDBConnection();

$q = trim($_GET['q'] ?? '');
$tab = $_GET['tab'] ?? 'all';

$users = [];     // will contain freelancers + clients, each row has Type = 'freelancer'|'client'
$services = [];
$jobs = [];

if ($q !== '') {
    $like = '%' . $q . '%';

    // Search freelancers
    $stmt = $conn->prepare("SELECT FreelancerID AS ID, FirstName, LastName, ProfilePicture, Bio, 'freelancer' AS Type
                            FROM freelancer
                            WHERE FirstName LIKE ? OR LastName LIKE ? OR Bio LIKE ?
                            LIMIT 20");
    if ($stmt) {
        $stmt->bind_param("sss", $like, $like, $like);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $users[] = $row;
        }
        $stmt->close();
    }

    // Search clients (CompanyName or Description)
    $stmt = $conn->prepare("SELECT ClientID AS ID, CompanyName AS FirstName, '' AS LastName, '' AS ProfilePicture, Description AS Bio, 'client' AS Type
                            FROM client
                            WHERE CompanyName LIKE ? OR Description LIKE ?
                            LIMIT 20");
    if ($stmt) {
        $stmt->bind_param("ss", $like, $like);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $users[] = $row;
        }
        $stmt->close();
    }

    // Search services (only active)
    $stmt = $conn->prepare("SELECT s.ServiceID, s.Title, s.Description, s.Price, s.DeliveryTime, f.FirstName, f.LastName
                            FROM service s
                            JOIN freelancer f ON s.FreelancerID = f.FreelancerID
                            WHERE s.Status = 'active' AND (s.Title LIKE ? OR s.Description LIKE ?)
                            LIMIT 20");
    if ($stmt) {
        $stmt->bind_param("ss", $like, $like);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $services[] = $row;
        }
        $stmt->close();
    }

    // Search jobs (only active)
    $stmt = $conn->prepare("SELECT j.JobID, j.Title, j.Description, j.Budget, j.Deadline
                            FROM job j
                            WHERE j.Status = 'active' AND (j.Title LIKE ? OR j.Description LIKE ?)
                            LIMIT 20");
    if ($stmt) {
        $stmt->bind_param("ss", $like, $like);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $jobs[] = $row;
        }
        $stmt->close();
    }
}
?>

<div class="container search-results-container">
    <div class="search-header">
        <h1>Search Results for "<?php echo htmlspecialchars($q); ?>"</h1>
    </div>

    <?php if ($q === ''): ?>
        <div class="empty-search">
            <div class="empty-icon">🔍</div>
            <h2>Start Searching</h2>
            <p>Use the search bar to find freelancers, services, and jobs</p>
        </div>
    <?php else: ?>
        <div class="tabs-container">
            <a href="?q=<?php echo urlencode($q); ?>&tab=all" class="tab-btn <?php echo ($tab === 'all') ? 'active' : ''; ?>">
                All (<?php echo count($users) + count($services) + count($jobs); ?>)
            </a>
            <a href="?q=<?php echo urlencode($q); ?>&tab=users" class="tab-btn <?php echo ($tab === 'users') ? 'active' : ''; ?>">
                Users (<?php echo count($users); ?>)
            </a>
            <a href="?q=<?php echo urlencode($q); ?>&tab=services" class="tab-btn <?php echo ($tab === 'services') ? 'active' : ''; ?>">
                Services (<?php echo count($services); ?>)
            </a>
            <a href="?q=<?php echo urlencode($q); ?>&tab=jobs" class="tab-btn <?php echo ($tab === 'jobs') ? 'active' : ''; ?>">
                Jobs (<?php echo count($jobs); ?>)
            </a>
        </div>

        <?php if ($tab === 'all' || $tab === ''): ?>
            <?php if (!empty($users)): ?>
                <section class="results-section">
                    <h2 class="section-title">👤 Users</h2>
                    <div class="users-grid">
                        <?php foreach ($users as $user): ?>
                            <div class="user-card">
                                <div class="user-avatar">
                                    <?php
                                    if (!empty($user['ProfilePicture'])) {
                                        echo '<img src="' . htmlspecialchars($user['ProfilePicture']) . '" alt="">';
                                    } else {
                                        echo '👤';
                                    }
                                    ?>
                                </div>

                                <h3>
                                    <?php
                                    // For clients we stored CompanyName in FirstName and blank LastName
                                    echo htmlspecialchars(trim($user['FirstName'] . ' ' . ($user['LastName'] ?? '')));
                                    ?>
                                </h3>

                                <p class="user-bio"><?php echo nl2br(htmlspecialchars(mb_strimwidth($user['Bio'] ?? '', 0, 100, '...'))); ?></p>

                                <?php if ($user['Type'] === 'freelancer'): ?>
                                    <a href="/page/view_freelancer.php?id=<?php echo $user['ID']; ?>" class="btn-view">View Profile</a>
                                <?php else: /* client */ ?>
                                    <a href="/page/view_client.php?id=<?php echo $user['ID']; ?>" class="btn-view">View Profile</a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <?php if (!empty($services)): ?>
                <section class="results-section">
                    <h2 class="section-title">🛠️ Services</h2>
                    <div class="services-grid">
                        <?php foreach ($services as $service): ?>
                            <div class="service-card">
                                <div class="card-header">
                                    <h3><?php echo htmlspecialchars($service['Title']); ?></h3>
                                    <span class="service-price">MYR <?php echo number_format($service['Price'], 2); ?></span>
                                </div>
                                <p class="service-provider">by <?php echo htmlspecialchars($service['FirstName'] . ' ' . $service['LastName']); ?></p>
                                <p class="service-description"><?php echo nl2br(htmlspecialchars(mb_strimwidth($service['Description'], 0, 150, '...'))); ?></p>
                                <div class="service-meta">
                                    <span>Delivery: <?php echo intval($service['DeliveryTime']); ?> day(s)</span>
                                </div>
                                <a href="/page/view_service.php?id=<?php echo $service['ServiceID']; ?>" class="btn-view">View Service</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <?php if (!empty($jobs)): ?>
                <section class="results-section">
                    <h2 class="section-title">💼 Jobs</h2>
                    <div class="jobs-grid">
                        <?php foreach ($jobs as $job): ?>
                            <div class="job-card">
                                <div class="card-header">
                                    <h3><?php echo htmlspecialchars($job['Title']); ?></h3>
                                    <span class="job-budget">$<?php echo number_format($job['Budget'], 2); ?></span>
                                </div>
                                <p class="job-description"><?php echo nl2br(htmlspecialchars(mb_strimwidth($job['Description'], 0, 150, '...'))); ?></p>
                                <div class="job-meta">
                                    <span>Deadline: <?php echo date('M d, Y', strtotime($job['Deadline'])); ?></span>
                                </div>
                                <a href="/page/job/view/jobDetails.php?id=<?php echo $job['JobID']; ?>" class="btn-view">View Job</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($tab === 'users'): ?>
            <?php if (!empty($users)): ?>
                <section class="results-section">
                    <div class="users-grid">
                        <?php foreach ($users as $user): ?>
                            <div class="user-card">
                                <div class="user-avatar">
                                    <?php
                                    if (!empty($user['ProfilePicture'])) {
                                        echo '<img src="' . htmlspecialchars($user['ProfilePicture']) . '" alt="">';
                                    } else {
                                        echo '👤';
                                    }
                                    ?>
                                </div>
                                <h3><?php echo htmlspecialchars(trim($user['FirstName'] . ' ' . ($user['LastName'] ?? ''))); ?></h3>
                                <p class="user-bio"><?php echo nl2br(htmlspecialchars(mb_strimwidth($user['Bio'] ?? '', 0, 100, '...'))); ?></p>
                                <?php if ($user['Type'] === 'freelancer'): ?>
                                    <a href="/page/view_freelancer.php?id=<?php echo $user['ID']; ?>" class="btn-view">View Profile</a>
                                <?php else: ?>
                                    <a href="/page/view_client.php?id=<?php echo $user['ID']; ?>" class="btn-view">View Profile</a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php else: ?>
                <div class="no-results"><p>No users found</p></div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($tab === 'services'): ?>
            <?php if (!empty($services)): ?>
                <section class="results-section">
                    <div class="services-grid">
                        <?php foreach ($services as $service): ?>
                            <div class="service-card">
                                <div class="card-header">
                                    <h3><?php echo htmlspecialchars($service['Title']); ?></h3>
                                    <span class="service-price">MYR <?php echo number_format($service['Price'], 2); ?></span>
                                </div>
                                <p class="service-provider">by <?php echo htmlspecialchars($service['FirstName'] . ' ' . $service['LastName']); ?></p>
                                <p class="service-description"><?php echo nl2br(htmlspecialchars(mb_strimwidth($service['Description'], 0, 150, '...'))); ?></p>
                                <div class="service-meta">
                                    <span>Delivery: <?php echo intval($service['DeliveryTime']); ?> day(s)</span>
                                </div>
                                <a href="/page/view_service.php?id=<?php echo $service['ServiceID']; ?>" class="btn-view">View Service</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php else: ?>
                <div class="no-results"><p>No services found</p></div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($tab === 'jobs'): ?>
            <?php if (!empty($jobs)): ?>
                <section class="results-section">
                    <div class="jobs-grid">
                        <?php foreach ($jobs as $job): ?>
                            <div class="job-card">
                                <div class="card-header">
                                    <h3><?php echo htmlspecialchars($job['Title']); ?></h3>
                                    <span class="job-budget">$<?php echo number_format($job['Budget'], 2); ?></span>
                                </div>
                                <p class="job-description"><?php echo nl2br(htmlspecialchars(mb_strimwidth($job['Description'], 0, 150, '...'))); ?></p>
                                <div class="job-meta">
                                    <span>Deadline: <?php echo date('M d, Y', strtotime($job['Deadline'])); ?></span>
                                </div>
                                <a href="/page/job/view/jobDetails.php?id=<?php echo $job['JobID']; ?>" class="btn-view">View Job</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php else: ?>
                <div class="no-results"><p>No jobs found</p></div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (empty($users) && empty($services) && empty($jobs)): ?>
            <div class="no-results">
                <div class="empty-icon">❌</div>
                <h2>No Results Found</h2>
                <p>Try searching with different keywords</p>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php
$conn->close();
include '../_foot.php';
?>

<style>
/* Browse Profile / Search Results Page */
.search-results-container {
    max-width: 1200px;
    margin: 30px auto;
    padding: 0 16px;
}

.search-header {
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #f0f0f0;
}

.search-header h1 {
    font-size: 2rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0;
}

/* Empty Search State */
.empty-search {
    text-align: center;
    padding: 80px 40px;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-radius: 16px;
    border: 2px dashed #dee2e6;
    margin: 40px 0;
}

.empty-icon {
    font-size: 4rem;
    margin-bottom: 20px;
}

.empty-search h2 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 12px 0;
}

.empty-search p {
    color: #666;
    margin: 0;
}

/* Tabs */
.tabs-container {
    display: flex;
    gap: 12px;
    margin-bottom: 30px;
    border-bottom: 2px solid #e9ecef;
    flex-wrap: wrap;
}

.tab-btn {
    padding: 12px 20px;
    background: none;
    border: none;
    text-decoration: none;
    font-weight: 600;
    color: #666;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    transition: all 0.3s ease;
    font-size: 0.95rem;
}

.tab-btn:hover {
    color: rgb(159, 232, 112);
}

.tab-btn.active {
    color: rgb(159, 232, 112);
    border-bottom-color: rgb(159, 232, 112);
}

/* Results Sections */
.results-section {
    margin-bottom: 40px;
}

.section-title {
    font-size: 1.3rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 20px 0;
}

/* Users Grid */
.users-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 20px;
}

.user-card {
    background: white;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    text-align: center;
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
}

.user-card:hover {
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
    transform: translateY(-3px);
}

.user-avatar {
    width: 80px;
    height: 80px;
    margin: 0 auto 12px;
    border-radius: 50%;
    background: #f0f0f0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    overflow: hidden;
}

.user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.user-card h3 {
    font-size: 1.1rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 8px 0;
}

.user-bio {
    color: #666;
    font-size: 0.85rem;
    margin: 0 0 12px 0;
    min-height: 40px;
    flex: 1;
}

/* Services Grid */
.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.service-card {
    background: white;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
}

.service-card:hover {
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
    transform: translateY(-3px);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 8px;
}

.card-header h3 {
    font-size: 1.1rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0;
    flex: 1;
    line-height: 1.3;
}

.service-price {
    background: rgb(159, 232, 112);
    color: #333;
    padding: 6px 12px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 0.85rem;
    white-space: nowrap;
}

.job-budget {
    background: #007bff;
    color: white;
    padding: 6px 12px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 0.85rem;
    white-space: nowrap;
}

.service-provider {
    color: #666;
    font-size: 0.9rem;
    margin: 0 0 8px 0;
}

.service-description,
.job-description {
    color: #555;
    font-size: 0.9rem;
    margin: 0 0 12px 0;
    flex: 1;
    line-height: 1.5;
}

.service-meta,
.job-meta {
    color: #999;
    font-size: 0.85rem;
    margin-bottom: 12px;
    padding-bottom: 12px;
    border-bottom: 1px solid #e9ecef;
}

/* Jobs Grid */
.jobs-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.job-card {
    background: white;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
}

.job-card:hover {
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
    transform: translateY(-3px);
}

/* Buttons */
.btn-view {
    background: rgb(159, 232, 112);
    color: #333;
    padding: 10px 16px;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9rem;
    text-align: center;
    transition: all 0.3s ease;
}

.btn-view:hover {
    background: rgb(140, 210, 90);
    box-shadow: 0 4px 12px rgba(159, 232, 112, 0.3);
}

/* No Results */
.no-results {
    text-align: center;
    padding: 60px 40px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    border: 2px dashed #dee2e6;
}

.no-results h2 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 12px 0;
}

.no-results p {
    font-size: 1rem;
    color: #666;
    margin: 0;
}

/* Responsive */
@media (max-width: 768px) {
    .users-grid {
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    }

    .services-grid,
    .jobs-grid {
        grid-template-columns: 1fr;
    }

    .tab-btn {
        padding: 10px 16px;
        font-size: 0.85rem;
    }
}

@media (max-width: 480px) {
    .search-header h1 {
        font-size: 1.4rem;
    }

    .users-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    }

    .user-avatar {
        width: 60px;
        height: 60px;
    }

    .card-header {
        flex-direction: column;
    }

    .service-price,
    .job-budget {
        align-self: flex-start;
    }
}
</style>

<?php
/*
Database table structure:
- freelancer table with FreelancerID, FirstName, LastName, ProfilePicture, Bio
- service table with ServiceID, FreelancerID, Title, Description, Price, DeliveryTime, Status
- job table with JobID, Title, Description, Budget, Deadline, Status

The page searches:
1. Freelancers by FirstName, LastName, Bio
2. Services by Title, Description (only Status = 'active')
3. Jobs by Title, Description (only Status = 'active')
*/
?>