<?php
require_once __DIR__ . "/../include/auth.php";
require_once __DIR__ . "/../include/db.php";
require_role("teacher");

// Read the selected submission safely from the URL and return to the dashboard if it is missing or invalid.
$submissionID = require_get_int("submission_id", "teacher_dashboard.php");
$teacherID = current_user_id(); // Take user id from SESSION (not taken from the URL since that could be changed to access another teacher's dashboard)

// Load the selected submission together with the student name, quiz title, and any existing feedback.
// LEFT JOIN is used for feedback because the page must still work even when no feedback has been written yet.
$sqlSelectSubmission = " SELECT subm.submission_id, subm.user_id AS student_id, subm.score, subm.submitted_at, subm.assessment_id, u.username AS student_name,
    assess.title AS quiz_title,
    feedb.feedback_id,
    feedb.teacher_id,
    teach.username AS teacher_name, feedb.what_went_well, feedb.needs_improvement,feedb.next_steps 
    FROM submission subm JOIN user u ON u.user_id = subm.user_id JOIN assessment assess ON assess.assessment_id = subm.assessment_id 
    LEFT JOIN feedback feedb ON feedb.submission_id = subm.submission_id 
    LEFT JOIN user teach ON teach.user_id = feedb.teacher_id WHERE subm.submission_id = :submission_id LIMIT 1"; // teacher_id used to display the author.

$statementSubmission = $pdo->prepare($sqlSelectSubmission);
$statementSubmission->execute([":submission_id" => $submissionID]); 
$rowSubmission = $statementSubmission->fetch();

if (!$rowSubmission) 
{
    header("Location: teacher_dashboard.php");
    exit;
}

$studentID = (int)$rowSubmission["student_id"];
$assessmentID = (int)$rowSubmission["assessment_id"]; 

// Calculate the class average for the same quiz so the teacher can compare one student's score against the wider cohort.
$sqlSelectClassAverage = " SELECT ROUND(AVG(score), 1) AS class_average FROM submission WHERE assessment_id = :assessment_id";
$statementClassAverage = $pdo->prepare($sqlSelectClassAverage);
$statementClassAverage->execute([":assessment_id" => $assessmentID]);
$rowAverage = $statementClassAverage->fetch();

// Default the average to 0 if there are no rows available.
$classAverage = 0.0;
if ($rowAverage && isset($rowAverage["class_average"]) && $rowAverage["class_average"] !== null)
{
    $classAverage = (float)$rowAverage["class_average"]; 
}

$studentScore = (int)$rowSubmission["score"];
// Decides if teacher is inserting or overwriting, if feedback_id exists enter into overwrite mode.
$isOverwrite = false;
if (isset($rowSubmission["feedback_id"]) && $rowSubmission["feedback_id"] !== null) 
{
    $isOverwrite = true;
}

// Update the page text so that the UI clearly shows whether the teacher is creating or editing feedback.
$pageTitle = "Enter Feedback";
$buttonText = "Submit Feedback";
if ($isOverwrite) 
{
    $pageTitle = "Overwrite Feedback";
    $buttonText = "Submit Feedback";
}
// Pre-fill the form fields with any existing feedback so the teacher can review and edit it more easily.
$whatWentWell = "";
$needsImprovement = "";
$nextSteps = "";
if (isset($rowSubmission["what_went_well"]) && $rowSubmission["what_went_well"] !== null) 
{
    $whatWentWell = $rowSubmission["what_went_well"];
}

if (isset($rowSubmission["needs_improvement"]) && $rowSubmission["needs_improvement"] !== null) 
{
    $needsImprovement = $rowSubmission["needs_improvement"];
}

if (isset($rowSubmission["next_steps"]) && $rowSubmission["next_steps"] !== null) 
{
    $nextSteps = $rowSubmission["next_steps"];
}

$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") 
{
    // When the form is submitted, validate the input first, then either update existing feedback or insert a new row.
    $newWhatWentWell = "";
    $newNeedsImprovement = "";
    $newNextSteps = "";

    // trim(), removes leading and trailing spaces so that it is not treated as valid feedback.
    if (isset($_POST["what_went_well"])) 
    {
        $newWhatWentWell = trim($_POST["what_went_well"]); 
    }

    if (isset($_POST["needs_improvement"]))     
    {
        $newNeedsImprovement = trim($_POST["needs_improvement"]);
    }

    if (isset($_POST["next_steps"])) 
    {
        $newNextSteps = trim($_POST["next_steps"]);
    }
    
    // Require all three sections so the feedback stays structured and complete.
    if ($newWhatWentWell === "" || $newNeedsImprovement === "" || $newNextSteps === "") 
    {
        $errorMessage = "Please complete all three feedback fields.";
        // Keep the typed input that the teacher has done so that the form does not reset.
        $whatWentWell = $newWhatWentWell;
        $needsImprovement = $newNeedsImprovement;
        $nextSteps = $newNextSteps;
    } 

        // If feedback already exists, update the same row instead of creating a duplicate.
        // Reset is_read to 0 so the student can see that the feedback has been changed.
        else if ($isOverwrite) 
        {                      
            $sqlUpdateFeedback = "UPDATE feedback SET teacher_id = :teacher_id, what_went_well = :what_went_well,
                    needs_improvement = :needs_improvement, next_steps = :next_steps, is_read = 0 
                    WHERE submission_id = :submission_id ";

            $statementUpdateFeedback = $pdo->prepare($sqlUpdateFeedback);
            $statementUpdateFeedback->execute([ ":what_went_well" => $newWhatWentWell, ":needs_improvement" => $newNeedsImprovement,":teacher_id" => $teacherID,
                ":next_steps" => $newNextSteps,
                ":submission_id" => $submissionID]);
                header("Location: teacher_dashboard.php"); // After saving return the teacher to the dashboard (creates a nice loop and flow).
                exit;
        }  

        else 
        {     
            $sqlInsertFeedback = "INSERT INTO feedback(submission_id, teacher_id, what_went_well, needs_improvement, next_steps, created_at, is_read)
                                 VALUES (:submission_id, :teacher_id, :what_went_well, :needs_improvement, :next_steps, NOW(), 0)";
            $statementInsertFeedback = $pdo->prepare($sqlInsertFeedback);
            $statementInsertFeedback->execute([":submission_id" => $submissionID, ":teacher_id" => $teacherID,":what_went_well" => $newWhatWentWell,
                ":needs_improvement" => $newNeedsImprovement,
                ":next_steps" => $newNextSteps, ]);
                 header("Location: teacher_dashboard.php"); 
                 exit;
        }
        
    }
