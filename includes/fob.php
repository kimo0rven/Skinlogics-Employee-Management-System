<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (isset($_POST["leave_request"])) {

        $sql = 'INSERT INTO leave_request (leave_type, start_date, end_date, reason, employee_id) VALUES (:leave_type, :start_date, :end_date, :reason, :employee_id)';
        $stmt_user = $pdo->prepare($sql);
        $stmt_user->execute([
            ':leave_type' => $_POST['leave_type'],
            ':start_date' => $_POST['start_date'],
            ':end_date' => $_POST['end_date'],
            ':reason' => $_POST['reason'],
            ':employee_id' => $_SESSION['employee_id']
        ]);
    }

    if (isset($_POST["overtime_request"])) {
        print_r($_POST["overtime_request"]);
    }
}
?>

<html>

<head>
    <link rel="stylesheet" href="style.css" />

</head>
<div>
    <div class="floating-wrapper" id="floatingWrapper">
        <div class="floating-container" id="mainMenu">
            <img src="assets/images/icons/menu-icon.png" alt="Menu Icon">
        </div>

        <div class="floating-sub-button" id="leaveRequestModalBtn">
            <img src="assets/images/icons/leave-request-icon2.png" alt="">
        </div>

        <div class="floating-sub-button" id="overtimeRequestModalBtn">
            <img src="assets/images/icons/overtime-icon.png" alt="">
        </div>
    </div>

    <dialog id="leaveRequestModal" class="font-medium">
        <form id="leaveForm" class="flex flex-column gap-20" method="POST" action="#">
            <div class="flex flex-column gap-20">
                <div class="flex flex-row flex-start">

                    <p class="font-bold">SUBMIT LEAVE REQUEST</p>

                    <div id="leaveRequestCloseBtn" style="position: absolute; top: 10px; right: 10px; cursor: pointer;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18 6L6 18" stroke="#333" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" />
                            <path d="M6 6L18 18" stroke="#333" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </div>
                </div>
                <div class="flex flex-row gap-20 space-around">
                    <div>
                        <label class="profile-label" for="leave_type">Type of Leave</label>
                        <select name="leave_type" id="">
                            <option value="Vacation">Vacation</option>
                            <option value="Sick">Sick</option>
                            <option value="Personal">Personal</option>
                            <option value="Emergency">Emergency</option>
                        </select>
                    </div>

                </div>

                <div id="dateError" class="font-medium" style="color: red; display: none;"></div>

                <div class="flex flex-row gap-20 space-between">
                    <div style="width:100%">
                        <label class="profile-label" for="start_date">Start Date</label>
                        <input class="leave_request_input" type="date" name="start_date" style="width:100%">
                    </div>

                    <div style="width:100%">
                        <label class="profile-label" for="end_date">End Date</label>
                        <input class="leave_request_input" type="date" name="end_date" style="width:100%">
                    </div>
                </div>

                <div class="flex flex-row gap-20 space-between">
                    <div>
                        <label class="profile-label" for="reason">Reason</label>
                        <textarea name="reason" id="leave_request_reason"
                            style="width:450px;border-radius: 8px; border: 1px solid var(--color-base-300);padding: 10px; height: 80%"></textarea>
                    </div>


                </div>
            </div>
            <div class="flex flex-row justify-center">
                <button type="submit" name="leave_request" style="padding: 10px 20px">Submit Request</button>
            </div>
        </form>
    </dialog>

    <dialog id="overtimeRequeustModal" class="font-medium">
        <form id="overtimeForm" class="flex flex-column gap-20" method="POST" action="#">
            <div class="flex flex-row space-between">
                <div>
                    <p class="font-bold">OVERTIME REQUEST</p>
                </div>
                <div id="overtimeRequestCloseBtn" style="position: absolute; top: 10px; right: 10px; cursor: pointer;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18 6L6 18" stroke="#333" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                        <path d="M6 6L18 18" stroke="#333" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </div>
            </div>

            <div class="flex flex-row gap-20 space-between">
                <div>
                    <label class="profile-label" for="shift_of_the_day">Shift of the Day</label>
                    <select name="shift_of_the_day" id="shift_of_the_day"
                        style="margin: 0; padding: 10px; width: 215px">
                        <option value="Morning">Morning</option>
                        <option value="Afternoon">Afternoon</option>
                        <option value="Night">Night</option>
                        <option value="Day Off">Day Off</option>
                    </select>
                </div>

                <div style="width:100%">
                    <label class="profile-label" for="overtime_date">Overtime Date</label>
                    <input type="date" id="overtime_date" name="overtime_date" class="leave_request_input"
                        style="width:100%">
                </div>

            </div>

            <div id=" dateError" class="font-medium" style="color: red; display: none;">
            </div>

            <div class="flex flex-row gap-20 space-between">
                <div style="width:100%">
                    <label class="profile-label" for="start_time">Start Time</label>
                    <input class="leave_request_input" type="time" name="start_time" style="width:100%">
                </div>

                <div style="width:100%">
                    <label class="profile-label" for="end_time">End Time</label>
                    <input class="leave_request_input" type="time" name="end_time" style="width:100%">
                </div>
            </div>

            <div class="flex flex-row gap-20 space-around">
                <div>
                    <label class="profile-label" for="overtime_type">Type of Overtime</label>
                    <select name="overtime_type" id="">
                        <option value="Weekday">Weekday</option>
                        <option value="Weekend">Weekend</option>
                        <option value="Holiday">Holiday</option>
                    </select>
                </div>

            </div>

            <div class="flex flex-row gap-20 space-between">
                <div>
                    <label class="profile-label" for="overtime_request_reason">Reason</label>
                    <textarea name="reason" id="overtime_request_reason"
                        style="width:450px;border-radius: 8px; border: 1px solid var(--color-base-300);padding: 10px; height: 80%"></textarea>
                </div>


            </div>

            <div class="flex flex-row justify-center">
                <button type="submit" name="overtime_request" style="padding: 10px 20px">Submit Request</button>
            </div>
        </form>
    </dialog>
