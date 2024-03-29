# ***MYSQLI HELPER FUNCTIONS (PHP)***


## **INTRODUCTION**


This is an simple include file written in PHP that (currenlty) has 6 functions made to simplify mysqli queries.

The MySQLi functions allows you to access MySQL database servers. 

They are more secure than regular functions because they use prepared statements instead of regular ones which 
  prevents probability of SQL Injection attacks and improves security.

#### **File contains 2 main functions and 4 side functions :**
  - 2 main functions are : **_mysqli_single_query ( )_** and **_mysqli_multi_query ( )_**
  - 4 side functions are : **_mysqli_select ( )_** , **_mysqli_select_all ( )_**, **_mysqli_insert ( )_**, **_mysqli_update ( )_**.

Only difference between these groups of functions is the ammount and type of variables that you send to the function.
 
Main functions are more straight forward and requiere : 
	written mysqli query,
	parameters, 
	database connection, 
	(return type)


Side functions are more precise but they require more arguments: 
	requests,
	table name,
	targets,
	databse connection,
	return type,
	operators

These functions accept parts of sql query statment than the function itself combines them to make a real mysqli query.

**NOTE: ** that this is not a new way or a better way of executing querys in PHP, this is just an include file that
  can maybe help someone avoid sphagetti code. 


### **Table of content :**

  #### 1. Main function and examples
   - **1.1** - mysqli_single_query() example
   - **1.2** - mysqli_multiple_query() example
  #### 2. Side functions and examples
   -  **2.1** - mysqli_select() example
   -  **2.2** - mysqli_select_all() example
   -  **2.3** - mysqli_insert() example
   -  **2.4** - mysqli_update() example
  #### 3. Guide and detailed description for main functions
   -  **3.1** - mysqli_single_query() detailed description
   -  **3.2** - mysqli_multiple_query() detailed description
  #### 4. Guide and detailed description for side functions
   -  **4.1** - mysqli_select() detailed description
   -  **4.2** - mysqli_select_all() detailed description
   -  **4.3** - mysqli_insert() detailed description
   -  **4.4** - mysqli_update() detailed description
  #### 5. Update logs and credits
   -  **5.1** - Update logs
   -  **5.2** - Credits


 ## ***1. MAIN FUNCTIONS*** 


### **EXAMPLE ONE - MYSQLI_SINGLE_QUERY()**

 Usually (with sql error check, result check and formatting ) your PHP code to make a SINGLE query where you
 select all data from a table would look like this :
	
```
	$sql = "SELECT * FROM users WHERE email = ?";
   	$stmt = mysqli_stmt_init($conn);

    	if(!mysqli_stmt_prepare($stmt,$sql)) {
        	header("Location: ../register.php?sqlerror");
        	exit();    
    	} else {

        	mysqli_stmt_bind_param($stmt,"s",$userEmail);
        	mysqli_stmt_execute($stmt);
        	mysqli_stmt_store_result($stmt);
		$resultCheck = mysqli_stmt_num_rows($stmt);
        
	
        	if($resultCheck > 0) {
        
            		header("Location: ../register.php?emailTaken");
            		exit();
        	}
    	}
```

This code is not too complicated untill you have multi querys that all depend on the result of the previous 
query which would make the code not only unreadable but confusing and distracting.

Functions that are defined in this include files keep the result but make the code much shorter and more readable. 

These functions feel more like pure SQL functions so every user, no matter of their skill level, can understand 
the concept, which , in my opinion, is much harder in the previous example.

Same example using main function mysqli_single_query() from this file 

```
	$count = mysqli_single_query( "SELECT * FROM users WHERE email = ?", $email, $database_connection,'c');
        if($count > 0) {
        	header("Location: ../register.php?emailTaken");
            	exit();        
        }
```

**NOTE :** Functions have integrated error handling system so SQL error check and wrong parameter error check is not needed(throws an exception with exact location).

 This function call reduced from 19 lines to 5 ( both examples could have less white space )
 
 The function call could be called in more lines if the query is longer to increase readability which in worse case would produce 11 lines of code.

### ***EXAMPLE 2 - MYSQLI_MULTI_QUERY()***

In this scenario we will need to check if the user exist in the database and than based on that query we should
decide if we should insert new data into table or throw an error if the user exist.

This written in simple mysqli way would look like this :

