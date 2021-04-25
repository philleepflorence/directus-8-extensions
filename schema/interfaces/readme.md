# Interface Extensions
> A collection of interface templates, default field values, interface options, and SQL Update Helpers for an existing Directus 8 Installations. 

## What
*The Nature, Value, and Identity*

Field and Interface Utilities. 
 
Default Field Values, Templates, and SQL Migration Scripts for the Standard Status Interface Component. 

## Why
*The Motivation* 

## HOW
*The Functions, Processes, and Methods*

> Each Directory references a SQL file in a Directory represents a single concept, such as Default Field Values, Interface Options, and Schema Updates.

### Interface
*The Function and Process of the Interface SQL File*

Add the Interface (or Field) to an existing Collection, by combining the Schema, Field.  
Requires SQL Alter, Select, and Update Privileges.

### Field
*The Function of the Field SQL File*

CRUD Interface Options and Field Values in the Directus Fields Collection.  
Requires SQL Select and Update Privileges.

### Role
*The Function of the Role SQL File*

CRUD Interface Options and Field Values in the Directus Roles and Permissions Collections.
Requires SQL Select and Update Privileges.

### Schema
*The Function of the Schema SQL File*

Alter the DB Table Field configuration, such as Data Type, Date Length, Field Comment, Index.
Requires SQL Alter, Select, and Update Privileges.