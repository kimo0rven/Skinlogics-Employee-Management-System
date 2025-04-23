<?php
session_start();

if (empty($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
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
        department d ON e.department_id = d.department_id
    INNER JOIN 
        user_account ua ON e.user_account_id = ua.user_account_id
    WHERE 
        e.user_account_id = :user_account_id';
    $stmt_employees = $pdo->prepare($sql);
    $stmt_employees->execute([':user_account_id' => $_SESSION['user_account_id']]);
    $currentUser = $stmt_employees->fetch(PDO::FETCH_ASSOC);
    // print_r($currentUser);
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
                                        <?php echo $first_name . " " . $last_name; ?>
                                    </p>
                                    <p class="profile-job-name"><?php echo $currentUser['job_name'] ?>
                                    </p>
                                    <!-- <p><?php echo $currentUser['department_name'] ?></p>
                                    <p><?php echo $currentUser['branch'] ?></p> -->

                                </div>
                            </div>

                        </div>
                        <div class="profile-display">
                            <div class="tabset">
                                <input type="radio" name="tabset" id="tab1" aria-controls="personal_information"
                                    checked>
                                <label for="tab1">Personal Information</label>
                                <input type="radio" name="tabset" id="tab2" aria-controls="rauchbier">
                                <label for="tab2">Other Information</label>
                                <input type="radio" name="tabset" id="tab3" aria-controls="dunkles">
                                <label for="tab3">Emergency Contact</label>
                                <input type="radio" name="tabset" id="tab4" aria-controls="emergency">
                                <label for="tab4">Government Agencies</label>

                                <div class="tab-panels">
                                    <section id="personal_information" class="tab-panel">
                                        <div class="flex flex-row space-between">
                                            <div class="font-black">
                                                About Me
                                            </div>
                                            <div><button id="personal_information_edit_btn" style="padding: 10px 20px"
                                                    onclick="showHide()">Edit</button></div>
                                        </div>
                                        <div id="personal_information_display"
                                            class="flex flex-rowflex-start space-between flex-wrap">
                                            <div class="profile-detail-fields">
                                                <p>Full Name:
                                                    <?php echo $currentUser['first_name'] . " " . $currentUser['middle_name'] . " " . $currentUser['last_name'] ?>
                                                </p>
                                            </div>

                                            <div class="profile-detail-fields">
                                                <p>Mobile: <?php echo $currentUser['mobile'] ?></p>
                                            </div>

                                            <div class="profile-detail-fields">
                                                <p>Email: <?php echo $currentUser['email'] ?></p>
                                            </div>

                                            <div class="profile-detail-fields">
                                                <p>Gender: <?php echo $currentUser['gender'] ?></p>
                                            </div>

                                            <div class="profile-detail-fields">
                                                <p>Date of Birth: <?php echo $currentUser['dob'] ?></p>
                                            </div>

                                            <div class="profile-detail-fields">
                                                <p>Civil Status: <?php echo $currentUser['civil_status'] ?></p>
                                            </div>

                                            <div class="profile-detail-fields">
                                                <p>Street: <?php echo $currentUser['street'] ?></p>
                                            </div>

                                            <div class="profile-detail-fields">
                                                <p>Barangay: <?php echo $currentUser['barangay'] ?></p>
                                            </div>

                                            <div class="profile-detail-fields">
                                                <p>City: <?php echo $currentUser['city'] ?></p>
                                            </div>

                                            <div class="profile-detail-fields">
                                                <p>Province: <?php echo $currentUser['province'] ?></p>
                                            </div>

                                            <div class="profile-detail-fields">
                                                <p>Gender: <?php echo $currentUser['gender'] ?></p>
                                            </div>

                                        </div>

                                        <div id="personal_information_edit" style="display: none">
                                            <form action="" class="flex flex-rowflex-start
                                            space-between flex-wrap">
                                                <div class="profile-detail-fields">
                                                    <label class="profile-label" for="first_name">First Name</label>
                                                    <input type="text" id="firstName" name="first_name">
                                                </div>
                                                <div class="profile-detail-fields">
                                                    <label class="profile-label" for="middle_name">Middle Name</label>
                                                    <input type="text" id="middle_name" name="middle_name">
                                                </div>
                                                <div class="profile-detail-fields">
                                                    <label class="profile-label" for="last_name">Last Name</label>
                                                    <input type="text" id="last_name" name="last_name">
                                                </div>
                                                <div class="profile-detail-fields">
                                                    <label class="profile-label" for="email">Email</label>
                                                    <input type="email" id="email" name="email">
                                                </div>
                                                <div class="profile-detail-fields">
                                                    <label class="profile-label" for="phone">Phone</label>
                                                    <input type="tel" id="phone" name="phone">
                                                </div>
                                                <div class="profile-detail-fields">
                                                    <button type="submit">Save</button>
                                                    <button type="button" id="cancelEdit">Cancel</button>
                                                </div>
                                            </form>
                                        </div>
                                    </section>
                                    <section id="rauchbier" class="tab-panel">
                                        <h2>6B. Rauchbier</h2>
                                        <p><strong>Overall Impression:</strong> An elegant, malty German amber lager
                                            with a balanced, complementary beechwood smoke character. Toasty-rich malt
                                            in aroma and flavor, restrained bitterness, low to high smoke flavor, clean
                                            fermentation profile, and an attenuated finish are characteristic.</p>
                                        <p><strong>History:</strong> A historical specialty of the city of Bamberg, in
                                            the Franconian region of Bavaria in Germany. Beechwood-smoked malt is used
                                            to make a Märzen-style amber lager. The smoke character of the malt varies
                                            by maltster; some breweries produce their own smoked malt (rauchmalz).</p>
                                    </section>
                                    <section id="dunkles" class="tab-panel">
                                        <h2>6C. Dunkles Bock</h2>
                                        <p><strong>Overall Impression:</strong> A dark, strong, malty German lager beer
                                            that emphasizes the malty-rich and somewhat toasty qualities of continental
                                            malts without being sweet in the finish.</p>
                                        <p><strong>History:</strong> Originated in the Northern German city of Einbeck,
                                            which was a brewing center and popular exporter in the days of the Hanseatic
                                            League (14th to 17th century). Recreated in Munich starting in the 17th
                                            century. The name “bock” is based on a corruption of the name “Einbeck” in
                                            the Bavarian dialect, and was thus only used after the beer came to Munich.
                                            “Bock” also means “Ram” in German, and is often used in logos and
                                            advertisements.</p>
                                    </section>

                                    <section id="emergency" class="tab-panel">
                                        <form class="personal_information_form" action="">
                                            <div class="flex flex-row">
                                                <div><label for="sss_number">SSS</label>
                                                    <input type="sss_number">
                                                </div>
                                            </div>
                                        </form>
                                    </section>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        var edit_btn = document.getElementById('personal_information_edit_btn');

        function showHide() {
            var personalDisplay = document.getElementById('personal_information_display');
            var personalEdit = document.getElementById('personal_information_edit');


            if (personalDisplay.style.display === 'none') {
                personalDisplay.style.display = ''; // Or 'block', 'flex', etc., depending on the element's default display
                personalEdit.style.display = 'none';
            } else {
                personalDisplay.style.display = 'none';
                personalEdit.style.display = ''; // Or 'block', 'flex', etc.
            }
        }

    </script>

</body>