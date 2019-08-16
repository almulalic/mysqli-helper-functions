# ***MYSQLI HELPER FUNCTIONS (PHP)***


## **INTRODUCTION**


This is an simple include file written in PHP that (currenlty) has 6 functions made to simplify mysqli querries.

The MySQLi functions allows you to access MySQL database servers. 

They are more secure than regular functions because they use prepared statements instead of regular ones which 
  prevents probability of SQL Injection attacks and improves security.

File contains 2 main functions and 5 side functions :
  - 2 main functions are : mysqli_single_querry() and mysqli_multiple_querry()
  - 4 side functions are : mysqli_select(),mysqli_select_all(),mysqli_insert(),mysqli_update()

Only difference between these groups of functions is the ammount of variables that you send to the functions.
 
Main functions are more advanced and requiere : written mysqli querry,(parameters), data connection and (return type).
Side functions are more simpler to call but they require more arguments: requests,tableName,targets,databse connection,(return type),(operators).
  These functions accept parts of sql querry statment than the function itself combines them to make a real mysqli querry.

**NOTE** that this is not a new way or a better way of executing querrys in PHP, this is just an include file that
  can maybe help someone avoid sphagetti code. 


### **Table of content :**

  #### 1. Main function and examples
   #####  1.1 mysqli_single_querry() example
   #####  1.2 mysqli_multiple_querry() example
  #### 2. Side functions and examples
   #####  2.1 mysqli_select() example
   #####  2.2 mysqli_select_all() example
   #####  2.3 mysqli_insert() example
   #####  2.4 mysqli_update() example
  #### 3. Guide and detailed description for main functions
   #####  3.1 mysqli_single_querry() detailed description
   #####  3.2 mysqli_multiple_querry() detailed description
  #### 4. Guide and detailed description for side functions
   #####  4.1 mysqli_select() detailed description
   #####  4.2 mysqli_select_all() detailed description
   #####  4.3 mysqli_insert() detailed description
   #####  4.4 mysqli_update() detailed description
  #### 5. Update logs and credits
   #####  5.1 Update logs
   #####  5.2 Credits


 ## ***1. MAIN FUNCTIONS*** 


### **EXAMPLE ONE - MYSQLI_SINGLE_QUERRY()**

 Usually (with sql error check, result check and formatting ) your PHP code to make a SINGLE querry where you
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

This code is not too complicated untill you have multiple querrys that all depend on the result of the previous 
querry which would make the code not only unreadable but confusing and distracting.

Functions that are defined in this include files keep the result but make the code much shorter and more readable. 

These functions feel more like pure SQL functions so every user, no matter of their skill level, can understand 
the concept, which , in my opinion, is much harder in the previous example.

Same example using main function mysqli_single_querry() from this file 

```
	$count = mysqli_single_querry( "SELECT * FROM users WHERE email = ?", $email, $database_connection,'c');
        if($count > 0) {
        	header("Location: ../register.php?emailTaken");
            	exit();        
        }
```

**NOTE :** Functions have integrated error handling system so SQL error check and wrong parameter error check is not needed(throws an exception with exact location).

 This function call reduced from 19 lines to 5 ( both examples could have less white space )
 
 The function call could be called in more lines if the querry is longer to increase readability which in worse case would produce 11 lines of code.

### ***EXAMPLE 2 - MYSQLI_MULTIPLE_QUERRY()***

In this scenario we will need to check if the user exist in the database and than based on that querry we should
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

  Lets look at the same example using mysqli_multiple_querry() function.
  
  This function allows us to basically make array of arrays(matrix) where every row(array) would represent a single querry.
  
  Additional varaiables would determine if the function should stop and return index of last successful querry or finish and return      true.

