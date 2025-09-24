<?php
session_start();
require_once '../includes/db.php';

// Check if user is logged in and is a librarian
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || $_SESSION['user_role'] !== 'librarian') {
    header('Location: index.php');
    exit();
}

// Handle survey actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $survey_id = (int)$_POST['survey_id'];

    switch ($_POST['action']) {
        case 'activate':
            executeQuery("UPDATE surveys SET status = 'active' WHERE id = ?", "i", [$survey_id]);
            $_SESSION['success_message'] = "Survey activated successfully!";
            break;
        case 'deactivate':
            executeQuery("UPDATE surveys SET status = 'inactive' WHERE id = ?", "i", [$survey_id]);
            $_SESSION['success_message'] = "Survey deactivated successfully!";
            break;
        case 'delete':
            executeQuery("DELETE FROM surveys WHERE id = ?", "i", [$survey_id]);
            $_SESSION['success_message'] = "Survey deleted successfully!";
            break;
    }
    header('Location: manage-surveys.php');
    exit();
}

// Fetch surveys with statistics
$surveys_query = "
    SELECT
        s.*,
        COUNT(sr.id) as response_count,
        COUNT(CASE WHEN sr.response_status = 'completed' THEN 1 END) as completed_count,
        MAX(sr.submitted_at) as last_response
    FROM surveys s
    LEFT JOIN survey_responses sr ON s.id = sr.survey_id
    GROUP BY s.id
    ORDER BY s.created_at DESC