</div>
<script>
    document.getElementById('mainMenu').addEventListener('click', function () {
        document.getElementById('floatingWrapper').classList.toggle('active');
    });

    const leaveRequestdialog = document.getElementById('leaveRequestModal');
    const openDialogBtn = document.getElementById('leaveRequestModalBtn');

    openDialogBtn.addEventListener('click', () => {
        if (leaveRequestdialog.showModal) {
            leaveRequestdialog.showModal();
        }
    });

    const closeLeaveRequestBtn = document.getElementById('leaveRequestCloseBtn');
    closeLeaveRequestBtn.addEventListener('click', () => {
        leaveRequestdialog.close();
    });

    document.addEventListener('DOMContentLoaded', () => {
        const startDateInput = document.querySelector('input[name="start_date"]');
        const endDateInput = document.querySelector('input[name="end_date"]');
        const errorDiv = document.getElementById('dateError');

        function checkDates() {
            errorDiv.textContent = '';

            if (!startDateInput.value || !endDateInput.value) {
                errorDiv.style.display = 'none';
                return;
            }

            const startDate = new Date(startDateInput.value);
            const endDate = new Date(endDateInput.value);

            if (startDate > endDate) {
                errorDiv.textContent = "Start date cannot be later than End date.";
                errorDiv.style.display = 'block';
            } else {
                errorDiv.style.display = 'none';
            }
        }

        startDateInput.addEventListener('change', checkDates);
        endDateInput.addEventListener('change', checkDates);
    });

    const overtimeRequestDialog = document.getElementById('overtimeRequeustModal');
    const openOvertimeDialogBtn = document.getElementById('overtimeRequestModalBtn');

    openOvertimeDialogBtn.addEventListener('click', () => {
        if (overtimeRequestDialog.showModal) {
            overtimeRequestDialog.showModal();
        }
    });

    const closeOvertimeRequestBtn = document.getElementById('overtimeRequestCloseBtn');
    closeOvertimeRequestBtn.addEventListener('click', () => {
        overtimeRequestDialog.close();
    })
</script>

</html>