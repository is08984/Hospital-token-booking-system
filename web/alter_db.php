<?php
require_once 'db_config.php';
$mysqli = getDBConnection();
$mysqli->query("ALTER TABLE patients ADD COLUMN doctor VARCHAR(100) DEFAULT 'Dr. Rajesh Kumar'");
echo "Success";
$mysqli->close();
?>
