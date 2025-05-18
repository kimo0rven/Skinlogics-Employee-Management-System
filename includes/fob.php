<html>

<head>
    <link rel="stylesheet" href="style.css" />
</head>

<body>
    <div class="floating-wrapper" id="floatingWrapper">
        <div class="floating-container" id="mainMenu">
            <img src="assets/images/icons/menu-icon.png" alt="Menu Icon">
        </div>

        <div class="floating-sub-container">
            <div class="floating-sub-button" id="leaveRequestModalBtn">
                <img src="assets/images/icons/leave-request-icon.png" alt="">
            </div>
        </div>

        <div class="floating-sub-container">
            <div class="floating-sub-button" id="overtimeRequestModalBtn">
                <img src="assets/images/icons/overtime-icon.png" alt="">
            </div>
        </div>
    </div>


    <dialog id="leaveRequestModal" class="font-medium">
        <div id="leaveSuccessMessage" class="success-message"
            style="display: none; text-align: center; color: green; margin-top: 10px;">
            <span id="leaveSubmissionMessage"></span>
        </div>
        <form id="leaveForm" class="flex flex-column gap-20" method="POST" action="includes/handle_leave_request.php">
            <div class="flex flex-column gap-20">
                <div class="flex flex-row flex-start">

                    <p class="font-bold">FILE A LEAVE REQUEST</p>

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
                        <select name="leave_type" id="leave_type">
                            <option value="Vacation" selected>Vacation</option>
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
        <div id="overtimeSuccessMessage" class="success-message"
            style="display: none; text-align: center; color: green; margin-top: 10px;">
            <span id="submissionMessage"></span>
        </div>
        <form id="overtimeForm" class="flex flex-column gap-20" method="POST"
            action="includes/handle_overtime_request.php">
            <div class="flex flex-row space-between">
                <div>
                    <p class="font-bold">FILE AN OVERTIME REQUEST</p>
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
                <div style="width:100%">
                    <label class="profile-label" for="ot_type">Overtime Type</label>
                    <select name="ot_type" id="ot_type" style="margin: 0; padding: 10px;">
                        <option value="Morning" selected>Weekday</option>
                        <option value="Afternoon">Weekend</option>
                        <option value="Night">Holiday</option>
                    </select>
                </div>

                <!-- <div style="width:100%">
                    <label class="profile-label" for="overtime_date">Overtime Date</label>
                    <input type="date" id="overtime_date" name="overtime_date" class="leave_request_input"
                        style="width:100%">
                </div> -->
            </div>

            <div id="timeError" class="font-medium" style="color: red; display: none;"></div>
            <div id="timeError" style="display: none;color: red; font-weight: bold; margin-top: 10px;"></div>
            <div class="flex flex-row gap-20 space-between">
                <div style="width:100%">
                    <label class="profile-label" for="start_time">Start Time</label>
                    <input class="leave_request_input" type="datetime-local" id="start_time" name="start_time"
                        style="width:100%">
                </div>
                <div style="width:100%">
                    <label class="profile-label" for="end_time">End Time</label>
                    <input class="leave_request_input" type="datetime-local" id="end_time" name="end_time"
                        style="width:100%">
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

