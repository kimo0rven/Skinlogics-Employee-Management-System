<?php
session_start();

if (empty($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit();
}

include 'includes/database.php';
include 'config.php';

$currentUser;
try {
    $sql = '
    SELECT
        e.*,
        j.*,
        d.*,
        ua.avatar
    FROM
        employee e
    INNER JOIN
        job j ON e.job_id = j.job_id
    INNER JOIN
        department d ON j.department_id = d.department_id
    INNER JOIN
        user_account ua ON e.user_account_id = ua.user_account_id
    WHERE
        e.user_account_id = :user_account_id';
    $stmt_employees = $pdo->prepare($sql);
    $stmt_employees->execute([':user_account_id' => $_SESSION['user_account_id']]);
    $currentUser = $stmt_employees->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo $e->getMessage();
}
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : 'Admin'; ?> | Profile</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="profile.css" />
    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">
</head>

<body class="font-medium">
    <div id="admin">
        <div class="dashboard-background">
            <div class="dashboard-container">
                <div class="dashboard-navigation">
                    <?php include('includes/navigation.php') ?>
                </div>
                <div class="dashboard-content">
                    <div class="dashboard-content-item1">
                        <div class="dashboard-content-header font-black">
                            <h1>PROFILE</h1>
                        </div>
                        <div id="logout-admin" class="dashboard-content-header font-medium">
                            <?php include('includes/header-avatar.php') ?>
                        </div>
                    </div>

                    <div class="profile-main-content">
                        <div class="profile-display profile-top-details">
                            <div class="profile-top-detail-avatar">
                                <div><img src="assets/images/avatars/<?php echo $currentUser['avatar'] ?>" alt=""></div>
                                <div>
                                    <p class="font-bold profile-full-name">
                                        <?php echo $currentUser['first_name'] . " " . $currentUser['last_name']; ?>
                                    </p>
                                    <p class="profile-job-name"><?php echo $currentUser['job_name'] ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="profile-display">
                            <div class="tabset">
                                <input type="radio" name="tabset" id="tab1" aria-controls="personal_information"
                                    checked>
                                <label for="tab1">Personal Information</label>
                                <input type="radio" name="tabset" id="tab3" aria-controls="contact_information">
                                <label for="tab3">Contact Information</label>
                                <input type="radio" name="tabset" id="tab4" aria-controls="government_agencies">
                                <label for="tab4">Government Agencies</label>

                                <div class="tab-panels">

                                    <section id="personal_information" class="tab-panel">
                                        <div class="flex flex-row space-between">
                                            <div class="font-black">About Me</div>
                                        </div>
                                        <div class="flex flex-rowflex-start space-between flex-wrap">
                                            <div class="profile-detail-fields">
                                                <p>Full Name:
                                                    <?php echo $currentUser['first_name'] . " " . $currentUser['middle_name'] . " " . $currentUser['last_name']; ?>
                                                </p>
                                            </div>
                                            <div class="profile-detail-fields">
                                                <p>Mobile: <?php echo $currentUser['mobile']; ?></p>
                                            </div>
                                            <div class="profile-detail-fields">
                                                <p>Email: <?php echo $currentUser['email']; ?></p>
                                            </div>
                                            <div class="profile-detail-fields">
                                                <p>Gender: <?php echo $currentUser['gender']; ?></p>
                                            </div>
                                            <div class="profile-detail-fields">
                                                <p>Date of Birth: <?php echo $currentUser['dob']; ?></p>
                                            </div>
                                            <div class="profile-detail-fields">
                                                <p>Civil Status: <?php echo $currentUser['civil_status']; ?></p>
                                            </div>
                                            <div class="profile-detail-fields">
                                                <p>Street: <?php echo $currentUser['street']; ?></p>
                                            </div>
                                            <div class="profile-detail-fields">
                                                <p>Barangay: <?php echo $currentUser['barangay']; ?></p>
                                            </div>
                                            <div class="profile-detail-fields">
                                                <p>City: <?php echo $currentUser['city']; ?></p>
                                            </div>
                                            <div class="profile-detail-fields">
                                                <p>Province: <?php echo $currentUser['province']; ?></p>
                                            </div>
                                        </div>
                                    </section>

                                    <section id="contact_information" class="tab-panel">
                                        <h2>Contact Information</h2>
                                        <div class="flex flex-rowflex-start space-between flex-wrap">
                                            <div class="profile-detail-fields">
                                                <p>Contact Person: <?php echo $currentUser['emergency_contact_name']; ?>
                                                </p>
                                            </div>
                                            <div class="profile-detail-fields">
                                                <p>Contact Number:
                                                    <?php echo $currentUser['emergency_contact_number']; ?>
                                                </p>
                                            </div>
                                            <div class="profile-detail-fields">
                                                <p>Contact Relationship:
                                                    <?php echo $currentUser['emergency_contact_relationship']; ?>
                                                </p>
                                            </div>
                                        </div>
                                    </section>

                                    <section id="government_agencies" class="tab-panel">
                                        <h2>Government Agencies</h2>
                                        <div class="flex flex-rowflex-start space-between flex-wrap">
                                            <div class="profile-detail-fields">
                                                <p>SSS Number: <?php echo $currentUser['sss_number']; ?></p>
                                            </div>
                                            <div class="profile-detail-fields">
                                                <p>Pag-IBIG Number: <?php echo $currentUser['pagibig_number']; ?></p>
                                            </div>
                                            <div class="profile-detail-fields">
                                                <p>PhilHealth Number: <?php echo $currentUser['philhealth_number']; ?>
                                                </p>
                                            </div>
                                            <div class="profile-detail-fields">
                                                <p>TIN: <?php echo $currentUser['tin_number']; ?></p>
                                            </div>
                                        </div>
                                    </section>

                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</body>