```
  	mysqli_multiple_querry(
		array("SELECT * FROM users WHERE username = ? OR email = ?",array($username,$email),'e',false),
		array("INSERT INTO users (name,surname,email,username,password) VALUES (?,?,?,?,?)",array($name,$surname,$email,$username,$email,$password),'')),
		$conn
	);
```

 And that is basically it, this function will actually call mysqli_single_querry() function twice with the parameters you provided.
 In the array definition of the first (SQL querry) parameter stays the same as in the single call function(more on that later),
 that is true for the second and third parameter too but the fourth parameter is actually the contidition in which the next statement    should be executed

 In this example we ran a simple SELECT * querry , in the return type parameter we passed 'e' which stands for exist(retruns true if     value exist, false if not)
 and we also passed bool(false), so this function would actually be read as :

							=> RESULT == CONDITION => EXECUTE NEXT QUERRY
  EXECUTE FIRST QUERRY => COMPARE RESULT TO CONDITION => IF
							=> RESULT != CONDITION => RETURN INDEX OF LAST QUERRY THAT WAS EXECUTED => BREAK 

  More on the parameters types returns and detailed description later.
 **NOTE :** these functions already have


## ***SIDE FUNCTIONS***

 These functions require more variables and a bit of formatting but in some cases they are quick and efficient way to execute simple    mysqli querrys.

### **EXAMPLE ONE - MYSQLI SELECT()** 

 This is an example of a mysqli normal SELECT querry :

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

### **EXAMPLE TWO - MYSQLI SELECT ALL()**

 This is an example of a mysqli SELECT ALL querry :

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
	$result = mysqli_select(
		"users",
		array("username" => $username),
		$database_connection,
		'r'
	);

```


### **EXAMPLE THREE - MYSQLI INSERT ()**

 This is an example of a mysqli normal INSERT querry :

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
	$result = mysqli_select(
		"users",
		array("username" => $username),
		$database_connection,
		'r'
	);
```

### **EXAMPLE FOUR - MYSQLI UPDATE()**

 This is an example of a mysqli normal UPDATE querry :

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

 Same example using mysqli_select_all() function :

```
	$result = mysqli_update(
		"users",
		array("username" => $username),
		array("email" => $email),		
		$database_connection
	);
```

**NOTE:** These functions can be called in a single row too.


## ***3. GUIDE AND DOCUMENTATION FOR MAIN FUNCTIONS ***

## ***4. GUIDE AND DOCUMENTATION FOR SIDE FUNCTIONS ***


* *- Dictionary :
  **[REQ]** = Requiered, **[OPT]** = optional, **[DEF]** = Default


### **4.1 MYSQLI_SELECT()**


 - Use this function to select specific values from SQL database <->

 mysqli_select(
     [REQ] ( string ) $values_to_select (comma separated),
     [REQ] ( string ) $database_name,
     [REQ] ( assoc array ) $targets, 
     [REQ] ( mysqli_connect() ) $database_connection
     [OPT] ( string ) $returnType, [DEF] associative array
     [OPT] ( string OR array ) $operator , [DEF] " AND "
 ); 

 < NOTE > - $values_to_select can be '*' to select all from database but there is a separate function called mysqli_select_all().
 < NOTE > - If you need more than 1 operator provide an array of operators ( or a single operator) in $operator, if only one operator is provided it will be used for all parameters.
 < NOTE > - This function is "smart" which means that the [OPT] parameters will be decided automatically, you dont need to specify the default ones if you need any one thats after the first one.

 Supported return types :
 ------------------------
     - a - returns associatve array [DEF] can change in $returnType = 'a' 
     - r - returns mysqli_result
     - c - returns count of rows in mysqli result
     - e - returns true if value exist or false if it don't exist

 Supported operator types :
 ------------------------
     - AND - [DEF] Can be changed in $operator  
     - OR - 

 Errors and returns :
 ------------------------
     - Throws (ArgumentTypeError) for any argument missmatch, 
     - Throws (emptyParameterError) for calls with empty parameters, 
     - Throws (sqlError) for any SQL-related error 
     - Returns (by default) associative array as final output (can be changed)


