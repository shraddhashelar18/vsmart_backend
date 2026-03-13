<?php
require_once("../config.php");
require_once("../api_guard.php");
require_once("../cors.php");

$row=$conn->query("
SELECT allow_marksheet_upload
FROM admin_settings
WHERE id=1
")->fetch_assoc();

echo json_encode([
"allowUpload"=>$row["allow_marksheet_upload"]
]);
?>