<?php
namespace ExternalModules;

use Vanderbilt\AutoDAGsExternalModule\AutoDAGsExternalModule as module;
/** @var module $module */

include ExternalModules::getProjectHeaderPath();

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
	$dagFieldName = $module->getProjectSetting('dag-field');
	$recordIdFieldName = \REDCap::getRecordIdField();
	$data = \REDCap::getData($pid, 'array', null, [$recordIdFieldName]);
	foreach($data as $recordId=>$records){
		foreach($records as $eventId=>$record){
			$_GET['event_id'] = $eventId;

			// Pass a group id that would never exist to force the group to get set regardless (so we don't have to look up each group id).
			$groupId = PHP_INT_MAX;

			$module->setDAGFromField($pid, $recordId, $groupId, $dagFieldName);
		}
	}

	echo 'Done!';
}

include ExternalModules::getProjectFooterPath();
