<?php
require_once __DIR__ . "/../include/auth.php";
require_once __DIR__ . "/../include/db.php";
require_role("student");
$userID = current_user_id();

// Fetch the student's quiz scores, quiz titles and order by the assessment ID (So the first submission appears first in the charts).
// This query is also used to populate in bar chart and line chart.
$sqlProgress = "SELECT assess.assessment_id, assess.title AS quiz_title, subm.score FROM submission subm 
    JOIN assessment assess ON assess.assessment_id = subm.assessment_id WHERE subm.user_id = :user_id
    ORDER BY assess.assessment_id ASC";

$statementProgress = $pdo->prepare($sqlProgress);
$statementProgress->execute([":user_id" => $userID]);
$rows = $statementProgress->fetchAll();

// These variables are used by Chart.js, labels = quiz title, scores are used to calculate weakest and total score amongst all quizzes (average mark). 
$chartLabels = [];
$chartScores = [];

$totalScore = 0;
$totalQuizzes = 0;

$weakestScore = null;
$weakestQuizTitle = "";
foreach ($rows as $submissionRow) 
{
    $title = $submissionRow["quiz_title"];
    $score = (int)$submissionRow["score"];

    // Keep scores within a valid range so the summary and charts stay consistent.
    if ($score < 0) 
        {
         $score = 0; 
        }
        else if ($score > 100) 
            { 
            $score = 100; 
            }

    $chartLabels[] = $title;
    $chartScores[] = $score;

    // Update the total score of each quiz and increment the quiz counter, this is so that the average mark can be calculated after the loop.
    $totalScore += $score; 
    $totalQuizzes++;

    // Tracking of the lowest score so that the weakest quiz can be identified.
    if ($weakestScore === null || $score < $weakestScore) 
    {
        $weakestScore = $score;
        $weakestQuizTitle = $title;
    }
}
 // Calculate the average mark across all quizzes and round to 1 decimal place for cleaner output.
$averageMark = 0;
if ($totalQuizzes > 0)  
{
    $averageMark = round($totalScore / $totalQuizzes, 1);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Progress Overview</title>
    <link rel="stylesheet" href="../css/assets/bootstrap.min.css">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/student.css">
</head>
<body>

<div class="container-fluid dashboard_layout">
    <div class="row">
    <aside class="col-3 col-lg-2 dashboard_sidebar">
        <h3 class="menu_text">Navigation</h3>
        <a class="dashboard_link" href="student_dashboard.php">Dashboard</a>
        <a class="dashboard_link" href="progress_overview.php">Progress Overview</a>
        <a class="dashboard_link" href="../logout.php">Logout</a>
    </aside>

    <main class="col-9 col-lg-10 student_main_content">
    <p class="dashboard_welcome">Progress Overview</p>
    <h2 class="result_quiz">Your Progress Overview</h2>

        <?php
        if (empty($rows)) // If the student has no submissions yet, show a simple fallback message instead of empty charts.
        {
            echo '<div class="card_student"><p>No submissions available yet.</p></div>';
        } 
        else 
        {
        ?>
       
        <div class="progress_bar_card"> <!-- Container for the bar chart section showing all quiz scores at once. -->
            <h3 class="score_overview">All Quiz Scores</h3>
            <div class="chart_bar">
                <canvas id="all_quiz_bar_chart"></canvas>
            </div>
        </div>
        
        <div class="progress_bottom_row">

            <div class="progress_score_summary">
                <h3 class="score_overview">Score Summary</h3>

                <?php
                foreach ($rows as $submissionRow) // Output each quiz title and score for simple text summary (used for marks summary).
                {
                    $title = $submissionRow["quiz_title"];
                    $score = (int)$submissionRow["score"];

                    if ($score < 0) // Keep the displayed score consistent with the same 0–100 limits used in the charts.
                    { 
                       $score = 0; 
                    }
                        else if ($score > 100) 
                        {
                                $score = 100; 
                        }

                    echo "<p>" . htmlspecialchars($title) . ": " . htmlspecialchars((string)$score) . "</p>";
                }
                ?>

                <p>Average mark: <?php echo htmlspecialchars((string)$averageMark); ?>%</p>
                <p>Weakest quiz: <?php echo htmlspecialchars($weakestQuizTitle); ?></p>
            </div>

            <div class="progress_line_card"> <!-- Container for the line chart used to show score changes across quizzes and for styling (CSS). -->
                <h3 class="score_overview">Score Trend</h3>
                <div class="chart_line">
                    <canvas id="all_quiz_line_chart"></canvas>
                </div>
            </div>

        </div>

        <?php } ?>

    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.5/dist/chart.umd.min.js"></script>
<script>
const chartLabels = <?php echo json_encode($chartLabels); ?>;
const chartScores = <?php echo json_encode($chartScores); ?>;

// Bar chart, so the student can compare scores across all quizzes more easily.
new Chart(document.getElementById("all_quiz_bar_chart").getContext("2d"), 
{
    type: "bar",
    data: 
    {
        labels: chartLabels,
        datasets: 
        [{
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
            y: { beginAtZero: true, max: 100 }
        }
    }
});

// Line chart, using the score data to highlight overall performance trends over time.
new Chart(document.getElementById("all_quiz_line_chart").getContext("2d"), 
{
    type: "line",
    data: 
    {
        labels: chartLabels,
        datasets: [{
            label: "Score (%)",
            data: chartScores,
            backgroundColor: 
            [
            "rgba(220, 38, 38, 0.7)", 
            "rgba(37, 81, 151, 0.7)", 
            "rgba(220, 199, 11, 0.7)" 
            ]                               
        }]
    },
    options: 
    {
        responsive: true,
        maintainAspectRatio: false,
        scales: 
        {
            y: {  beginAtZero: true, max: 100 }
        }
    }
});
</script>
</body>
</html>