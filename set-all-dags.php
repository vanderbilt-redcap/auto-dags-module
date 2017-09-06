<?php
if($_SERVER['REQUEST_METHOD'] != 'POST'){
	?>
	<form method="post">
		<button onclick="this.remove(); return confirm('Are you sure to want to automatically set all DAGs based on the specified field?')">Set the DAG for all records</button>
	</form>
	<?php
}
else{ // POST
	$pid = $_GET['pid'];
	$recordIdFieldName = \REDCap::getRecordIdField();
	$data = \REDCap::getData($pid, 'array', null, [$recordIdFieldName]);
	foreach($data as $recordId=>$records){
		foreach($records as $eventId=>$record){
			$_GET['event_id'] = $eventId;
			$module->hook_save_record($pid, $recordId);
		}
	}

	echo 'Done!';
}
