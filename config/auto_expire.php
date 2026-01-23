<?php
$conn->query("
  UPDATE members
  SET status = 'EXPIRED'
  WHERE end_date < CURDATE()
");
?>