//////////////////////////////
// 3.2 MYSQLI_SELECT_ALL() //
////////////////////////////

 - Use this function to select all values from SQL database <->

 mysqli_select_all(
     [REQ] (string) $database_name,
     [REQ] (assoc array) $targets, 
     [REQ] (mysqli_connect()) $database_connection
     [OPT] (string) $returnType [DEF] 'a' associative array
 ); 

 Supported return types :
 ------------------------
     - a - returns associatve array --> [DEF] <--
     - r - returns mysqli_result
     - c - returns count of rows in mysqli result
     - e - returns true if value exist or false if it don't exist
 Errors and returns :
 ------------------------
     - Throws (ArgumentTypeError) for any argument missmatch, 
     - Throws (emptyParameterError) for calls with empty parameters, 
     - Throws (sqlError) for any SQL-related error 
     - returns (by default) associative array as final output (can be changed)


//////////////////////////
// 3.3 MYSQLI_INSERT() //
////////////////////////

 - Use this function to insert specific values in SQL database <->

 mysqli_insert(
     [REQ] (string) $tableName,
     [REQ] (assoc array) $values, 
     [REQ] (mysqli_connect()) $database_connection
     [OPT] (string OR char) $returnType
 ); 

 Supported return types :
 ------------------------
     - t - returns true if all executes without error --> [DEF] <--
     - i - returns last insert id (mysqli_insert_id()) 

 Errors and returns :
 ------------------------
     - Throws (ArgumentTypeError) for any argument missmatch, 
     - Throws (emptyParameterError) for calls with empty parameters, 
     - Throws (sqlError) for any SQL-related error 
     - returns (by default) true if all executes without error


//////////////////////////
// 3.4 MYSQLI_UPDATE() //
////////////////////////


 - This function first updates column inside table in database <->

 sqli_update(
     [REQ] (string) $tableName,
     [REQ] (assoc array) $values, 
     [REQ] (assoc array) $targets, 
     [REQ] (mysqli_connect()) $database_connection
 ); 

 Errors and returns :
 ------------------------
     - Throws (ArgumentTypeError) for any argument missmatch, 
     - Throws (emptyParameterError) for calls with empty parameters, 
     - Throws (sqlError) for any SQL-related error 
     - Returns (by default) true if all executes without error


////////////////////////////////
// 5. UPDATE LOG AND CREDITS //
//////////////////////////////

///////////////////////
// 5.1 UPDATE LOG : //
/////////////////////

 < 06/08/2019 v 1.0.0 >
 ------------------------
   - Added mysqli_select() and all documentation.
   ----------------------------------------------
 < 07/08/2019 v 1.0.1 >
 -----------------------
   - Added mysqli_slect_all(),sqli_insert(), sqli_check_insert
   - Added $returnType support for sqli_select().
   -------------------------------------------------
 < 08/08/2019 v 1.0.2 >
 ----------------------
   - Added mysqli_update()
   - Return types renamed from "assoc","result","count","exist" to 'a','r','c','e'
   - Added $operators choice on mysqli_select() call
   - Implemented "smart" optional parameter decision on sqli_select()
  -------------------------------------------------------------------- 
 < 10/08/2019 v 1.0.3 >
 -----------------------
   - Fixed bugs on mysqli_select_all()
   - Adeed $returnType support to mysqli_select_all() and mysqli_insert()
 
 < 11/08/2019 v 1.0.4 >
 ------------------------
   - Added =, >=, <=, <, >, support to all functions

 < 14/08/2019 v 1.0.5 >
 ------------------------
   - Added mysqli_single_querry()
   - Fixed few bugs with mysqli_select()
   - Removed mysqli_check_insert()

 < 15/08/2019 v 1.0.6 >
 ------------------------
   - Added mysqli_multiple_querry()
   - Updated the documentation


////////////////////
// 5.2 CREDITS : //
//////////////////

  - Made by Mulalić Almir 
  - Created : 06/08/2019
  - Last update : 15/08/2019 
  - Current stable version : 1.0.6
