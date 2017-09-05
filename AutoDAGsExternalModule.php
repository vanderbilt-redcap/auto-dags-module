<?php
namespace Vanderbilt\AutoDAGsExternalModule;

class AutoDAGsExternalModule extends \ExternalModules\AbstractExternalModule{
	const LABEL_VALUE_SEPARATOR = ' - ';

	function hook_save_record($project_id, $record){
		$dagFieldName = $this->getProjectSetting('dag-field');
		if(empty($dagFieldName)){
			return;
		}

		$recordIdFieldName = \REDCap::getRecordIdField();
		$data = json_decode(\REDCap::getData($project_id, 'json', [$record], [$recordIdFieldName, $dagFieldName]))[0];

		$fieldValue = $data->$dagFieldName;
		$fieldLabel = $this->getChoiceLabel($dagFieldName, $fieldValue);

		$groupName = $fieldLabel . self::LABEL_VALUE_SEPARATOR . $fieldValue;

		list($groupId, $existingGroupName) = $this->getDAGInfoForFieldValue($fieldValue);
		if($groupId == null){
			$groupId = $this->createDAG($groupName);
		}
		else if($existingGroupName != $groupName){
			$this->renameDAG($groupId, $groupName);
		}

		$this->setDAG($record, $groupId);
	}

	private function getDAGInfoForFieldValue($value){
		$groupNames = \REDCap::getGroupNames();
		foreach($groupNames as $groupId=>$groupName){
			$lastSeparatorIndex = strrpos($groupName, self::LABEL_VALUE_SEPARATOR);
			$associatedFieldValue = substr($groupName, $lastSeparatorIndex + strlen(self::LABEL_VALUE_SEPARATOR));

			if($associatedFieldValue == $value){
				return [$groupId, $groupName];
			}
		}

		return [null, null];
	}
}