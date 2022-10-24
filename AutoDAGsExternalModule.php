<?php
namespace Vanderbilt\AutoDAGsExternalModule;

class AutoDAGsExternalModule extends \ExternalModules\AbstractExternalModule{
	const LABEL_VALUE_SEPARATOR = ' - ';
    const LABEL_VALUE_ELEMENT_TYPES = ['select','radio'];
    const LABEL_ONLY_ELEMENT_TYPES = ['text','calc','yesno','truefalse','slider','sql'];

	// We cache group info for the set-all-dags.php script, both for performance and because
	// REDCap::getGroupNames() doesn't pick up on added or renamed groups until the next request.
	private $groupsByID;

	function redcap_save_record($project_id, $record, $instrument, $event_id, $group_id, $survey_hash, $response_id, $repeat_instance){
		$dagFieldName = $this->getProjectSetting('dag-field');
		if(empty($dagFieldName)){
			return;
		}
        $currInstrumentFields = \REDCap::getFieldNames($instrument);
        if (in_array($dagFieldName, $currInstrumentFields) or !($this->getProjectSetting('curr-instr-only')))
		{$this->setDAGFromField($project_id, $record, $group_id, $dagFieldName);}
	}

	function setDAGFromField($project_id, $record, $group_id, $dagFieldName){
		$currentGroupId = !is_null($group_id) ? intval($group_id) : $group_id;

		$recordIdFieldName = \REDCap::getRecordIdField();
		$data = json_decode(\REDCap::getData($project_id, 'json', [$record], [$recordIdFieldName, $dagFieldName]))[0];

		$fieldValue = $data->$dagFieldName;

		if(empty($fieldValue)){
			$groupId = null;
		}
		else{
            // Depending on field type, form dag name as either concatenation of label and value or just value
            $fieldType = \REDCap::getFieldType($dagFieldName);
            if(in_array($fieldType, self::LABEL_ONLY_ELEMENT_TYPES)) {
                $groupName = $fieldValue;
                list($groupId, $existingGroupName) = $this->getDAGInfoForFieldValue($fieldValue, false);
            }
            elseif (in_array($fieldType, self::LABEL_VALUE_ELEMENT_TYPES)) {
                $fieldLabel = $this->getChoiceLabel(array('field_name' => $dagFieldName, 'value' => $fieldValue, 'record_id' => $record));
                $groupName = $fieldLabel . self::LABEL_VALUE_SEPARATOR . $fieldValue;
                list($groupId, $existingGroupName) = $this->getDAGInfoForFieldValue($fieldValue, true);
            }
            else {
                // Incompatible field type
                \REDCap::logEvent($this->getModuleName() . "\n(Configuration Error)","AutoDag field $dagFieldName is if type $fieldType which is not compatible.  Please read documentation and adjust settings",'',$record,null,$project_id);
                return false;
            }

            \REDCap::logEvent($this->getModuleName() . "\n(DEBUG DAG)", "GroupID is " . json_encode($groupId) . " \n " . json_encode($existingGroupName),'',$record,null,$project_id);

			if($groupId == null){
				$groupId = $this->createDAG($groupName);
                \REDCap::logEvent($this->getModuleName() . "\n(Created DAG)", "A new dag named $groupName has been created",'',$record,null,$project_id);
			}
			else if($existingGroupName != $groupName){
                // This can only happen when the separator is used and values are changed
				$this->renameDAG($groupId, $groupName);
                \REDCap::logEvent($this->getModuleName() . "\n(Renamed DAG)", "A dag $groupId has been renamed from $existingGroupName to $groupName",'',$record,null,$project_id);
			}

			$this->groupsByID[$groupId] = $groupName;
		}

		if ($currentGroupId !== $groupId) {
			$this->setDAG($record, $groupId);
		}
	}

	private function getDAGInfoForFieldValue($value, $useSeparator = true){
		if(!isset($this->groupsByID)){
			$this->groupsByID = \REDCap::getGroupNames();
		}

        // For enumerated field types, changing the value for a given label will rename the DAG, so:
        // If the dagField was originally a radio with enum `1, Apple` selected, the dag name will be `Apple - 1`.
        // If the data dictionary is later changed so `1, Apple` becomes `1, Apricot`, then the dag name will be renamed to
        // `Apricot - 1`.  We only want to do this when using enumerated field types.
        // For non-separator fields (like text fields) - we just want to create the DAG if it doesn't exist.
		foreach($this->groupsByID as $groupId=>$groupName){
            if ($useSeparator) {
                $lastSeparatorIndex = strrpos($groupName, self::LABEL_VALUE_SEPARATOR);
                $associatedFieldValue = substr($groupName, $lastSeparatorIndex + strlen(self::LABEL_VALUE_SEPARATOR));
            } else {
                $associatedFieldValue = $groupName;
            }

			if($associatedFieldValue == $value){
				return [$groupId, $groupName];
			}
		}

		return [null, null];
	}
}
