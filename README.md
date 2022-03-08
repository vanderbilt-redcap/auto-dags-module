# Auto DAGs

Automatically creates, renames, and assigns records to Data Access Groups (DAGs) based on the value of a specified field. The value of that field becomes the name of the DAG. If the DAG already exists, then the record will be assigned to the existing DAG. An extra page is also provided for assigning the DAG for all records at once.

### Note

The field used to generate the DAGs should not be on a repeating instrument or an instrument of a repeating event. If the DAG field is on a repeating instrument, the record's DAG will never be set. If the field is on an instrument of a repeating event, the record's DAG will be set by the first instance of the event, and changes in subsequent instances will not change the record's DAG.

To use: 
1.	Enable module
1.	Click on configure and choose a field that will trigger the Auto DAGs
    *	Or click on Set DAG for all records (then click ok) 
1.	This will allow you to set the DAGs for the project.
1.	Once the configuration has been saved click Set DAG for all Records under external modules in the left-hand column
1.	This will allow you to verify the configuration and set the auto DAGS based on your project’s needs.
1.	You will see the changes, after clicking on DAGS in the left-hand column. 
    *	Changes include, the name of the DAG and which users are in which group.