// Prepare the chart data so the teacher can compare the student quiz score with the class average at a glance.
$chartLabels = ["Student Score", "Class Average"];
$chartData = [$studentScore, $classAverage];
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="../css/assets/bootstrap.min.css">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/teacher.css">
</head>
<body>

<div class="container-fluid dashboard_layout">
    <div class="row">
  <aside class="col-3 col-lg-2 dashboard_sidebar">
    <h3 class="menu_text">Navigation</h3>
    <a class="dashboard_link" href="teacher_dashboard.php">Back to Class View</a>
     <a class="dashboard_link" href="../logout.php">Logout</a>
  </aside>

  <main class="col-9 col-lg-10 dashboard_main_content">

    <h2 class="result_quiz"><?php echo htmlspecialchars($pageTitle); ?> for <?php echo htmlspecialchars($rowSubmission["student_name"]); ?></h2>

    <div class="student_overview">
        <p><?php echo htmlspecialchars($rowSubmission["quiz_title"]); ?></p>
        <p>Score Achieved: <?php echo htmlspecialchars($rowSubmission["score"]); ?>%</p>

         <!-- If overwriting ensure the teacher knows who provided the previous feedback. (Helps workflow and supports collaboration between teachers). -->
    <?php if ($isOverwrite && !empty($rowSubmission["teacher_name"])):  ?>
        <p> Existing feedback author: <?php echo htmlspecialchars($rowSubmission["teacher_name"]); ?> </p>
    <?php endif; ?>
    </div>

    <div class="card_teacher">
        <h3 class="quiz_context">Quiz Score vs Class Average</h3>
        <div class="chart_wrap">
            <canvas id="quiz_context_chart"></canvas>
        </div>
    </div>

    <?php
    // Only display the validation message when the form submission failed.
    if ($errorMessage !== "") 
    {
        echo '<p class="p_error">' . htmlspecialchars($errorMessage) . '</p>';
    }
    ?>
    <div class="give_feedback_card">
        <!-- Post back to the same page while keeping the selected submission ID in the URL. -->
        <form method="post" action="enter_feedback.php?submission_id=<?php echo (int)$submissionID; ?>">

            <label for="needs_improvement">What could be improved upon?</label><br>
            <textarea id="needs_improvement" name="needs_improvement" rows="5"><?php echo htmlspecialchars($needsImprovement); ?></textarea>

            <label for="what_went_well">What went well?</label><br>
            <textarea id="what_went_well" name="what_went_well" rows="5"><?php echo htmlspecialchars($whatWentWell); ?></textarea>

            <label for="next_steps">What to do next?</label><br>
            <textarea id="next_steps" name="next_steps" rows="5"><?php echo htmlspecialchars($nextSteps); ?></textarea>
            <br>
            <button type="submit" class="btn_action"><?php echo htmlspecialchars($buttonText); ?></button>
            <a class="btn_action" href="teacher_dashboard.php">Cancel</a>

        </form>
    </div>
  </main>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.5/dist/chart.umd.min.js"></script>
<script>
    // Similar to the other pages, the PHP chart arrays are converted into JavaScript so chart.js can use the database values.
const chartLabels = <?php echo json_encode($chartLabels); ?>;
const chartData = <?php echo json_encode($chartData); ?>;

const chartContext = document.getElementById("quiz_context_chart").getContext("2d");
new Chart(chartContext, {
type: "bar",
data: {
labels: chartLabels,
datasets: [{
    label: "Scores (%)",
    data: chartData,
    backgroundColor: [ 
"rgba(37, 81, 151, 0.7)",
"rgba(220, 38, 38, 0.7)"]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            // Keep the y-axis fixed to percentage values so the comparison stays consistent.
            y: 
            {
                beginAtZero: true,
                max: 100
            }
        }
    }
});
</script>
</body>
</html>