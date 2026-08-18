<?php
require_once __DIR__ . "/../include/auth.php";
require_once __DIR__ . "/../include/db.php";

require_role("student"); 
$userID = current_user_id(); // Take user id from SESSION (not taken from the URL since that could be changed to access another student's dashboard)
$submissionID = require_get_int("submission_id", "student_dashboard.php"); // submission_id must exist and be >= 1, otherwise go back to dashboard

// Fetch submission + assessment + feedback (only if it belongs to this student)
// Extra condition of "subm.user_id = :user_id" ensures that the current student can only view their submission.
// Prepared statements are used again so parameters are handled safely and is consistent with other pages. 
$sqlViewFeedback = "SELECT subm.submission_id, subm.score, assess.title AS quiz_title,
    feedb.feedback_id, feedb.teacher_id, teach.username AS teacher_name,
    feedb.what_went_well, feedb.needs_improvement, feedb.next_steps, feedb.created_at AS feedback_created_at,
    feedb.is_read FROM submission subm JOIN assessment assess ON assess.assessment_id = subm.assessment_id 
    LEFT JOIN feedback feedb ON feedb.submission_id = subm.submission_id 
    LEFT JOIN user teach ON teach.user_id = feedb.teacher_id WHERE subm.submission_id = :submission_id AND subm.user_id = :user_id LIMIT 1";

$statementViewFeedback = $pdo->prepare($sqlViewFeedback);
$statementViewFeedback->execute([":submission_id" => $submissionID,":user_id" => $userID]);
$viewFeedbackRow = $statementViewFeedback->fetch();

if (!$viewFeedbackRow) 
{
    // Either submission doesn't exist OR it doesn't belong to this student.
    header("Location: student_dashboard.php");
    exit;
}

$hasFeedback = false;
if (isset($viewFeedbackRow["feedback_id"]) && $viewFeedbackRow["feedback_id"] !== null) {
    $hasFeedback = true;
}

// Mark as read (only if feedback exists), creates a nice flow where if teacher updates the feedback this will be reset to 0.
if ($hasFeedback) 
{
    $sqlUpdate = " UPDATE feedback SET is_read = 1 WHERE submission_id = :submission_id";

    $statementUpdate = $pdo->prepare($sqlUpdate);
    $statementUpdate->execute([":submission_id" => $submissionID]);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Feedback</title>
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
    <p class="dashboard_welcome"> View Feedback </p>
        <div class="card_student">
            <h2><?php echo htmlspecialchars($viewFeedbackRow["quiz_title"]); ?></h2>
            <p class="student_feedback">Score: <?php echo htmlspecialchars($viewFeedbackRow["score"]); ?>%</p>

            <?php
            if ($hasFeedback) 
            {
                if (isset($viewFeedbackRow["teacher_name"]) && $viewFeedbackRow["teacher_name"] !== null) 
                {
                    echo "<p class='student_feedback'> Feedback author: " . htmlspecialchars($viewFeedbackRow["teacher_name"]) . "</p>";
                }
                
                if (isset($viewFeedbackRow["feedback_created_at"]) && $viewFeedbackRow["feedback_created_at"] !== null) 
                {
                    echo "<p class='student_feedback'> Feedback date: " . htmlspecialchars($viewFeedbackRow["feedback_created_at"]) . "</p>";
                }
            }
            ?>
        </div>

        <div class="feedback_given_student">
            <?php
            if (!$hasFeedback) 
            {
                echo "<p>Currently there is no feedback.</p>";
            } 
            else 
                {
                    // output each feedback section inside its own cardFeedback div (CSS can be used to style these).
                    // Seperated from the cardStudent
                     echo '<div class="card_feedback">
                    <h3>What went well</h3>
                    <p>' . htmlspecialchars($viewFeedbackRow["what_went_well"]) . '</p>
                    </div>';

                    echo '<div class="card_feedback">
                    <h3>What could be improved upon</h3> 
                    <p>' . htmlspecialchars($viewFeedbackRow["needs_improvement"]) . '</p>
                    </div>';

                    echo  '<div class="card_feedback">
                    <h3>What to do next</h3>
                    <p>' . htmlspecialchars($viewFeedbackRow["next_steps"]) . '</p>
                    </div>';
                }
            ?>
        </div>
    </main>
    </div>
</div>
</body>
</html>