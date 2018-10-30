# auto-dags-module

A REDCap External Module that automatically creates, renames, and assigns records to DAGs based on a specified field.

### Note

The field used to generate the DAGs should not be on a repeating instrument or an instrument of a repeating event. If the DAG field is on a repeating instrument, the record's DAG will never be set. If the field is on an instrument of a repeating event, the record's DAG will be set by the first instance of the event, and changes in subsequent instances will not change the record's DAG.
