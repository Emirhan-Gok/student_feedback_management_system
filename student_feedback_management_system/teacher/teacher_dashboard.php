<?php
require_once __DIR__ . "/../include/auth.php";
require __DIR__ . "/../include/db.php";

require_role("teacher"); // Taken from auth.php, enforces that the role must be a teacher to access this page.

// If a session is set ensure the username is defaulted to Teacher.
$username = "Teacher";
if (isset($_SESSION["username"]))
{
    $username = $_SESSION["username"];
}

//  Fetch all submissions with the student name and quiz title, ensure feedback has a time and date associated to it.
// LEFT JOIN feedback so that rows will still appear even if no feedback exists yet.
$sqlSelectSubmission = "SELECT subm.submission_id, subm.user_id AS student_id, subm.score, subm.submitted_at, user.username AS student_name,
assess.title AS quiz_title,
feedb.created_at AS feedback_created_at
FROM submission subm JOIN user ON user.user_id = subm.user_id
JOIN assessment assess ON assess.assessment_id = subm.assessment_id
LEFT JOIN feedback feedb ON feedb.submission_id = subm.submission_id
ORDER BY subm.submitted_at ASC";

// Use pdo to keep consistency and saftey throughout the system, helps in reducing potential mistakes when adding new features such as filtering quizzes.
$statementSubmission = $pdo -> prepare($sqlSelectSubmission);
$statementSubmission->execute();
$rowsSubmission = $statementSubmission->fetchAll();

// Calculate class average (AVG) score per quiz and group by assessment (quiz results)
// Supports teacher overview chart allowing them to see the average between students.
$sqlQuizAverage = "SELECT assess.assessment_id, assess.title AS quiz_title,
ROUND(AVG(subm.score), 1) AS average_score
FROM assessment assess LEFT JOIN submission subm ON subm.assessment_id = assess.assessment_id
GROUP BY assess.assessment_id, assess.title ORDER BY assess.assessment_id DESC";

$statementAvg = $pdo -> prepare($sqlQuizAverage);
$statementAvg->execute();
$rowsAvg = $statementAvg->fetchAll();

// Used to store query results into arrays for Chart.js (labels and averages for the chart.)
$chartLabels = [];
$chartData = [];

// Loop through each quiz treturned y the average score query. And then build two arrays for chart.js
foreach($rowsAvg as $rowAvg ) 
{
    $chartLabels[] = $rowAvg["quiz_title"];
 if ($rowAvg["average_score"] !== null)
    {
        $chartData[] = (float)$rowAvg["average_score"]; // Casted to a float, to keep values consistent throughout the chart.
    }
        else
         {
            $chartData[] = 0; // This is done so if a quizhas no verage yet, it uses 0 (so the chart still renders properly).
         }
}
?>
<!DOCTYPE html>
<html>
<head>
<title> Teacher Dashboard </title>
<link rel="stylesheet" href="../css/assets/bootstrap.min.css">
<link rel="stylesheet" href="../css/styles.css">
<link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>

<div class="container-fluid dashboard_layout">
    <div class="row">
        <aside class="col-3 col-lg-2 dashboard_sidebar">
            <h3 class="menu_text"> Navigation </h3>
            <a class="dashboard_link" href="teacher_dashboard.php"> Dashboard </a>
            <a class="dashboard_link" href="../logout.php"> Logout </a>
        </aside>

<main class ="col-9 col-lg-10 dashboard_main_content">
<p class="dashboard_welcome"> Welcome to your dashboard, <?php echo htmlspecialchars($username); ?>. </p>
<h2 class="result_quiz">Student Submissions </h2>
<div class ="card_dashboard">
<table class ="tbl_main">
        <tr>
            <th>Student</th>
            <th>Quiz</th>
            <th>Score (%)</th>
            <th>Risk Status</th>
            <th>Submitted</th>
            <th>Action</th>
        </tr>
<?php
     if(empty($rowsSubmission))
    {
    echo '<tr><td colspan="6">No submissions available.</td> </tr>';
    }
    else
        {      // For each row ensure the table has data selected from the SQL query e.g. student name, quiz title.
            foreach ($rowsSubmission as $row)
                {

                    $hasFeedback = false;
                    if ($row["feedback_created_at"] !== null)
                        {
                            $hasFeedback = true;
                        }
                            // Track score from the row, casted as an integer to keep all values the same data type.
                        $score = (int)$row["score"];
                        $riskText = "On Track";
                        $riskClass = "risk_ontrack";

                    if ($score < 40) // If the score on the row has less than 40, change the text and class to represent this. e.g. red will now be the colour of the text.
                    {
                        $riskText = "At Risk";
                        $riskClass = "risk_atrisk";
                    }

                            // Create the rows for the teacher dashboard (uses SQL query data).
                            // Filter text vaues to prevent XSS e.g. student name/quiz having unexpected charcaters.
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row["student_name"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["quiz_title"]) . "</td>";
                            echo "<td class='dashboard_score'>" . htmlspecialchars($row["score"]) . "</td>";       
                            
                            echo "<td class='" . $riskClass . "'>" . $riskText . "</td>"; // Calculated from the score, if score is less than 40, risk text = At Risk.
                            
                            echo "<td>" . htmlspecialchars($row["submitted_at"]) . "</td>";

                            echo "<td>";
                            if ($hasFeedback)  // Add the current submission ID to the URL so enter_feedback knows which submission to load.
                                {             // Casted to an int to avoid accidental injection or malformed links.
                                 echo '<a class="btn_feedback" href="enter_feedback.php?submission_id=' . (int)$row["submission_id"] . '">Overwrite Feedback</a>';
                                }
                                else 
                                    {
                                    echo '<a class="btn_feedback" href="enter_feedback.php?submission_id='  . (int)$row["submission_id"] . '">Enter Feedback</a>';
                                    }
                                    
                            echo "</td>";
                            echo "</tr>";
                }
        }
?>
</table>
</div>
<h3 class="score_overview"> Class Average - Quizzes </h3>
<div class ="card_dashboard">
<div class="chart_wrap">
<canvas id="class_avg_chart"></canvas>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.5/dist/chart.umd.min.js"> </script>
<script>
 // similar in purpose to htmlspecialchars, json_encode is used to change PHP values to JSON format which is what Chart.js can read.
 const chartLabels = <?php echo json_encode($chartLabels); ?>; // chartLabels is an array converted to json.
 const averageValues = <?php echo json_encode($chartData); ?>; // PHP array used to get the averages from the sql query.

const context = document.getElementById("class_avg_chart").getContext("2d");
new Chart(context, 
{
type: "bar",
data: {
labels: chartLabels,
datasets: [{
    label: "Class Average (%)",
    data: averageValues,
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
    maintainAspectRatio: false, // Allows the chart to use CSS for the dimensions (width and height for chart).
                                // Helps keep UI consistent across screens.
    
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
</div>
</body>
</html>