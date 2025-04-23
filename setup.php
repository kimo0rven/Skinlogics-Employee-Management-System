<?php
session_start();
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {

    include 'includes/database.php';
    include 'config.php';

    $sql = 'SELECT first_name, last_name, dob, gender, email, mobile FROM employee WHERE user_account_id = :user_account_id';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":user_account_id", $_SESSION['user_account_id']);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($rows)) {
        foreach ($rows as $row) {
            extract($row);
        }
    }

    $dateObject = DateTime::createFromFormat('Y-m-d', $dob);
    $year = $dateObject->format('Y');
    $monthNum = $dateObject->format('m');
    $monthName = $dateObject->format('F');
    $day = $dateObject->format('d');

    if (isset($_POST['setup'])) {
        echo $_POST['first_name'];
    }
} else {
    header('Location: index.php');
    exit;
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> | Setup Account</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">
</head>

<body>
    <div class="setup-background">
        <div class="setup-container font-medium">
            <div class="setup-container-header ">
                <div>
                    <!-- <h1 class="font-bold">Hello <?php echo $_SESSION['username'] ?>,</h1> -->
                    <h1 class="font-bold">Hello!</h1>

                </div>
                <div>
                    <a id="logout" href="/logout.php" style="font-size: 16px;">logout</a>
                </div>
            </div>
            <p>Please fill up this form to continue with your onboarding </p>

            <div>
                <form id="setupForm" action="/test.php" method="POST" enctype="multipart/form-data">
                    <div class="tab">
                        <p class="font-medium">Name</p>
                        <input placeholder="First name..." name="first_name" value="<?php echo $first_name; ?>"
                            oninput="this.className = ''">
                        <input placeholder="Middle name..." data-optional="true" name="middle_name"
                            oninput="this.className = ''">
                        <input placeholder="Last name..." name="last_name" value="<?php echo $last_name; ?>"
                            oninput="this.className = ''">
                    </div>

                    <div class="tab">
                        <p class="font-medium">Date of Birth</p>
                        <input placeholder="Day" name="dob_day" value="<?php echo $day; ?>" type="number"
                            oninput="this.className = ''">

                        <select placeholder="Month" name="dob_month" oninput="this.className = ''">
                            <option value="" disabled>Month</option>
                            <option value="01" <?php if ($monthNum == '01')
                                echo 'selected'; ?>>January</option>
                            <option value="02" <?php if ($monthNum == '02')
                                echo 'selected'; ?>>February</option>
                            <option value="03" <?php if ($monthNum == '03')
                                echo 'selected'; ?>>March</option>
                            <option value="04" <?php if ($monthNum == '04')
                                echo 'selected'; ?>>April</option>
                            <option value="05" <?php if ($monthNum == '05')
                                echo 'selected'; ?>>May</option>
                            <option value="06" <?php if ($monthNum == '06')
                                echo 'selected'; ?>>June</option>
                            <option value="07" <?php if ($monthNum == '07')
                                echo 'selected'; ?>>July</option>
                            <option value="08" <?php if ($monthNum == '08')
                                echo 'selected'; ?>>August</option>
                            <option value="09" <?php if ($monthNum == '09')
                                echo 'selected'; ?>>September</option>
                            <option value="10" <?php if ($monthNum == '10')
                                echo 'selected'; ?>>October</option>
                            <option value="11" <?php if ($monthNum == '11')
                                echo 'selected'; ?>>November</option>
                            <option value="12" <?php if ($monthNum == '12')
                                echo 'selected'; ?>>December</option>
                        </select>

                        <select placeholder="Year" name="dob_year" oninput="this.className = ''">
                            <option value="">Year</option>
                            <?php if (!empty($year)) { ?>
                                <option value="<?php echo $year; ?>" selected><?php echo $year; ?></option>
                            <?php } ?>
                            <script>
                                const currentYear = new Date().getFullYear();
                                for (let i = currentYear; i >= currentYear - 100; i--) {
                                    document.write(`<option value="${i}">${i}</option>`);
                                }
                            </script>
                        </select>

                    </div>

                    <div class="tab">Address
                        <input placeholder="Street" name="street" value="123" oninput="this.className = ''">
                        <input placeholder="Barangay" name="barangay" value="123" oninput="this.className = ''">
                        <input placeholder="City" name="city" value="123" oninput="this.className = ''">
                        <input placeholder="Province" name="province" value="123" oninput="this.className = ''">
                    </div>

                    <div class="tab">Other Info

                        <select placeholder="Gender" id="genderSelect" name="gender" onchange="toggleOtherInput()">
                            <option value="" disabled <?php if (empty($gender))
                                echo 'selected'; ?>>Gender</option>
                            <option value="male" <?php if ($gender === 'male')
                                echo 'selected'; ?>>Male</option>
                            <option value="female" <?php if ($gender === 'female')
                                echo 'selected'; ?>>Female</option>
                            <option value="other" <?php if ($gender === 'other')
                                echo 'selected'; ?>>Other</option>
                        </select>

                        <select id="civil_status" name="civil_status">
                            <option value="" disabled selected>Civil Status</option>
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Widowed">Widowed</option>
                            <option value="Separated">Separated</option>
                        </select>
                        <style>
                        </style>
                        <input placeholder="Mobile Number" name="mobile" value="<?php echo $mobile; ?>"
                            oninput="this.className = ''">
                        <input placeholder="Email" name="email" value="<?php echo $email; ?>"
                            oninput="this.className = ''">
                    </div>

                    <div class="tab">Emergency Contact
                        <input placeholder="Name" name="emergency_contact_name" value="Jollibee"
                            oninput="this.className = ''">
                        <input placeholder="Mobile Number" name="emergency_contact_number" value="8700"
                            oninput="this.className = ''">
                        <input placeholder="Relationship" name="emergency_contact_relationship" value="Mother"
                            oninput="this.className = ''">
                    </div>

                    <div class="tab">Government Agencies
                        <input placeholder="SSS ID" name="SSS" value="" data-optional="true"
                            oninput="this.className = ''">
                        <input placeholder="Philhealth/Malasakit No. (If Applicable)" name="philhealth" value=""
                            data-optional="true" oninput="this.className = ''">
                        <input placeholder="Pag-ibig No. / HDMF" name="pagibig" value="" data-optional="true"
                            oninput="this.className = ''">
                        <input placeholder="Tin No." name="tin" value="" data-optional="true"
                            oninput="this.className = ''">
                    </div>

                    <div class="tab">Avatar

                        <input type="file" name="file_upload">
                        <p style="font-size: 12px">Kindly provide a digital photograph of yourself for our records.
                            Please ensure the image is of a professional standard</p>
                    </div>


                    <div class="setup-nav-buttons">
                        <div style="text-align: left;">
                            <button type="button" id="prevBtn" onclick="nextPrev(-1)">Previous</button>
                        </div>
                        <div id="submitBtnDiv" style="text-align: right;display: none">
                            <button type="submit" id="submitBtn" name="setup">Submit</button>
                        </div>
                        <div id="nextBtnDiv" style="text-align: right;">
                            <button type="button" id="nextBtn" onclick="nextPrev(1)">Next</button>
                        </div>
                    </div>

                </form>
                <div class="steps" style="text-align:center;margin-top:40px;">
                    <span class="step"></span>
                    <span class="step"></span>
                    <span class="step"></span>
                    <span class="step"></span>
                    <span class="step"></span>
                    <span class="step"></span>
                    <span class="step"></span>




                </div>
            </div>
        </div>
    </div>

</body>
<script>
    document.getElementById("logout").addEventListener("click", function () {
        console.log(1)
        window.location.href = "logout.php";
    });

    var currentTab = 0;
    showTab(currentTab);

    function showTab(n) {
        var x = document.getElementsByClassName("tab");
        for (let i = 0; i < x.length; i++) {
            x[i].style.display = "none";
        }
        x[n].style.display = "block";
        if (n == 0) {
            document.getElementById("prevBtn").style.display = "none";
        } else {
            document.getElementById("prevBtn").style.display = "inline";
        }
        if (n == (x.length - 1)) {
            document.getElementById("nextBtnDiv").style.display = "none";
            document.getElementById("submitBtnDiv").style.display = "block";
        } else {
            document.getElementById("nextBtnDiv").style.display = "block";
            document.getElementById("submitBtnDiv").style.display = "none";
        }
        fixStepIndicator(n);
    }

    function nextPrev(n) {
        var x = document.getElementsByClassName("tab");
        if (n == 1 && !validateForm()) return false;
        x[currentTab].style.display = "none";
        currentTab = currentTab + n;
        showTab(currentTab);
        console.log(currentTab)

    }

    function validateForm() {
        var x, y, i, valid = true;
        x = document.getElementsByClassName("tab");
        y = x[currentTab].getElementsByTagName("input");
        for (i = 0; i < y.length; i++) {
            if (!y[i].dataset.optional && y[i].value == "") {
                y[i].className += " invalid";
                valid = false;
            }
        }
        if (valid) {
            document.getElementsByClassName("step")[currentTab].className += " finish";
        }
        return valid;
    }

    function fixStepIndicator(n) {
        var i, x = document.getElementsByClassName("step");
        for (i = 0; i < x.length; i++) {
            x[i].className = x[i].className.replace(" active", "");
        }
        x[n].className += " active";
    }
</script>

</html>