</body>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const getById = id => document.getElementById(id);
        const qs = selector => document.querySelector(selector);

        getById("mainMenu")?.addEventListener("click", () =>
            getById("floatingWrapper")?.classList.toggle("active")
        );

        const leaveDialog = getById("leaveRequestModal");
        const openLeaveBtn = getById("leaveRequestModalBtn");
        const closeLeaveBtn = getById("leaveRequestCloseBtn");
        openLeaveBtn && leaveDialog && openLeaveBtn.addEventListener("click", () => leaveDialog.showModal?.());
        closeLeaveBtn && leaveDialog && closeLeaveBtn.addEventListener("click", () => leaveDialog.close());

        const overtimeDialog = getById("overtimeRequeustModal");
        const openOvertimeBtn = getById("overtimeRequestModalBtn");
        const closeOvertimeBtn = getById("overtimeRequestCloseBtn");
        openOvertimeBtn && overtimeDialog && openOvertimeBtn.addEventListener("click", () => overtimeDialog.showModal?.());
        closeOvertimeBtn && overtimeDialog && closeOvertimeBtn.addEventListener("click", () => overtimeDialog.close());

        const startDateInput = qs('input[name="start_date"]');
        const endDateInput = qs('input[name="end_date"]');
        const dateError = getById("dateError");
        const checkDates = () => {
            if (!startDateInput || !endDateInput || !dateError) return false;
            dateError.textContent = "";
            if (!startDateInput.value || !endDateInput.value) {
                dateError.style.display = "none";
                return false;
            }
            const start = new Date(startDateInput.value),
                end = new Date(endDateInput.value);
            if (start > end) {
                dateError.textContent = "Start date cannot be later than End date.";
                dateError.style.display = "block";
                return true;
            }
            dateError.style.display = "none";
            return false;
        };
        startDateInput?.addEventListener("change", checkDates);
        endDateInput?.addEventListener("change", checkDates);

        const startTimeInput = getById("start_time");
        const endTimeInput = getById("end_time");
        const timeError = getById("timeError");
        const validateDateTime = () => {
            if (!startTimeInput || !endTimeInput || !timeError) return false;
            timeError.textContent = "";
            timeError.style.display = "none";
            if (startTimeInput.value && endTimeInput.value) {
                const startDT = new Date(startTimeInput.value),
                    endDT = new Date(endTimeInput.value);
                if (endDT < startDT) {
                    timeError.textContent = "End date/time cannot be earlier than start date/time.";
                    timeError.style.display = "block";
                    return true;
                }
            }
            return false;
        };
        startTimeInput?.addEventListener("input", validateDateTime);
        endTimeInput?.addEventListener("input", validateDateTime);

        const form = getById("overtimeForm");
        form && form.addEventListener("submit", e => {
            e.preventDefault();
            if (checkDates() || validateDateTime()) return;
            const formData = new FormData(form);
            fetch(form.action, { method: "POST", body: formData })
                .then(res => res.json())
                .then(result => {
                    getById("submissionMessage").innerText = result.message;
                    getById("overtimeSuccessMessage").style.display = "block";
                    form.reset();
                    overtimeDialog && setTimeout(() => typeof overtimeDialog.close == "function" ? overtimeDialog.close() : overtimeDialog.style.display = "none", 5000);
                })
                .catch(err => console.error("Error submitting the form:", err));
        });

        if (getById("leaveForm")) {
            const leaveForm = getById("leaveForm");
            const leaveStartInput = qs('input[name="start_date"]', leaveForm);
            const leaveEndInput = qs('input[name="end_date"]', leaveForm);
            const leaveDateError = qs('#dateError', leaveForm);

            const checkLeaveDates = () => {
                if (!leaveStartInput || !leaveEndInput || !leaveDateError) return false;
                leaveDateError.textContent = "";
                if (!leaveStartInput.value || !leaveEndInput.value) {
                    leaveDateError.style.display = "none";
                    return false;
                }
                const start = new Date(leaveStartInput.value);
                const end = new Date(leaveEndInput.value);
                if (start > end) {
                    leaveDateError.textContent = "Start date cannot be later than End date.";
                    leaveDateError.style.display = "block";
                    return true;
                }
                leaveDateError.style.display = "none";
                return false;
            };

            leaveStartInput?.addEventListener("change", checkLeaveDates);
            leaveEndInput?.addEventListener("change", checkLeaveDates);

            leaveForm.addEventListener("submit", e => {
                e.preventDefault();
                if (checkLeaveDates()) return;
                const formData = new FormData(leaveForm);
                fetch(leaveForm.action, { method: "POST", body: formData })
                    .then(res => res.json())
                    .then(result => {
                        getById("leaveSubmissionMessage").innerText = result.message;
                        getById("leaveSuccessMessage").style.display = "block";
                        leaveForm.reset();
                        leaveDialog && setTimeout(() => typeof leaveDialog.close == "function" ? leaveDialog.close() : leaveDialog.style.display = "none", 5000);

                    })
                    .catch(err => console.error("Error submitting the leave form:", err));
            });
        }
    });
</script>





</html>