```
	$sql = "SELECT * FROM users WHERE email = ? OR username = ?";
   	$stmt = mysqli_stmt_init($conn);

    	if(!mysqli_stmt_prepare($stmt,$sql)) {
        	header("Location: ../register.php?sqlerror");
        	exit();    
    	} else {

        	mysqli_stmt_bind_param($stmt,"ss",$userEmail,$username);
        	mysqli_stmt_execute($stmt);
        	mysqli_stmt_store_result($stmt);
		$resultCheck = mysqli_stmt_num_rows($stmt);
        
        	if($resultCheck > 0) {
        
            		header("Location: ../register.php?userExists");
            		exit();
        	} else {	
			$sql = "INSERT INTO users (name,surname,email,username,password) VALUES (?,?,?,?,?)";
 	  		$stmt = mysqli_stmt_init($conn);

	    		if(!mysqli_stmt_prepare($stmt,$sql)) {
        			header("Location: ../register.php?sqlerror");
        			exit();    
    			} else {

	        		mysqli_stmt_bind_param($stmt,"s",$userEmail);
        			mysqli_stmt_execute($stmt);
        			mysqli_stmt_store_result($stmt);
    			}
    		}
	}
```

  This doesen't look that bad but it sure is a lot of lines to read,follow, remember and scroll.

  Lets look at the same example using mysqli_multi_query() function.
  
  This function allows us to basically make array of arrays(matrix) where every row(array) would represent a single query.
  
  Additional varaiables would determine if the function should stop and return index of last successful query or finish and return      true.

```
  	mysqli_multi_query(
		array("SELECT * FROM users WHERE username = ? OR email = ?",array($username,$email),'e',false),
		array("INSERT INTO users (name,surname,email,username,password) VALUES (?,?,?,?,?)",array($name,$surname,$email,$username,$email,$password),'')),
		$conn
	);
```

 And that is basically it, this function will actually call mysqli_single_query() function twice with the parameters you provided.
 In the array definition of the first (SQL query) parameter stays the same as in the single call function(more on that later),
 that is true for the second and third parameter too but the fourth parameter is actually the contidition in which the next statement    should be executed

```
							=> RESULT == CONDITION => EXECUTE NEXT QUERY
  EXECUTE FIRST QUERRY => COMPARE RESULT TO CONDITION => IF
							=> RESULT != CONDITION => RETURN INDEX OF LAST QUERY THAT WAS EXECUTED => BREAK 
```


## ***SIDE FUNCTIONS***

 These functions require more variables and a bit of formatting but in some cases they are quick way to execute simple sql querys.

### **EXAMPLE ONE - MYSQLI SELECT ( )** 

 This is an example of a mysqli normal SELECT query :

```
	$sql = "SELECT email FROM users WHERE username= ?";
   	$stmt = mysqli_stmt_init($conn);

    	if(!mysqli_stmt_prepare($stmt,$sql)) {
        	header("Location: ../register.php?sqlerror");
        	exit();    
    	} else {

        	mysqli_stmt_bind_param($stmt,"s",$userEmail);
        	mysqli_stmt_execute($stmt);
        	$result = mysqli_stmt_get_result($stmt);
    	}
```

- Same example using mysqli_select() function :

```
	$result = mysqli_select(
		"email",
		"users",
		array("username" => $username),
		$database_connection,
		'r'
	);

```

### **EXAMPLE TWO - MYSQLI SELECT ALL ( )**

 This is an example of a mysqli SELECT ALL query :

```
	$sql = "SELECT * FROM users WHERE username= ?";
   	$stmt = mysqli_stmt_init($conn);

    	if(!mysqli_stmt_prepare($stmt,$sql)) {
        	header("Location: ../register.php?sqlerror");
        	exit();    
    	} else {

        	mysqli_stmt_bind_param($stmt,"s",$userEmail);
        	mysqli_stmt_execute($stmt);
        	$result = mysqli_stmt_get_result($stmt);
    	}
```

 Same example using mysqli_select_all() function :

```
	$result = mysqli_select_all(
		"users",
		array("username" => $username),
		$database_connection,
		'r'
	);

```


### **EXAMPLE THREE - MYSQLI INSERT ( )**

 This is an example of a mysqli normal INSERT query :

```
	$sql = "INSERT INTO users (username,email) VALUES (?,?)";
   	$stmt = mysqli_stmt_init($conn);

    	if(!mysqli_stmt_prepare($stmt,$sql)) {
        	header("Location: ../register.php?sqlerror");
        	exit();    
    	} else {

        	mysqli_stmt_bind_param($stmt,"s",$userEmail);
        	mysqli_stmt_execute($stmt);
        	$result = mysqli_stmt_get_result($stmt);
    	}

```

 Same example using mysqli_insert() function :

