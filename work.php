<?php
session_start();

if (empty($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

?>

<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : 'Admin'; ?> | Employees</title>
    <link rel="stylesheet" href="style.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.min.js"></script>
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
                            <h1>Job & </h1>
                        </div>
                        <div id="logout-admin" class="dashboard-content-header font-medium">
                            <?php include('includes/header-avatar.php') ?>
                        </div>
                    </div>

                    <div class="employee-main-content" style="background: none">

                        <div class="tabset">
                            <input type="radio" name="tabset" id="tab1" aria-controls="personal_information" checked>
                            <label for="tab1">Active Jobs</label>
                            <input type="radio" name="tabset" id="tab2" aria-controls="rauchbier">
                            <label for="tab2">Inactive Jobs</label>
                            <input type="radio" name="tabset" id="tab3" aria-controls="dunkles">
                            <label for="tab3">Active Departments</label>
                            <input type="radio" name="tabset" id="tab4" aria-controls="emergency">
                            <label for="tab4">Inactive Team Leaders</label>


                            <div class="tab-panels">
                                <section id="personal_information" class="tab-panel"
                                    style="height: 100%;min-height: calc(100vh - 36vh);">
                                    <div class="flex flex-column gap-20">
                                        <div class="border-red" style="flex: 0 0 20%;">Header</div>
                                        <div>Body</div>
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

</body>

</html>