<?php
require_once __DIR__ . "/../include/auth.php";
require_once __DIR__ . "/../include/db.php";
require_role("student"); // Taken from auth.php, enforces that the role must be a student to access this page.
$userID = current_user_id();

$username = "Student"; // Default name incase no session name is available.
if (isset($_SESSION["username"])) // If the session contains a username, use it.
{
    $username = $_SESSION["username"];
}

// SQL query that takes the submission id, score and title in the database so that it can be refered to as quiz_title.
// is_read and created_at in the feedback table is taken so that it can be referred to as feedback_exists 
// fetches the logged in students' submission, and feedback.
$sqlSelectSubmission= "SELECT subm.submission_id, subm.user_id AS student_id, subm.score , subm.submitted_at, assess.title AS quiz_title, feedb.is_read, 
feedb.created_at AS feedback_created_at FROM submission subm 
JOIN assessment assess ON assess.assessment_id = subm.assessment_id LEFT JOIN feedback feedb ON feedb.submission_id = subm.submission_id
WHERE subm.user_id = :user_id ORDER BY assess.assessment_date ASC";

$statementSubmission = $pdo->prepare($sqlSelectSubmission);
$statementSubmission->execute([":user_id" => $userID]); // Takes :user_id placeholder and ensures rows are returned for only the user currently logged in.
$rowsSubmission = $statementSubmission->fetchAll();

// Store the chart variables as arrays to use when building the chart.js script, this is then converted to JSON format.
$chartLabels = [];
$chartData = [];

// Loop through each submission row and use the quiz title and score for the chart. (This format makes it easier to pass into chart.js).
foreach ($rowsSubmission as $chartRows)
{
$chartLabels[] =$chartRows["quiz_title"];
$chartData[] =(int)$chartRows["score"];
}
?>

<!DOCTYPE html>
<html> 
<head>
   <title> Student Dashboard </title> 
   <link rel="stylesheet" href="../css/styles.css">
   <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>

<!-- This is the student sidebar. It allows for navigation between pages. -->
<div class="dashboard_layout">
<aside class="dashboard_sidebar">
<h3 class="menu_text"> Navigation </h3>
<a class="dashboard_link" href="progress_overview.php"> Progress Overview </a>
<a class="dashboard_link" href="../logout.php"> Logout </a>
</aside>

<main class="dashboard_main_content">
    <p class="dashboard_welcome"> Welcome to your dashboard, <?php echo htmlspecialchars($username); ?>.  </p>
    <h2 class="result_quiz">Your Results and Quizzes</h2>
    <div class="card_dashboard">
    <table class="tbl_main">
    <tr>
        <th>Quiz Title</th>
        <th>Score (%)</th>
        <th>Risk Status</th>
        <th>Feedback Date</th>
        <th>Feedback Status </th>
        <th>Action</th>
    </tr>


<?php 
 if(empty($rowsSubmission))
{
    echo '<tr><td colspan="6">No submissions available.</td> </tr>';
}
else
    // For each row check it has feedback and is not null.
    // Used over a while loop as rows are array values given one at a time.
    // Each row has feedback_created_at and the row chekcs 
   foreach ($rowsSubmission as $row)
{
    $checkFeedback = ($row["feedback_created_at"] !== null);
    $isNewFeedback = ($checkFeedback && (int)$row["is_read"] === 0);
        // Track score from the row, casted as an integer to keep all values the same data type.
    $score = (int)$row["score"];

    $riskText = "On Track";
    $riskClass = "risk_ontrack";

    if ($score < 40) // If the score on the row has less than 40, mark the row as "At Risk" and apply the matching CSS class.
    {
        $riskText = "At Risk";
        $riskClass = "risk_atrisk";
    }

    $feedbackDate = "No Feedback Given"; // Fallback value used when no feedback has been given.
    if ($checkFeedback)
    {
        $feedbackDate = htmlspecialchars($row["feedback_created_at"]); 
    }
    // Default feedback status is that feedback is not available.
    $feedbackStatus = "Not Available";
    if ($isNewFeedback)
    {           
        $feedbackStatus = "New!"; // This will showcase every time feedback is overwritten or newly entered.
    }
    else if ($checkFeedback)
    {
        $feedbackStatus = "Feedback Available";
    }

    $actionHtml = '<span class="btn_feedback_disabled">No Feedback Given</span>'; 
    if ($checkFeedback)
    {
        $link = "view_feedback.php?submission_id=" . (int)$row["submission_id"]; // Get the submission ID of the clicked viewed feedback e.g. submission 1.
        $actionHtml = '<a class="btn_feedback" href="' . $link . '">View Feedback</a>'; 
    }

    echo "<tr>";
    echo "<td>" . htmlspecialchars($row["quiz_title"]) . "</td>"; 
    echo "<td class='dashboard_score'>" . htmlspecialchars($row["score"]) . "</td>"; // Score row.
    echo "<td class='" . $riskClass . "'>" . $riskText . "</td>"; // At Risk row, changes based on if score is less than 40 or not.
    echo "<td>" . $feedbackDate . "</td>";
    echo "<td>" . $feedbackStatus . "</td>"; 
    echo "<td>" . $actionHtml . "</td>"; 
    echo "</tr>";
}
?>
</table>
</div>
<h4 class="score_overview">Score/Mark Overview</h4>
<div class="card_dashboard"> 
<canvas id="score_student"> </canvas>
</div>
<!-- This is a script that uses chart.js to render the charts, the source is CDN.
This allows for charts to be used without the need to locally download them although internet is required. -->

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.5/dist/chart.umd.min.js"> </script>
<script>
 // similar in purpose to htmlspecialchars, json_encode is used to change PHP values to JSON format which is what Chart.js can read.
 const chartLabels = <?php echo json_encode($chartLabels); ?>; // chartLabel is an array converted to json.
 const chartScores = <?php echo json_encode($chartData); ?>; 

const context = document.getElementById("score_student").getContext("2d");
new Chart(context, 
{
type: "bar",
data: {
labels: chartLabels,
datasets: [{
    label: "Score (%)",
    data: chartScores,
     backgroundColor: 
            [
            "rgba(220, 38, 38, 0.7)", // Quiz 1 colour = red
            "rgba(37, 81, 151, 0.7)", // Quiz 2 colour = blue
            "rgba(220, 199, 11, 0.7)" // Quiz 3 colour = yellow
            ]    
}]
},
options: 
{
    responsive: true,
    maintainAspectRatio: false,
    
    scales: 
    {
        y: 
        {
            beginAtZero: true, max: 100
        }
    }
}
});
</script>
</main>
</div>
</body>
</html>