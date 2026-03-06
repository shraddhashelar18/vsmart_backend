<?php

function calculatePromotion($conn, $studentId, $atktLimit){

    $stmt = $conn->prepare("
        SELECT subject,
               SUM(total_marks) as total_marks,
               SUM(obtained_marks) as obtained_marks
        FROM marks
        WHERE student_id = ?
        GROUP BY subject
    ");

    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $result = $stmt->get_result();

    $totalFinalSubjects = 0;
    $failCount = 0;
    $ktSubjects = [];

    $totalMaxMarks = 0;
    $totalObtainedMarks = 0;

    while ($row = $result->fetch_assoc()) {

        $totalFinalSubjects++;

        if ($row['obtained_marks'] === NULL) {
            continue;
        }

        $total = $row['total_marks'];
        $obtained = $row['obtained_marks'];

        $totalMaxMarks += $total;
        $totalObtainedMarks += $obtained;

        $percentage = ($obtained / $total) * 100;

        // subject fail condition
        if ($percentage < 40) {
            $failCount++;
            $ktSubjects[] = $row['subject'];
        }
    }

    // If no subjects found
    if ($totalFinalSubjects == 0) {
        return [
            "status" => "NOT_EVALUATED",
            "percentage" => null,
            "backlogCount" => 0,
            "ktSubjects" => []
        ];
    }

    // Promotion logic
    if ($failCount == 0) {
        $status = "PROMOTED";
    } 
    elseif ($failCount <= $atktLimit) {
        $status = "PROMOTED_WITH_ATKT";
    } 
    else {
        $status = "DETAINED";
    }

    // Percentage only if no ATKT
    if ($failCount == 0 && $totalMaxMarks > 0) {
        $overallPercentage = ($totalObtainedMarks / $totalMaxMarks) * 100;
        $overallPercentage = round($overallPercentage, 2);
    } 
    else {
        $overallPercentage = null;
    }

    return [
        "status" => $status,
        "percentage" => $overallPercentage,
        "backlogCount" => $failCount,
        "ktSubjects" => $ktSubjects
    ];
}