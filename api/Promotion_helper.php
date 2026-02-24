function calculatePromotion($conn, $studentId, $atktLimit){

    $stmt = $conn->prepare("
    SELECT subject, total_marks, obtained_marks
    FROM marks
    WHERE student_id = ?
    AND exam_type = 'FINAL'
    ");

    $stmt->bind_param("i",$studentId);
    $stmt->execute();
    $result = $stmt->get_result();

    $failCount = 0;
    $ktSubjects = [];

    while($row = $result->fetch_assoc()){

        if(is_null($row['obtained_marks'])){
            $failCount++;
            $ktSubjects[] = $row['subject'];
            continue;
        }

        $percentage = ($row['obtained_marks'] / $row['total_marks']) * 100;

        if($percentage < 40){
            $failCount++;
            $ktSubjects[] = $row['subject'];
        }
    }

    if($failCount == 0){
        $status = "PROMOTED";
    }
    elseif($failCount <= $atktLimit){
        $status = "PROMOTED_WITH_ATKT";
    }
    else{
        $status = "DETAINED";
    }

    return [
        "status"=>$status,
        "backlogCount"=>$failCount,
        "ktSubjects"=>$ktSubjects
    ];
}