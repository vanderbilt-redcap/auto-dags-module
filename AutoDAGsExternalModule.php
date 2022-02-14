<?php
namespace Vanderbilt\AutoDAGsExternalModule;

class AutoDAGsExternalModule extends \ExternalModules\AbstractExternalModule{
	const LABEL_VALUE_SEPARATOR = ' - ';

	// We cache group info for the set-all-dags.php script, both for performance and because
	// REDCap::getGroupNames() doesn't pick up on added or renamed groups until the next request.
	private $groupsByID;

	function redcap_save_record($project_id, $record, $instrument, $event_id, $group_id, $survey_hash, $response_id, $repeat_instance){
		$this->setDAGFromField($project_id, $record, $group_id);
	}

	function setDAGFromField($project_id, $record, $group_id){
		$currentGroupId = !is_null($group_id) ? intval($group_id) : $group_id;
		$dagFieldName = $this->getProjectSetting('dag-field');
		if(empty($dagFieldName)){
			return;
		}

		$recordIdFieldName = \REDCap::getRecordIdField();
		$data = json_decode(\REDCap::getData($project_id, 'json', [$record], [$recordIdFieldName, $dagFieldName]))[0];

		$fieldValue = $data->$dagFieldName;

		if(empty($fieldValue)){
			$groupId = null;
		}
		else{
			$fieldLabel = $this->getChoiceLabel($dagFieldName, $fieldValue);

			$groupName = $fieldLabel . self::LABEL_VALUE_SEPARATOR . $fieldValue;

			list($groupId, $existingGroupName) = $this->getDAGInfoForFieldValue($fieldValue);
			if($groupId == null){
				$groupId = $this->createDAG($groupName);
			}
			else if($existingGroupName != $groupName){
				$this->renameDAG($groupId, $groupName);
			}

			$this->groupsByID[$groupId] = $groupName;
		}

		if ($currentGroupId !== $groupId) {
			$this->setDAG($record, $groupId);
		}
	}

	private function getDAGInfoForFieldValue($value){
		if(!isset($this->groupsByID)){
			$this->groupsByID = \REDCap::getGroupNames();
		}

		foreach($this->groupsByID as $groupId=>$groupName){
			$lastSeparatorIndex = strrpos($groupName, self::LABEL_VALUE_SEPARATOR);
			$associatedFieldValue = substr($groupName, $lastSeparatorIndex + strlen(self::LABEL_VALUE_SEPARATOR));

			if($associatedFieldValue == $value){
				return [$groupId, $groupName];
			}
		}

		return [null, null];
	}
}
