<?php
require_once 'config.php';

header('Content-Type: application/json');

// Check parameters
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$user_type = isset($_GET['user_type']) ? $_GET['user_type'] : '';

if (!$user_id || !$user_type) {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit();
}

// Validate user type
if (!in_array($user_type, ['client', 'freelancer'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid user type']);
    exit();
}

try {
    $conn = getDBConnection();

    // Determine table and ID column based on user type
    $table = ($user_type === 'client') ? 'client' : 'freelancer';
    $id_column = ($user_type === 'client') ? 'ClientID' : 'FreelancerID';

    // Fetch profile picture
    $stmt = $conn->prepare("SELECT ProfilePicture FROM $table WHERE $id_column = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $profilePicture = $row['ProfilePicture'] ?? null;

        if (!empty($profilePicture)) {
            echo json_encode([
                'success' => true,
                'profilePicture' => $profilePicture
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'No profile picture'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'User not found'
        ]);
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
