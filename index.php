<?php
include 'db_connection.php';

// Open database connection and retrieve categories
$sql = "SELECT id, name FROM categories";
$result = $pdo->query($sql);
$categories = $result->fetchAll(PDO::FETCH_ASSOC);

// Set default to the current month and year
$month = isset($_GET['month']) ? $_GET['month'] : date('m');
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');
$view = isset($_GET['view']) ? $_GET['view'] : 'month';
$week = isset($_GET['week']) ? $_GET['week'] : date('W');
$day = isset($_GET['day']) ? $_GET['day'] : date('j');

// Number of days in the month
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

// First day of the month
$firstDay = mktime(0, 0, 0, $month, 1, $year);

// Get month and weekday names
$monthName = date('F', $firstDay);
$dayOfWeek = date('N', $firstDay);
$currentWeek = date('W');

// Days of the week starting from Monday
$daysOfWeek = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];

// Check if the parameter 'msgEdite' is present in the URL
if (isset($_GET['msgEdite'])) {
    // Retrieve the message type (success or error)
    $msg = $_GET['msgEdite'];

    // Display the alert message based on the message type
    if ($msg === 'success') {
        // Display success message and redirect
        echo "<div class='alert success'>Appointment successfully updated.</div>";
    } else {
        // Display error message and redirect
        echo "<div class='alert error'>Error updating appointment.</div>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP calendar</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>

    <!-- Edit the appointment modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModalEdite()">&times;</span>
            <h2 style='text-align: center;'>Edit Appointment</h2>
            <form id="editForm" action="edit_appointment.php" method="POST" onsubmit="return validateFormEdit()">
                <input type="hidden" name="editId" id="editId">
                <div class="form-row">
                    <label for="editTitle">Title:</label><br>
                    <input type="text" name="editTitle" id="editTitle">&nbsp;(*)
                </div>
                <div class="form-row">
                    <label for="editDescription">Description:</label>
                    <textarea name="editDescription" id="editDescription"></textarea>
                </div>
                <div class="form-row">
                    <label for="editDate">Date:</label>
                    <input type="date" name="editDate" id="editDate">&nbsp;(*)
                </div>
                <div class="form-row">
                    <label for="end_date">End Date:</label>
                    <input type="date" name="end_date" id="end_dateEdite">
                </div>
                <div class="form-row">
                    <label>All-day Event:</label>
                    <input type="radio" name="is_full_dayEdite" class="is_full_day" id="isFullDayYes" value="1"> Yes
                    <input type="radio" name="is_full_dayEdite" class="is_full_day" id="isFullDayNo" value="0"> No
                </div>
                <div id="timeInputsEdite">
                    <div class="form-row">
                        <label for="editStartTime">Start Time:</label>
                        <input type="time" name="editStartTime" id="editStartTime">&nbsp;(*)
                    </div>
                    <div class="form-row">
                        <label for="editEndTime">End Time:</label>
                        <input type="time" name="editEndTime" id="editEndTime">&nbsp;(*)
                    </div>
                </div>
                <div class="form-row">
                    <label for="editCategory">Category:</label>
                    <select name="category_id" id="editCategory">
                        <?php foreach ($categories as $category) : ?>
                            <option value="<?= $category['id'] ?>"><?= $category['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-row">
                    <label></label>
                    <button type="submit" name="submit">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Appointment Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeAddModal()">&times;</span>
            <h2 style='text-align: center;'>Add Appointment</h2>
            <form id="addForm">
                <div class="form-row">
                    <label for="title">Title:</label>
                    <input type="text" name="title" id="title" required>&nbsp;(*)
                </div>
                <div class="form-row">
                    <label for="description">Description:</label>
                    <textarea name="description" id="description"></textarea>
                </div>
                <div class="form-row">
                    <label for="date">Date:</label>
                    <input type="date" name="date" id="date" required>&nbsp;(*)
                </div>
                <div class="form-row">
                    <label for="end_date">End Date:</label>
                    <input type="date" name="end_date" id="end_date">
                </div>
                <div class="form-row">
                    <label>All-day Event:</label>
                    <input type="radio" name="is_full_day" id="full_day_no" class="is_full_day" value="0" checked> No
                    <input type="radio" name="is_full_day" class="is_full_day" value="1"> Yes
                </div>
                <div id="timeInputs">
                    <div class="form-row">
                        <label for="start_time">Start Time:</label>
                        <input type="time" name="start_time" id="start_time">&nbsp;(*)
                    </div>
                    <div class="form-row">
                        <label for="end_time">End Time:</label>
                        <input type="time" name="end_time" id="end_time">&nbsp;(*)
                    </div>
                </div>
                <div class="form-row">
                    <label for="category">Category:</label>
                    <select name="category" id="category">
                        <?php foreach ($categories as $category) : ?>
                            <option value="<?= $category['id'] ?>"><?= $category['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-row">
                    <label></label>
                    <button type="submit" onclick="submitAddAppointment(event)">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Display Event Modal -->
    <div id="displayModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeDisplayEventsModel()">&times;</span>
            <h2 style='text-align: center;'>Display Event</h2>
            <div class="form-row">
                <label>Title:</label><br>
                <h4 id="displayTitle"></h4>
            </div>
            <div class="form-row" id="Description_display">
                <label>Description:</label>
                <h4 id="displayDescription"></h4>
            </div>
            <div class="form-row">
                <label>Date:</label>
                <h4 id="displayDate"></h4>
            </div>
            <div class="form-row" id="end_datedisplay">
                <label>End Date:</label>
                <h4 id="displayend_date"></h4>
            </div>
            <div class="form-row">
                <label>All-day Event:</label>
                <h4 id="showFullDay"></h4>
            </div>
            <div id="timeInputsdisplay">
                <div class="form-row">
                    <label>Start Time:</label>
                    <h4 id="displayStartTime"></h4>
                </div>
                <div class="form-row">
                    <label>End Time:</label>
                    <h4 id="displayEndTime"></h4>
                </div>
            </div>
            <div class="form-row">
                <label>Category:</label>
                <h4 id="displayCategory"></h4>
            </div>
        </div>
    </div>

    <div id="message" class="message"></div>

    <button onclick="openAddModal()" class="buttun"> <i class='bx bx-calendar-plus bx-tada bx-xs'></i> Add Appointment</button>

    <!-- PHP code to display the calendar -->
    <div class="calendar-container">

        <div class="navigation">
            <a href='?view=day&month=<?= date('m') ?>&year=<?= date('Y') ?>'> <i class='bx bx-filter'></i> Current day </a> &nbsp;
            <a href='?view=week&month=<?= date('m') ?>&year=<?= date('Y') ?>&week=<?= $currentWeek ?>'> <i class='bx bx-filter'></i> Current week</a>&nbsp;
            <a href='?view=month&month=<?= date('m') ?>&year=<?= date('Y') ?>'><i class='bx bx-filter'></i> Current month</a>
        </div>

        <?php if ($view == 'month') : ?>

            <!-- Navigation -->
            <div class="navigation">
                <a href='?month=<?= ($month == 1) ? 12 : $month - 1 ?>&year=<?= ($month == 1) ? $year - 1 : $year ?>'>
                    &lt; Previous</a>
                <h3 class='h3'><?= $monthName ?> <?= $year ?></h3>
                <a href='?month=<?= ($month == 12) ? 1 : $month + 1 ?>&year=<?= ($month == 12) ? $year + 1 : $year ?>'>Next
                    &gt;</a>
            </div>
            <!-- Calendar table -->
            <table class="tableM">
                <tr>
                    <?php foreach ($daysOfWeek as $day) : ?>
                        <!-- Day headings -->
                        <th><?= $day ?></th>
                    <?php endforeach; ?>
                </tr>

                <tr>
                    <?php
                    // Add cells for days before the first day of the month
                    for ($i = 1; $i < $dayOfWeek; $i++) {
                        echo "<td class='other-month'>" . (date('t', mktime(0, 0, 0, $month - 1, 1, $year)) - ($dayOfWeek - $i - 1)) . "</td>";
                    }

                    $dates = [];

                    // Iterate through each day of the month.
                    for ($day = 1; $day <= $daysInMonth; $day++) {

                        // Set class for the current day
                        $class = ($day == date('j') && $month == date('n') && $year == date('Y')) ? 'today' : '';
                        echo "<td class='date $class' style='width: 300px'>$day";

                        if (!empty($dates)) {
                            foreach ($dates as $date => $data) {
                                if (date('d', strtotime($date)) == $day) {
                                    foreach ($data as $entry) {
                                        $color = $entry['color'];
                                        $title = (strlen($entry['title']) > 18) ? substr($entry['title'], 0, 18) . '...' : $entry['title'];
                                        $time = $entry['time'];
                                        $id = $entry['id'];
                                        echo "<div class='appointment' style='background-color: $color;'>";
                                        echo "<span class='date Hovericon' onclick='DisplayEventsModel(" . $id . ")'>$title</span><br/>";
                                        echo "<div class='descriptionCurrEv'><span>$time [Multi-day event]</span>";

                                        echo "</div></div>";
                                    }
                                    unset($dates[$date]);
                                }
                            }
                        }

                        // Retrieve appointments for this day from the database
                        $appointments = [];
                        $sql = "SELECT a.*, c.*, a.id FROM appointments a LEFT JOIN categories c ON a.category_id = c.id WHERE YEAR(a.date) = $year AND MONTH(a.date) = $month AND DAY(a.date) = $day ORDER BY a.start_time";
                        $result = $pdo->query($sql);

                        if ($result->rowCount() > 0) {
                            // Display appointments
                            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                                // Check if it is an all-day event
                                $time = '';
                                $title = (strlen($row['title']) > 18) ? substr($row['title'], 0, 18) . '...' : $row['title'];
                                if ($row["is_full_day"] == 1) {
                                    // All-day event
                                    echo "<div class='appointment' style='background-color: " . $row["color"] . ";'>";
                                    echo "<span class='full-day Hovericon' onclick='DisplayEventsModel(" . $row["id"] . ")'>" . $title . "<br/></span> <div class='containerAll'> <span class='descriptionEv'> All-day</span>";
                                    $time = 'All-day';
                                } else {
                                    // Appointment with time
                                    echo "<div class='appointment' style='background-color: " . $row["color"] . ";'>";
                                    echo "<span class='timed Hovericon' onclick='DisplayEventsModel(" . $row["id"] . ")'>" . $title . " <br/> </span>";
                                    echo "<div class='containerAll'><span class='descriptionEv'>" . substr($row["start_time"], 0, 5);
                                    if (!empty($row["end_time"])) {
                                        // Display end time if it's not empty
                                        echo " - " . substr($row["end_time"], 0, 5);
                                    }
                                    $time = substr($row["start_time"], 0, 5) . " - " . substr($row["end_time"], 0, 5);
                                    echo "</span>";
                                }

                                // Check multiple days
                                if (!empty($row["end_date"]) && $row["end_date"] != $row["date"]) {
                                    // Highlight date cells from start date to end date
                                    $start = new DateTime($row["date"]);
                                    $end = new DateTime($row["end_date"]);

                                    // Iterate through each date in the range
                                    $currentDate = clone $start;
                                    $currentDate = $currentDate->modify('+1 day');
                                    while ($currentDate <= $end) {
                                        $dates[$currentDate->format('Y-m-d')][] = [
                                            'color' => $row["color"], 
                                            'title' => $row["title"], 
                                            'id' => $row["id"], 
                                            'time' => $time 
                                        ];
                                        $currentDate->modify('+1 day');
                                    }
                                }

                                // Add edit and delete icons
                                echo "<div class='iconsDV'><i class='bx bx-edit bx-sm Hovericon' style='margin-right: 7px;' onclick='openModalEdite(" . $row["id"] . ")'></i>";
                                echo "<i class='bx bx-trash bx-sm Hovericon' onclick='deleteAppointment(" . $row["id"] . ")'></i></div>";
                                echo "</div></div>";
                            }
                        }
                        echo "</td>";

                        // Start new row on Sunday
                        if (date('N', mktime(0, 0, 0, $month, $day, $year)) == 7) {
                            echo "</tr><tr>";
                        }
                    }

                    //  Add cells for days after the last day of the month
                    $lastDayOfWeek = date('N', mktime(0, 0, 0, $month, $daysInMonth, $year));
                    for ($i = $lastDayOfWeek + 1; $i <= 7; $i++) {
                        $nextMonthDay = $i - $lastDayOfWeek;
                        echo "<td class='other-month'>$nextMonthDay</td>";
                    }
                    ?>
                </tr>
            </table>

        <?php elseif ($view == 'week') : ?>

            <!-- Determine the weekday of the first day of the year-->
            <?php
            $firstDayOfYear = date('N', mktime(0, 0, 0, 1, 1, $year));
            $weekNumber = date('W', strtotime($year . '-01-01 +' . ($week - 1) . ' weeks'));

            // Determine the first day of the week
            $firstDayOfWeek = strtotime('+' . (($week - 1) * 7) . ' days', strtotime($year . '-01-01'));
            $firstDayOfWeek = date('M j', $firstDayOfWeek);

            // Determine the last day of the week
            $lastDayOfWeek = strtotime('+' . (6 + (($week - 1) * 7)) . ' days', strtotime($year . '-01-01'));
            $lastDayOfWeek = date('M j, Y', $lastDayOfWeek);
            ?>

            <!-- Navigation through the weeks -->
            <div class="navigation">
                <a href='?view=week&week=<?= $week - 1 ?>'>
                    < Previous week </a>&nbsp;&nbsp;
                        <h3> week <?= $weekNumber ?> :
                            <?= $firstDayOfWeek . " - " . $lastDayOfWeek ?></h3>
                        &nbsp;&nbsp;<a href='?view=week&week=<?= $week + 1 ?>'> Next week > </a>
            </div>

            <!-- Kalendertabelle -->
            <table>
                <tr>
                    <th>Time</th>
                    <?php foreach ($daysOfWeek as $day) : ?>
                        <th><?= $day ?></th>
                    <?php endforeach; ?>
                </tr>
                <!-- Rows for all-day events -->
                <tr>
                    <td style="font-weight: bold;width: 100px;">All day</td>
                    <?php for ($i = 1; $i <= 7; $i++) :
                        // Set date to the start of the current week
                        $startOfWeek = new DateTime();
                        $startOfWeek->setISODate($year, $week, $i);
                        $currentDate = $startOfWeek->format('Y-m-d');
                        $class = ($currentDate  == sprintf('%04d-%02d-%02d', date('Y'), date('n'), date('j'))) ? 'today' : '';
                    ?>
                        <td style='width: 170px;' class='date <?= $class ?>'>
                            <!-- Retrieve and display all-day events for this day -->
                            <?php

                            $sql = "SELECT a.*, c.* ,a.id FROM appointments a LEFT JOIN categories c ON a.category_id = c.id WHERE DATE(a.date) <= :currentDate AND (:currentDate <= DATE(a.end_date) OR a.end_date IS NULL) AND a.is_full_day = 1";
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindParam(':currentDate', $currentDate);
                            $stmt->execute();
                            $fullDayEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($fullDayEvents as $fullDayEvent) {
                                $title = (strlen($fullDayEvent['title']) > 18) ? substr($fullDayEvent['title'], 0, 18) . '...' : $fullDayEvent['title'];
                                echo "<div class='appointment' style='background-color: " . $fullDayEvent["color"] . ";'>";
                                echo "<span class='full-day Hovericon' onclick='DisplayEventsModel(" . $fullDayEvent["id"] . ")'>" . $title . "<br/></span> <div class='containerAll' style='margin-top: 16px;'> <span class='descriptionEv'> All day </span>";
                                if ($fullDayEvent['date'] == $currentDate) {
                                    echo "<div class='iconsDV'><i class='bx bx-edit bx-sm Hovericon' style='margin-right: 7px;' onclick='openModalEdite(" . $fullDayEvent["id"] . ")'></i>";
                                    echo "<i class='bx bx-trash bx-sm Hovericon' onclick='deleteAppointment(" . $fullDayEvent["id"] . ")'></i></div>";
                                    echo "</div></div>";
                                } else {
                                    echo "<div style='margin-top: -5px;'><span> &nbsp;&nbsp;[Multi-day event]</span></div>";
                                    echo "</div></div>";
                                }
                            }
                            ?>
                        </td>
                    <?php endfor; ?>
                </tr>
                <!-- Loop for hours -->
                <?php for ($hour = 0; $hour < 24; $hour++) : ?>
                    <tr>
                        <td style="width: 100px;"><?= str_pad($hour, 2, '0', STR_PAD_LEFT) ?>:00</td>
                        <?php for ($day = 1; $day <= 7; $day++) :
                            // Set date to the start of the current week
                            $startOfWeek = new DateTime();
                            $startOfWeek->setISODate($year, $week, $day);
                            $currentDate = $startOfWeek->format('Y-m-d');
                            $currentHour = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00:00';
                            $nextHour = str_pad($hour + 1, 2, '0', STR_PAD_LEFT) . ':00:00';
                            $class = ($currentDate  == sprintf('%04d-%02d-%02d', date('Y'), date('n'), date('j'))) ? 'today' : '';
                        ?>
                            <td style='width: 170px;' class='date <?= $class ?>'>
                                <!-- Retrieve and display events for this hour and this day -->
                                <?php
                                // SQL query to retrieve events for this hour and this day
                                $sql = "SELECT a.*, c.* ,a.id  FROM appointments a LEFT JOIN categories c ON a.category_id = c.id WHERE (a.date = :date OR (:date BETWEEN a.date AND a.end_date)) AND ((a.start_time <= :start_time AND a.end_time > :start_time) OR (a.start_time >= :start_time AND a.start_time < :next_hour)) ORDER BY a.start_time";
                                $stmt = $pdo->prepare($sql);
                                $stmt->bindParam(':date', $currentDate);
                                $stmt->bindParam(':start_time', $currentHour);
                                $stmt->bindParam(':next_hour', $nextHour);
                                $stmt->execute();

                                $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                // Display events for this hour and this day
                                foreach ($events as $event) {
                                    $title = (strlen($event['title']) > 18) ? substr($event['title'], 0, 18) . '...' : $event['title'];
                                    echo "<div class='appointment' style='background-color: " . $event["color"] . ";'>";
                                    echo "<span class='timed Hovericon' onclick='DisplayEventsModel(" . $event["id"] . ")'>" . $title . " <br/> </span>";
                                    echo "<div class='containerAll'><span class='descriptionEv'>" . substr($event["start_time"], 0, 5);
                                    if (!empty($event["end_time"])) {
                                        // Display end time if available
                                        echo " - " . substr($event["end_time"], 0, 5);
                                    }
                                    echo "</span>";
                                    if ($event['date'] == $currentDate) {
                                        echo "<div class='iconsDV'><i class='bx bx-edit bx-sm Hovericon' style='margin-right: 7px;' onclick='openModalEdite(" . $event["id"] . ")'></i>";
                                        echo "<i class='bx bx-trash bx-sm Hovericon' onclick='deleteAppointment(" . $event["id"] . ")'></i></div>";
                                        echo "</div></div>";
                                    } else {
                                        echo "<div style='margin-top: -5px;'><span> &nbsp;&nbsp;[Multi-day event]</span></div>";
                                        echo "</div></div>";
                                    }
                                }
                                ?>
                            </td>
                        <?php endfor; ?>
                    </tr>
                <?php endfor; ?>
            </table>

        <?php elseif ($view == 'day') : ?>
            <?php
            // Previous and next day calculations
            $prevDayTimestamp = strtotime("-1 day", mktime(0, 0, 0, $month, $day, $year));
            $nextDayTimestamp = strtotime("+1 day", mktime(0, 0, 0, $month, $day, $year));
            $prevDay = date('j', $prevDayTimestamp);
            $nextDay = date('j', $nextDayTimestamp);
            $currentDay = date('M j, Y', mktime(0, 0, 0, $month, $day, $year));
            $currentDate = sprintf('%04d-%02d-%02d', $year, $month, $day);

            // Calculate the day number in the year
            $dayOfYear = date('z', mktime(0, 0, 0, $month, $day, $year)) + 1;

            // Calculate the previous and next dates
            $prevDate = date('Y-m-d', strtotime('-1 day', strtotime($year . '-' . $month . '-' . $day)));
            $nextDate = date('Y-m-d', strtotime('+1 day', strtotime($year . '-' . $month . '-' . $day)));

            // Extract the year, month, and day from the previous and next dates
            $prevYear = date('Y', strtotime($prevDate));
            $prevMonth = date('m', strtotime($prevDate));
            $nextYear = date('Y', strtotime($nextDate));
            $nextMonth = date('m', strtotime($nextDate));

            $dayName = date('D', mktime(0, 0, 0, $month, $day, $year));
            ?>

            <div class="navigation">
                <a href='?view=day&day=<?= $prevDay ?>&month=<?= $prevMonth ?>&year=<?= $prevYear ?>'>
                    < Previous day</a>&nbsp;&nbsp;
                        <h3> Day <?= $dayOfYear ?> : <?= $dayName ?> - <?= $currentDay ?></h3>
                        &nbsp;&nbsp;<a href='?view=day&day=<?= $nextDay ?>&month=<?= $nextMonth ?>&year=<?= $nextYear ?>'>Next day ></a>
            </div>

            <!-- Calendar table -->
            <table>
                <tr>
                    <th>Time</th>
                    <th><?= $dayName ?></th>
                </tr>
                <!-- Row for all-day events -->
                <tr>
                    <td style='width: 240px;'>Ganztägig</td>
                    <?php
                    // Check if the current day is today to apply the corresponding CSS class
                    $class = ($year . '-' . $month . '-' . $day  == sprintf('%04d-%02d-%02d', date('Y'), date('n'), date('j'))) ? 'today' : '';
                    ?>
                    <td class='date <?= $class ?>'>
                        <!-- Retrieve and display all-day events for this day -->
                        <?php
                        $sql = "SELECT a.*, c.* ,a.id FROM appointments  a LEFT JOIN categories c ON a.category_id = c.id WHERE DATE(a.date) <= :date AND (DATE(a.end_date) >= :date OR a.end_date IS NULL) AND a.is_full_day = 1";
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(':date', "$year-$month-$day");
                        $stmt->execute();
                        $fullDayEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($fullDayEvents as $fullDayEvent) {
                            echo "<div class='appointment' style='background-color: " . $fullDayEvent["color"] . ";'>";
                            echo "<span class='full-day Hovericon' onclick='DisplayEventsModel(" . $fullDayEvent["id"] . ")'>" . $fullDayEvent['title'] . "<br/></span> <div class='containerAll' style='margin-top: 16px;'> <span class='descriptionEv'> All day </span>";
                            if ($fullDayEvent['date'] == $currentDate) {
                                echo "<div class='iconsDV' style='margin-right: 50px;'><i class='bx bx-edit bx-sm Hovericon' style='margin-right: 7px;' onclick='openModalEdite(" . $fullDayEvent["id"] . ")'></i>";
                                echo "<i class='bx bx-trash bx-sm Hovericon' onclick='deleteAppointment(" . $fullDayEvent["id"] . ")'></i></div>";
                                echo "</div></div>";
                            } else {
                                echo "<div class='iconsDV' style='margin-right: 50px;'><span> [Multi-day event]</span></div>";
                                echo "</div></div>";
                            }
                        }
                        ?>
                    </td>
                </tr>

                <?php for ($hour = 0; $hour < 24; $hour++) : ?>
                    <tr>
                        <td style='width: 240px;'><?= str_pad($hour, 2, '0', STR_PAD_LEFT) ?>:00</td>
                        <td class='date <?= $class ?>'>
                            <!-- Retrieve and display events for this hour and this day -->
                            <?php
                            $currentHour = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00:00';
                            $nextHour = str_pad($hour + 1, 2, '0', STR_PAD_LEFT) . ':00:00';

                            // Retrieve all events for this hour and this day
                            $sql = "SELECT a.*, c.* ,a.id FROM appointments a LEFT JOIN categories c ON a.category_id = c.id WHERE (a.date = :date OR (:date BETWEEN a.date AND a.end_date)) AND ((a.start_time <= :start_time AND a.end_time > :start_time) OR (a.start_time >= :start_time AND a.start_time < :next_hour)) ORDER BY a.start_time";
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':date', "$year-$month-$day");
                            $stmt->bindParam(':start_time', $currentHour);
                            $stmt->bindParam(':next_hour', $nextHour);
                            $stmt->execute();

                            $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($events as $event) {
                                echo "<div class='appointment' style='background-color: " . $event["color"] . ";'>";
                                echo "<span class='timed Hovericon' onclick='DisplayEventsModel(" . $event["id"] . ")'>" . $event['title'] . " <br/> </span>";
                                echo "<div class='containerAll'><span class='descriptionEv'>" . substr($event["start_time"], 0, 5);
                                if (!empty($event["end_time"])) {
                                    // Display end time if available
                                    echo " - " . substr($event["end_time"], 0, 5);
                                }
                                echo "</span>";
                                if ($event['date'] == $currentDate) {
                                    echo "<div class='iconsDV' style='margin-right: 50px;'><i class='bx bx-edit bx-sm Hovericon' style='margin-right: 7px;' onclick='openModalEdite(" . $event["id"] . ")'></i>";
                                    echo "<i class='bx bx-trash bx-sm Hovericon' onclick='deleteAppointment(" . $event["id"] . ")'></i></div>";
                                    echo "</div></div>";
                                } else {
                                    echo "<div class='iconsDV' style='margin-right: 50px;'><span> [Multi-day event]</span></div>";
                                    echo "</div></div>";
                                }
                            }
                            ?>
                        </td>
                    </tr>
                <?php endfor; ?>
            </table>

        <?php endif; ?>

    </div>

    <script defer type="text/javascript" src="js/script.js"></script>
</body>

</html>
