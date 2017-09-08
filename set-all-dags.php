<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>

<?php
if($_SERVER['REQUEST_METHOD'] != 'POST'){
	?>
	<form method="post">
		<button>Set the DAG for all records</button>
	</form>
	<script>
		$(function(){
			$('button').click(function(){
				var returnValue = confirm('Are you sure to want to automatically set all DAGs based on the specified field?')

				if(returnValue){
					$('form').hide();
				}

				return returnValue
			})
		})
	</script>
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