```
	$result = mysqli_insert(
		"users",
		array("username" => $username,"email"=>$email),
		$database_connection,
	);
```

### **EXAMPLE FOUR - MYSQLI UPDATE ( )**

 This is an example of a mysqli normal UPDATE query :

```
	$sql = "UPDATE users SET firstName = ? WHERE username = ?";
   	$stmt = mysqli_stmt_init($conn);

    	if(!mysqli_stmt_prepare($stmt,$sql)) {
        	header("Location: ../register.php?sqlerror");
        	exit();    
    	} else {

        	mysqli_stmt_bind_param($stmt,"ss",$firstName,$username);
        	mysqli_stmt_execute($stmt);
        	$result = mysqli_stmt_get_result($stmt);
    	}
```

 Same example using mysqli_update() function :

```
	$result = mysqli_update(
		"users",
		array("username" => $username),
		array("email" => $email),		
		$database_connection
	);
```


**NOTE:** These functions can be called in a single row too.


## ***3. GUIDE AND DOCUMENTATION FOR MAIN FUNCTIONS***

* *- Dictionary :
  **[REQ]** = Requiered, **[OPT]** = optional, **[DEF]** = Default
  
### **3.1 MYSQLI_SINGLE_QUERY ( )**


 - Use this function to execute a single mysqli query with a prepared statement.
 
**NOTE:** This function uses mysqli prepared statements instead of default sql statements so additional knowladge of mysqli prepared statements is needed. <->


```
 mysqli_single_query(
     [REQ] ( string ) $preparedStatement (valid mysqli prepared statement) ,
     [REQ] ( array or a single value ) $parameters,
     [REQ] ( mysqli_connect() ) $database_connection
     [OPT] ( string ) $returnType, [DEF] associative array
 ); 
 ```
 
 #### Supported return types :
     - a - returns associatve array [DEF] can change in $returnType = 'a' 
     - r - returns mysqli_result
     - c - returns count of rows in mysqli result
     - e - returns true if value exist or false if it don't exist

 #### Errors and returns :
     - Throws (ArgumentTypeError) for any argument missmatch, 
     - Throws (emptyParameterError) for calls with empty parameters, 
     - Throws (sqlError) for any SQL-related error 
     - Returns (by default) associative array as final output (can be changed)
     
     
### **3.2 MYSQLI_MULTI_QUERY ( )**


 - Use this function to execute multiple single mysqli query with a prepared statement. This function is synchronus and condition based 
 which means it will execute one query at the time and if the result of that query matches your expected result it will contiune on the
 next one. If the result is different this function will return index of the last sucessfully executed query (starts at 1).
 
**NOTE:** This function uses mysqli prepared statements instead of default sql statements so additional knowladge of mysqli prepared statements is needed. <->

**NOTE:** First parameter is a matrix of arrays. To make this matrix you need to write multiple single sqli querries identical to one's 
mentioned in 3.1 MYSQLI_SQLI_QUERIES.

```
 mysqli_single_query(
     [REQ] ( array or associate array ) $singleQueryArray ( matrix of single sqli queries ) ,
     [REQ] ( mysqli_connect() ) $database_connection
 ); 
 ```
 
 #### Supported return types :
     - a - returns associatve array [DEF] can change in $returnType = 'a' 
     - r - returns mysqli_result
     - c - returns count of rows in mysqli result
     - e - returns true if value exist or false if it don't exist

 #### Errors and returns :
     - Throws (ArgumentTypeError) for any argument missmatch, 
     - Throws (emptyParameterError) for calls with empty parameters, 
     - Throws (sqlError) for any SQL-related error 
     - Returns (by default) associative array as final output (can be changed)
     


## ***4. GUIDE AND DOCUMENTATION FOR SIDE FUNCTIONS***


* *- Dictionary :
  **[REQ]** = Requiered, **[OPT]** = optional, **[DEF]** = Default


### **4.1 MYSQLI_SELECT ( )**


 - Use this function to select specific values from SQL database <->

```
 mysqli_select(
     [REQ] ( string ) $values_to_select (comma separated),
     [REQ] ( string ) $database_name,
     [REQ] ( associative array ) $targets, 
     [REQ] ( mysqli_connect() ) $database_connection
     [OPT] ( string ) $returnType, [DEF] associative array
     [OPT] ( string OR array ) $operator , [DEF] " AND "
 ); 
 ```

