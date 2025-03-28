<?php
session_start();
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {

    print_r($_SESSION);


    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['setup'])) {
        include 'includes/database.php';

        echo $_POST['setup'];

        $newFileName = '';

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 100 * 1024 * 1024;


        if (isset($_FILES['file_upload'])) {
            $file = $_FILES['file_upload'];

            if ($file['error'] === UPLOAD_ERR_OK) {

                $fileName = basename($file['name']);
                $fileTmpPath = $file['tmp_name'];
                $fileSize = $file['size'];
                $fileType = $file['type'];
                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                $uploadDir = 'assets/images/avatars/';


                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $newFileName = $_SESSION['user_account_id'] . '.' . $fileExt;
                $destPath = $uploadDir . $newFileName;

                if (in_array($fileType, $allowedTypes)) {
                    if ($fileSize <= $maxSize) {
                        if (move_uploaded_file($fileTmpPath, $destPath)) {
                            echo "File uploaded successfully to: " . $destPath;
                        } else {
                            echo "Error: Failed to move uploaded file.";
                        }
                    } else {
                        echo "Error: File too large. Maximum size is 2MB.";
                    }
                } else {
                    echo "Error: Invalid file type. Only JPG, PNG, GIF allowed.";
                }
            } else {

                switch ($file['error']) {
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        echo "Error: File too large.";
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        echo "Error: File only partially uploaded.";
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        echo "Error: No file was uploaded.";
                        break;
                    default:
                        echo "Error: Unknown upload error (Code: {$file['error']}).";
                }
            }
        } else {
            echo "Error: No file was submitted.";
        }


        $sql = "SELECT employee_id FROM employee WHERE user_account_id = :user_account_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":user_account_id", $_SESSION['user_account_id']);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);  // Use fetch() instead of fetchAll() since we expect one row

        if ($row) {
            $employee_id = $row['employee_id'];

            // Update user_account avatar
            $sql = "UPDATE user_account SET avatar = :avatar WHERE user_account_id = :user_account_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(":user_account_id", $_SESSION['user_account_id']);
            $stmt->bindParam(":avatar", $newFileName);
            $stmt->execute();

            // Format date of birth
            $dob = sprintf(
                "%04d-%02d-%02d",
                $_POST['dob_year'],
                $_POST['dob_month'],
                $_POST['dob_day']
            );

            // Update employee record
            $sql = "UPDATE employee 
            SET first_name = :first_name, 
                middle_name = :middle_name, 
                last_name = :last_name, 
                dob = :dob, 
                street = :street, 
                barangay = :barangay, 
                city = :city, 
                province = :province, 
                gender = :gender, 
                civil_status = :civil_status, 
                mobile = :mobile, 
                email = :email, 
                emergency_contact_name = :emergency_contact_name,
                emergency_contact_number = :emergency_contact_number, 
                emergency_contact_relationship = :emergency_contact_relationship, 
                sss_number = :sss_number, 
                philhealth_number = :philhealth_number, 
                pagibig_number = :pagibig_number, 
                tin_number = :tin_number,
                date_modified = NOW(),
                setup = 1,
                status = 'Active'
            WHERE employee_id = :employee_id";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(":employee_id", $employee_id, PDO::PARAM_INT);
            $stmt->bindParam(":first_name", $_POST['first_name'], PDO::PARAM_STR);
            $stmt->bindParam(":middle_name", $_POST['middle_name'], PDO::PARAM_STR);
            $stmt->bindParam(":last_name", $_POST['last_name'], PDO::PARAM_STR);
            $stmt->bindParam(":dob", $dob, PDO::PARAM_STR);
            $stmt->bindParam(":street", $_POST['street'], PDO::PARAM_STR);
            $stmt->bindParam(":barangay", $_POST['barangay'], PDO::PARAM_STR);
            $stmt->bindParam(":city", $_POST['city'], PDO::PARAM_STR);
            $stmt->bindParam(":province", $_POST['province'], PDO::PARAM_STR);
            $stmt->bindParam(":gender", $_POST['gender'], PDO::PARAM_STR);
            $stmt->bindParam(":civil_status", $_POST['civil_status'], PDO::PARAM_STR);
            $stmt->bindParam(":mobile", $_POST['mobile'], PDO::PARAM_STR);
            $stmt->bindParam(":email", $_POST['email'], PDO::PARAM_STR);
            $stmt->bindParam(":emergency_contact_name", $_POST['emergency_contact_name'], PDO::PARAM_STR);
            $stmt->bindParam(":emergency_contact_number", $_POST['emergency_contact_number'], PDO::PARAM_STR);
            $stmt->bindParam(":emergency_contact_relationship", $_POST['emergency_contact_relationship'], PDO::PARAM_STR);
            $stmt->bindParam(":sss_number", $_POST['SSS'], PDO::PARAM_STR);
            $stmt->bindParam(":philhealth_number", $_POST['philhealth'], PDO::PARAM_STR);
            $stmt->bindParam(":pagibig_number", $_POST['pagibig'], PDO::PARAM_STR);
            $stmt->bindParam(":tin_number", $_POST['tin'], PDO::PARAM_STR);
            $stmt->execute();
        }
        header('Location: dashboard.php');
        exit;

    } else {
        header('Location: index.php');
        exit;
    }
} else {
    header('Location: index.php');
    exit;
}
?>