";
$surveys = executeQuery($surveys_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Surveys - Library Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-green: #2d5a27;
            --medium-green: #4a7c59;
            --light-green: #7fb069;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }

        .main-content {
            margin-left: 260px;
            padding: 2rem;
            min-height: 100vh;
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .page-title {
            color: var(--primary-green);
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            color: #6c757d;
            font-size: 1rem;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 1.5rem;
        }

        .card-header {
            background: white;
            border-bottom: 1px solid #e9ecef;
            padding: 1rem 1.5rem;
        }

        .card-title {
            color: var(--primary-green);
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
        }

        .btn-primary {
            background: var(--medium-green);
            border: none;
            border-radius: 6px;
            padding: 0.6rem 1.2rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: var(--primary-green);
        }

        .survey-item {
            padding: 1.2rem;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .survey-item:hover {
            border-color: var(--medium-green);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .survey-title {
            color: var(--primary-green);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .survey-meta {
            color: #6c757d;
            font-size: 0.85rem;
            margin-bottom: 0.8rem;
        }

        .badge {
            padding: 0.3rem 0.8rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .badge-success {
            background: var(--light-green);
            color: white;
        }

        .badge-warning {
            background: #ffc107;
            color: #212529;
        }

        .badge-danger {
            background: #dc3545;
            color: white;
        }

        /* Alert Styles */
        .alert-container {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1050;
            width: 90%;
            max-width: 500px;
        }

        .alert {
            animation: slideDown 0.3s ease-in-out;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border: none;
            border-radius: 8px;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Confirmation Modal Styles */
        .confirmation-modal .modal-content {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .confirmation-modal .modal-header {
            background: linear-gradient(135deg, var(--primary-green), var(--medium-green));
            color: white;
            border-radius: 12px 12px 0 0;
            padding: 1.5rem;
        }

        .confirmation-modal .modal-body {
            padding: 2rem;
            text-align: center;
        }

        .confirmation-modal .modal-footer {
            padding: 1.5rem;
            border-top: 1px solid #e9ecef;
        }

        .confirmation-modal .btn-danger {
            background: #dc3545;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            font-weight: 500;
        }

        .confirmation-modal .btn-warning {
            background: #ffc107;
            border: none;
            color: #212529;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            font-weight: 500;
        }

        .confirmation-modal .btn-secondary {
            background: #6c757d;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            font-weight: 500;
        }

        /* Survey Tabs Styling */
        .survey-tabs .nav-pills .nav-link {
            color: #6c757d;
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            margin: 0 0.5rem;
            padding: 1rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }

        .survey-tabs .nav-pills .nav-link:hover {
            border-color: var(--medium-green);
            color: var(--medium-green);
        }

        .survey-tabs .nav-pills .nav-link.active {
            background: var(--medium-green);
            border-color: var(--medium-green);
            color: white;
        }

        .survey-tabs .nav-pills .nav-link .badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
        }

        .survey-tabs .nav-pills .nav-link.active .badge {
            background: white !important;
            color: var(--medium-green) !important;
        }

        /* Mobile Menu Toggle */
        .mobile-menu-toggle {
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1060;
            background: var(--medium-green);
            color: white;
            border: none;
            border-radius: 8px;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }

        .mobile-menu-toggle:hover {
            background: var(--primary-green);
            transform: scale(1.05);
        }

        /* Mobile Overlay */
        .mobile-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1055;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .mobile-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        @media (max-width: 768px) {
            /* Override sidebar z-index and positioning on mobile */
            .sidebar {
                z-index: 1056 !important;
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                width: 260px !important;
                height: 100vh !important;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .sidebar.show {
                transform: translateX(0) !important;
            }

            .main-content {
                margin-left: 0;
                padding: 1rem;
                position: relative;
                z-index: 1;
                width: 100%;
                overflow-x: hidden;
            }

            /* Prevent body scroll when sidebar is open */
            body.sidebar-open {
                overflow: hidden;
            }

            .alert-container {
                width: 95%;
                z-index: 1057;
            }

            /* Mobile Page Header */
            .page-header .d-flex {
                flex-direction: column;
                gap: 1rem;
            }

            .page-title {
                font-size: 1.5rem;
            }

            /* Mobile Survey Tabs */
            .survey-tabs .nav-pills {
                flex-direction: column;
                gap: 0.5rem;
            }

            .survey-tabs .nav-pills .nav-link {
                margin: 0;
                padding: 0.8rem 1rem;
                text-align: center;
            }

            .survey-tabs .nav-pills .nav-link i {
                display: none;
            }

            .survey-tabs .nav-pills .nav-link .badge {
                margin-left: 0.5rem;
            }

            /* Mobile Survey Items */
            .survey-item {
                padding: 1rem;
            }

            .survey-item .d-flex {
                flex-direction: column;
                gap: 1rem;
            }

            .survey-meta {
                font-size: 0.8rem;
                line-height: 1.4;
            }

            .survey-meta i {
                display: none;
            }

            /* Mobile Dropdown Actions */
            .dropdown-menu {
                position: absolute !important;
                transform: none !important;
                right: 0 !important;
                left: auto !important;
            }

            /* Mobile Modal Adjustments */
            .modal-dialog {
                margin: 1rem;
                max-width: calc(100% - 2rem);
            }

            .confirmation-modal .modal-body {
                padding: 1.5rem;
            }
        }

        @media (max-width: 576px) {
            .main-content {
                padding: 0.5rem;
            }

            .page-title {
                font-size: 1.3rem;
            }

            .page-subtitle {
                font-size: 0.9rem;
            }

            /* Extra small screens - Stack everything */
            .survey-item .d-flex .d-flex {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 0.5rem;
            }

            .btn-sm {
                font-size: 0.8rem;
                padding: 0.4rem 0.8rem;
            }

            .survey-tabs .nav-pills .nav-link {
                font-size: 0.9rem;
                padding: 0.7rem 0.8rem;
            }
        }

        /* Landscape mobile orientation */
        @media (max-width: 896px) and (orientation: landscape) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .survey-tabs .nav-pills {
                flex-direction: row;
            }

            .survey-tabs .nav-pills .nav-link {
                font-size: 0.85rem;
                padding: 0.6rem 0.8rem;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle d-md-none" id="mobileMenuToggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobileOverlay"></div>

    <!-- Include Librarian Sidebar -->
    <?php include '../includes/librarian_sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="page-title">Manage Surveys</h1>
                    <p class="page-subtitle">Create, edit, and manage library surveys.</p>
                </div>
                <a href="add-survey.php" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Create New Survey
                </a>
            </div>
        </div>

        <!-- Categorize Surveys -->
        <?php
        $active_surveys = [];
        $draft_surveys = [];
        $inactive_surveys = [];

        if ($surveys && $surveys->num_rows > 0) {
            while ($survey = $surveys->fetch_assoc()) {
                if ($survey['status'] == 'active') {
                    $active_surveys[] = $survey;
                } elseif ($survey['status'] == 'draft') {
                    $draft_surveys[] = $survey;
                } else {
                    $inactive_surveys[] = $survey;
                }
            }
        }
        ?>

        <!-- Survey Type Navigation Tabs -->
        <div class="survey-tabs mb-4">
            <ul class="nav nav-pills nav-fill">
                <li class="nav-item">
                    <a class="nav-link active" id="active-tab" data-bs-toggle="pill" href="#active-surveys" role="tab">
                        <i class="fas fa-play-circle me-2"></i>Active Surveys
                        <span class="badge bg-white text-success ms-2"><?= count($active_surveys) ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="draft-tab" data-bs-toggle="pill" href="#draft-surveys" role="tab">
                        <i class="fas fa-file-alt me-2"></i>Draft Surveys
                        <span class="badge bg-white text-warning ms-2"><?= count($draft_surveys) ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="inactive-tab" data-bs-toggle="pill" href="#inactive-surveys" role="tab">
                        <i class="fas fa-pause-circle me-2"></i>Inactive Surveys
                        <span class="badge bg-white text-danger ms-2"><?= count($inactive_surveys) ?></span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Tab Content -->
        <div class="tab-content">
            <!-- Active Surveys Tab -->
            <div class="tab-pane fade show active" id="active-surveys" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Active Surveys (<?= count($active_surveys) ?>)</h5>
                        </div>
                        <div class="card-body">
                <?php if (!empty($active_surveys)): ?>
                    <?php foreach ($active_surveys as $survey): ?>
                        <div class="survey-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="survey-title"><?= htmlspecialchars($survey['title']) ?></div>
                                    <div class="survey-meta">
                                        <i class="fas fa-calendar me-2"></i>Created: <?= date('M j, Y', strtotime($survey['created_at'])) ?> •
                                        <i class="fas fa-users me-2"></i><?= $survey['response_count'] ?> responses •
                                        <i class="fas fa-clock me-2"></i><?= $survey['estimated_duration'] ?> minutes
                                        <?php if ($survey['last_response']): ?>
                                            • <i class="fas fa-clock-o me-2"></i>Last response: <?= date('M j, Y', strtotime($survey['last_response'])) ?>
                                        <?php endif; ?>
                                    </div>
                                    <p class="mb-2"><?= htmlspecialchars($survey['description']) ?></p>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge badge-success">Active</span>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Actions
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="edit-survey.php?id=<?= $survey['id'] ?>"><i class="fas fa-edit me-2"></i>Edit</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="viewSurvey(<?= $survey['id'] ?>)"><i class="fas fa-eye me-2"></i>Preview</a></li>
                                            <li><a class="dropdown-item" href="responses.php?survey_id=<?= $survey['id'] ?>"><i class="fas fa-eye me-2"></i>View Responses</a></li>
                                            <li><a class="dropdown-item" href="analytics.php?survey_id=<?= $survey['id'] ?>"><i class="fas fa-chart-bar me-2"></i>Analytics</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-warning" href="#" onclick="changeSurveyStatus(<?= $survey['id'] ?>, 'deactivate')"><i class="fas fa-pause me-2"></i>Deactivate</a></li>
                                            <li><a class="dropdown-item text-danger" href="#" onclick="deleteSurvey(<?= $survey['id'] ?>)"><i class="fas fa-trash me-2"></i>Delete</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                        <h5>No active surveys</h5>
                        <p class="text-muted">Create your first survey to get started!</p>
                        <a href="add-survey.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Create New Survey
                        </a>
                        </div>
                    <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Draft Surveys Tab -->
            <div class="tab-pane fade" id="draft-surveys" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Draft Surveys (<?= count($draft_surveys) ?>)</h5>
                    </div>
            <div class="card-body">
                <?php if (!empty($draft_surveys)): ?>
                    <?php foreach ($draft_surveys as $survey): ?>
                        <div class="survey-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="survey-title"><?= htmlspecialchars($survey['title']) ?></div>
                                    <div class="survey-meta">
                                        <i class="fas fa-calendar me-2"></i>Created: <?= date('M j, Y', strtotime($survey['created_at'])) ?> •
                                        <i class="fas fa-clock me-2"></i><?= $survey['estimated_duration'] ?> minutes •
                                        <i class="fas fa-file-alt me-2"></i>Draft - Not published yet
                                    </div>
                                    <p class="mb-2"><?= htmlspecialchars($survey['description']) ?></p>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge badge-warning">Draft</span>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Actions
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="edit-survey.php?id=<?= $survey['id'] ?>"><i class="fas fa-edit me-2"></i>Continue Editing</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="viewSurvey(<?= $survey['id'] ?>)"><i class="fas fa-eye me-2"></i>Preview</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-success" href="#" onclick="changeSurveyStatus(<?= $survey['id'] ?>, 'activate')"><i class="fas fa-play me-2"></i>Publish Survey</a></li>
                                            <li><a class="dropdown-item text-danger" href="#" onclick="deleteSurvey(<?= $survey['id'] ?>)"><i class="fas fa-trash me-2"></i>Delete Draft</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                        <h5>No draft surveys</h5>
                        <p class="text-muted">Start creating a survey to see drafts here.</p>
                        <a href="add-survey.php" class="btn btn-outline-primary">
                            <i class="fas fa-plus me-2"></i>Create New Survey
                        </a>
                        </div>
                    <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Inactive/Completed Surveys Tab -->
            <div class="tab-pane fade" id="inactive-surveys" role="tabpanel">
                <?php if (!empty($inactive_surveys)): ?>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Inactive/Completed Surveys (<?= count($inactive_surveys) ?>)</h5>
                        </div>
                        <div class="card-body">
                <?php foreach ($inactive_surveys as $survey): ?>
                    <div class="survey-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="survey-title"><?= htmlspecialchars($survey['title']) ?></div>
                                <div class="survey-meta">
                                    <i class="fas fa-calendar me-2"></i>Created: <?= date('M j, Y', strtotime($survey['created_at'])) ?> •
                                    <i class="fas fa-users me-2"></i><?= $survey['response_count'] ?> responses •
                                    <i class="fas fa-clock me-2"></i><?= $survey['estimated_duration'] ?> minutes
                                </div>
                                <p class="mb-2"><?= htmlspecialchars($survey['description']) ?></p>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge badge-danger">
                                    <?= ucfirst($survey['status']) ?>
                                </span>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="responses.php?survey_id=<?= $survey['id'] ?>"><i class="fas fa-eye me-2"></i>View Results</a></li>
                                        <li><a class="dropdown-item" href="analytics.php?survey_id=<?= $survey['id'] ?>"><i class="fas fa-chart-bar me-2"></i>Analytics</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="alert('Export functionality coming soon!')"><i class="fas fa-download me-2"></i>Export Data</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-success" href="#" onclick="changeSurveyStatus(<?= $survey['id'] ?>, 'activate')"><i class="fas fa-play me-2"></i>Reactivate</a></li>
                                        <li><a class="dropdown-item text-danger" href="#" onclick="deleteSurvey(<?= $survey['id'] ?>)"><i class="fas fa-trash me-2"></i>Delete</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        </div>
                    <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-pause-circle fa-3x text-muted mb-3"></i>
                        <h5>No inactive surveys</h5>
                        <p class="text-muted">Inactive surveys will appear here when you deactivate them.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- View Survey Modal -->
    <div class="modal fade" id="viewSurveyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-eye me-2"></i>Survey Preview
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="surveyPreviewContent">
                    <!-- Survey preview content will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="editCurrentSurvey()">
                        <i class="fas fa-edit me-2"></i>Edit Survey
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Fixed Alert Container -->
    <div class="alert-container">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php
                switch ($_GET['error']) {
                    case 'create_failed':
                        echo 'Failed to create survey. Please try again.';
                        break;
                    default:
                        echo 'An error occurred. Please try again.';
                }
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Confirmation Modals -->
    <!-- Activate Survey Modal -->
    <div class="modal fade confirmation-modal" id="activateModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-play me-2"></i>Activate Survey
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <i class="fas fa-question-circle text-success fa-3x mb-3"></i>
                    </div>
                    <h6>Are you sure you want to activate this survey?</h6>
                    <p class="text-muted mb-0">Students will be able to take this survey once activated.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="confirmActivate">
                        <i class="fas fa-play me-2"></i>Activate Survey
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Deactivate Survey Modal -->
    <div class="modal fade confirmation-modal" id="deactivateModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-pause me-2"></i>Deactivate Survey
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <i class="fas fa-exclamation-triangle text-warning fa-3x mb-3"></i>
                    </div>
                    <h6>Are you sure you want to deactivate this survey?</h6>
                    <p class="text-muted mb-0">Students will no longer be able to take this survey.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" id="confirmDeactivate">
                        <i class="fas fa-pause me-2"></i>Deactivate Survey
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Survey Modal -->
    <div class="modal fade confirmation-modal" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-trash me-2"></i>Delete Survey
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <i class="fas fa-exclamation-circle text-danger fa-3x mb-3"></i>
                    </div>
                    <h6>Are you sure you want to delete this survey?</h6>
                    <p class="text-muted mb-3">This action cannot be undone and will permanently remove all survey data including responses.</p>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-warning me-2"></i>
                        <strong>Warning:</strong> All survey responses will be permanently lost!
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">
                        <i class="fas fa-trash me-2"></i>Delete Survey
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentEditingSurveyId = null;
        let currentSurveyId = null;
        let currentAction = null;

        // View survey preview
        function viewSurvey(surveyId) {
            fetch(`survey-preview.php?id=${surveyId}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('surveyPreviewContent').innerHTML = html;
                    currentEditingSurveyId = surveyId;
                    const modal = new bootstrap.Modal(document.getElementById('viewSurveyModal'));
                    modal.show();
                })
                .catch(error => {
                    console.error('Error loading survey preview:', error);
                    showAlert('Error loading survey preview. Please try again.', 'danger');
                });
        }

        // Edit current survey
        function editCurrentSurvey() {
            if (currentEditingSurveyId) {
                window.location.href = `edit-survey.php?id=${currentEditingSurveyId}`;
            }
        }

        // Survey status operations
        function changeSurveyStatus(surveyId, action) {
            currentSurveyId = surveyId;
            currentAction = action;

            if (action === 'activate') {
                const modal = new bootstrap.Modal(document.getElementById('activateModal'));
                modal.show();
            } else if (action === 'deactivate') {
                const modal = new bootstrap.Modal(document.getElementById('deactivateModal'));
                modal.show();
            }
        }

        function deleteSurvey(surveyId) {
            currentSurveyId = surveyId;
            currentAction = 'delete';
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        }

        // Confirmation handlers
        document.getElementById('confirmActivate').addEventListener('click', function() {
            submitSurveyAction();
        });

        document.getElementById('confirmDeactivate').addEventListener('click', function() {
            submitSurveyAction();
        });

        document.getElementById('confirmDelete').addEventListener('click', function() {
            submitSurveyAction();
        });

        function submitSurveyAction() {
            if (currentSurveyId && currentAction) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="${currentAction}">
                    <input type="hidden" name="survey_id" value="${currentSurveyId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Show alert function
        function showAlert(message, type = 'info') {
            const alertContainer = document.querySelector('.alert-container');
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            alertContainer.insertAdjacentHTML('beforeend', alertHtml);

            // Auto-hide after 5 seconds
            setTimeout(function() {
                const alerts = alertContainer.querySelectorAll('.alert');
                const lastAlert = alerts[alerts.length - 1];
                if (lastAlert) {
                    lastAlert.classList.remove('show');
                    setTimeout(() => lastAlert.remove(), 150);
                }
            }, 5000);
        }

        // Auto-hide existing alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    if (alert.classList.contains('show')) {
                        alert.classList.remove('show');
                        setTimeout(function() {
                            alert.remove();
                        }, 150);
                    }
                }, 5000);
            });

            // Mobile menu functionality
            const mobileMenuToggle = document.getElementById('mobileMenuToggle');
            const mobileOverlay = document.getElementById('mobileOverlay');
            const sidebar = document.querySelector('.sidebar');

            if (mobileMenuToggle && mobileOverlay && sidebar) {
                // Toggle mobile menu
                mobileMenuToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                    mobileOverlay.classList.toggle('show');
                    document.body.classList.toggle('sidebar-open', sidebar.classList.contains('show'));
                });

                // Close menu when clicking overlay
                mobileOverlay.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    mobileOverlay.classList.remove('show');
                    document.body.classList.remove('sidebar-open');
                });

                // Close menu when clicking nav links on mobile
                const navLinks = sidebar.querySelectorAll('.nav-link');
                navLinks.forEach(function(link) {
                    link.addEventListener('click', function() {
                        if (window.innerWidth <= 768) {
                            sidebar.classList.remove('show');
                            mobileOverlay.classList.remove('show');
                            document.body.classList.remove('sidebar-open');
                        }
                    });
                });

                // Close menu on resize to desktop
                window.addEventListener('resize', function() {
                    if (window.innerWidth > 768) {
                        sidebar.classList.remove('show');
                        mobileOverlay.classList.remove('show');
                        document.body.classList.remove('sidebar-open');
                    }
                });
            }
        });
    </script>
</body>
</html>