**NOTE:** - $values_to_select can be '*' to select all from database but there is a separate function called mysqli_select_all().
**NOTE:** - If you need more than 1 operator provide an array of operators ( or a single operator) in $operator, if only one operator is provided it will be used for all parameters.
**NOTE:** - This function is "smart" which means that the [OPT] parameters will be decided automatically, you dont need to specify the default ones if you need any one thats after the first one.

 #### Supported return types :
     - a - returns associatve array [DEF] can change in $returnType = 'a' 
     - r - returns mysqli_result
     - c - returns count of rows in mysqli result
     - e - returns true if value exist or false if it don't exist

 #### Supported operator types :
     - AND - [DEF] Can be changed in $operator  
     - OR - 

 #### Errors and returns :
     - Throws (ArgumentTypeError) for any argument missmatch, 
     - Throws (emptyParameterError) for calls with empty parameters, 
     - Throws (sqlError) for any SQL-related error 
     - Returns (by default) associative array as final output (can be changed)


### **4.2 MYSQLI_SELECT_ALL ( )**

 - Use this function to select all values from SQL database <->

```
 mysqli_select_all(
     [REQ] (string) $database_name,
     [REQ] (assoc array) $targets, 
     [REQ] (mysqli_connect()) $database_connection
     [OPT] (string) $returnType [DEF] 'a' associative array
 );
 ```

 ### Supported return types :
     - a - returns associatve array --> [DEF] <--
     - r - returns mysqli_result
     - c - returns count of rows in mysqli result
     - e - returns true if value exist or false if it don't exist

### Errors and returns :
     - Throws (ArgumentTypeError) for any argument missmatch, 
     - Throws (emptyParameterError) for calls with empty parameters, 
     - Throws (sqlError) for any SQL-related error 
     - returns (by default) associative array as final output (can be changed)


### **4.2 MYSQLI_INSERT ( )**

 - Use this function to insert specific values in SQL database <->

```
 mysqli_insert(
     [REQ] (string) $tableName,
     [REQ] (assoc array) $values, 
     [REQ] (mysqli_connect()) $database_connection
     [OPT] (string OR char) $returnType
 );
 ```

### Supported return types :
     - t - returns true if all executes without error --> [DEF] <--
     - i - returns last insert id (mysqli_insert_id()) 


### Errors and returns :
     - Throws (ArgumentTypeError) for any argument missmatch, 
     - Throws (emptyParameterError) for calls with empty parameters, 
     - Throws (sqlError) for any SQL-related error 
     - returns (by default) true if all executes without error


### **4.2 MYSQLI_UPDATE ( )**

 - This function first updates column inside table in database <->
`
```
 sqli_update(
     [REQ] (string) $tableName,
     [REQ] (assoc array) $values, 
     [REQ] (assoc array) $targets, 
     [REQ] (mysqli_connect()) $database_connection
 );
 ```

 ### Errors and returns :
     - Throws (ArgumentTypeError) for any argument missmatch, 
     - Throws (emptyParameterError) for calls with empty parameters, 
     - Throws (sqlError) for any SQL-related error 
     - Returns (by default) true if all executes without error


## ***5. UPDATE LOGS AND CREDITS***

### 5.1 UPDATE LOGS

 **16/08/2019 v 1.0.7**
   - Added documentation for main functions
   - Updated the documentation
   
   
 **15/08/2019 v 1.0.6**
   - Added mysqli_multi_query()
   - Updated the documentation
   	
 **14/08/2019 v 1.0.5**
   - Added mysqli_single_query()
   - Fixed few bugs with mysqli_select()
   - Removed mysqli_check_insert()
 
 **11/08/2019 v 1.0.4**
   - Added =, >=, <=, <, >, support to all functions
  
 **10/08/2019 v 1.0.3**
   - Fixed bugs on mysqli_select_all()
   - Adeed $returnType support to mysqli_select_all() and mysqli_insert()
 
 **08/08/2019 v 1.0.2**
   - Added mysqli_update()
   - Return types renamed from "assoc","result","count","exist" to 'a','r','c','e'
   - Added $operators choice on mysqli_select() call
   - Implemented "smart" optional parameter decision on sqli_select()
 
 **07/08/2019 v 1.0.1**
   - Added mysqli_slect_all(),sqli_insert(), sqli_check_insert
   - Added $returnType support for sqli_select().
   
 **06/08/2019 v 1.0.0**
   - Added mysqli_select() and all documentation.


### 5.1 CREDITS

  **Made by Mulalić Almir**
  **Created : 06/08/2019**
  **Last update : 15/08/2019**
  **Current stable version : 1.0.6**
