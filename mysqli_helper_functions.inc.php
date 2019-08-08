<?php
 
function is_assoc($arr) {
    return array_keys($arr) !== range(0, count($arr) - 1);
}
  
// This is a include file that contains basic function to access and fetch from SQL database.
// It's using an MYSQLI secure approach with prepared statements.
// There are currently 5 functions supported : sqli_select(), sqli_select_all() ,sqli_insert() ,sqli_check_insert(),sqli_update()  
// Functions are designed to show exact place where the error has occured
//
// Dictionary :
// [REQ] = Requiered, [OPT] = optional, [DEF] = Default
//
//
// ---------------------
// << SQL/SQLI SELECT >>
// ---------------------
//
// <-> Use this function to select specific values from SQL database <->
//
//  sqli_select(
//      [REQ] ( string ) $values_to_select(comma separated),
//      [REQ] ( string ) $database_name,
//      [REQ] ( [DEF] assoc array ) $targets, 
//      [REQ] ( mysqli_connect() ) $database_connection
//      [OPT] ( string OR char ) $returnType, [DEF] associative array
//      [OPT] ( [DEF] string OR array ) $operator , [DEF] " AND "
//  ); 
// 
//  < NOTE > - $values_to_select can be '*' to select all from database but there is a separate function called mysqli_select_all().
//  < NOTE > - If you need more than 1 operator provide an array of operator for every parametar in $operator, if only one operator is provided it will be used for all parameters.
//  < NOTE > - This function is "smart" which means that the [OPT] parameters will be decided automatically, you dont need to specify the default ones if you need any one thats after the first one.
//
//  Supported return types :
//  ------------------------
//      - a - returns associatve array --> [DEF] can change in $returnType = 'a' <--
//      - r - returns mysqli_result
//      - c - returns count of rows in mysqli result
//      - e - returns true if value exist or false if it didnt exist
//
//  Supported operator types :
//  ------------------------
//      - AND - 
//      - OR - 
// 
//  Errors and returns :
//  ------------------------
//      - returns/throws (ArgumentTypeError) for any argument missmatch, 
//      - returns/throws (emptyParameterError) for calls with empty parameters, 
//      - returns/throws (sqlError) for any SQL-related error 
//      - returns (by default) associative array as final output (can be changed)
//
function sqli_select($requests,$tableName,$targets,$conn,$returnType = 'a',$operators = "AND") {
    
    if(is_array($returnType) && $returnType == "AND" && $returnType == "OR") {
        $operators = $returnType;
        $returnType = 'a';
    }

    if(!is_string($requests))
        throw new Exception('ArgumentTypeError - first parameter must be a string !');
    else if(!is_string($tableName))
        throw new Exception('ArgumentTypeError - second parameter must be a string !');
    else if(!is_assoc($targets))
        throw new Exception('ArgumentTypeError - third parameter must be an associative array !');
    else if(!is_a($conn,'mysqli'))
        throw new Exception('ArgumentTypeError - fourh parameter must be an valid mysqli connection !');
    else if(!is_string($returnType) && !is_char($returnType))
        throw new Exception('ArgumentTypeError - fifth parameter must be a string or char !');
    else if(!is_string($operators) && !is_array($operators))
        throw new Exception('ArgumentTypeError - sixth parameter must be a string or string array !');
    
    if (empty($requests))
        throw new Exception('emptyParameterError - first parameter can not not be empty');
    else if(empty($tableName))
        throw new Exception('emptyParameterError - second parameter can not not be empty');
    else if(empty($targets))
        throw new Exception('emptyParameterError - third parameter can not not be empty');
    else if(empty($conn))
        throw new Exception('emptyParameterError - fourth parameter can not not be empty');
    else if(empty($returnType))
        throw new Exception('emptyParameterError - fifth parameter can not not be empty');
    else if(empty($operators))
        throw new Exception('emptyParameterError - sixth parameter can not not be empty');

    if($returnType != "a" && $returnType != 'r' && $returnType != 'c' && $returnType != 'e')
        throw new Exception('returnTypeError - invalid return type !');

    if(is_string($operators) && $operators != "AND" && $operators != "OR")
        throw new Exception('operatorsTypeError - invalid operator type !');

    $targetsSize = count($targets);
    $targetKeys = array_keys($targets);

    if(is_array($operators) && count($operators) != $targetsSize -1)
        throw new Exception('operatorsCountError - invalid number of operators submited !');
    
    $prepSql = "SELECT " . $requests . " FROM " . $tableName . " WHERE ";
    $oper=0;
    for($i = 0; $i < $targetsSize; ++$i) {
        $prepSql .= $targetKeys[$i];
        $prepSql .= " = ";
        $prepSql .= "?";
         
        if($i < $targetsSize-1) { 
            if(is_string($operators))
                $prepSql .= ' ' . $operators . ' ';
            else 
                $prepSql .= ' ' .$operators[$oper++] . ' ';
        }
           
    }  

    $parameters = array();
    for($i=0; $i<$targetsSize;$i++) {
        array_push($parameters,$targets[$targetKeys[$i]]);   
    }


    var_dump($prepSql);
    $stmt = mysqli_stmt_init($conn);

    if(!mysqli_stmt_prepare($stmt,$prepSql))
        throw new Exception ('SQLError - check your SQL parameters');

    mysqli_stmt_bind_param($stmt, str_pad("",$targetsSize,"s"), ...$parameters);
    mysqli_stmt_execute($stmt);
    
    if($returnType == "a") {
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    } else if ($returnType == "r")
        return mysqli_stmt_get_result($stmt);
    else if ($returnType == "c") {
        mysqli_stmt_store_result($stmt);
        return mysqli_stmt_num_rows($stmt);        
    } else if ($returnType == "e") {
        mysqli_stmt_store_result($stmt);
        $resultCheck = mysqli_stmt_num_rows($stmt);
        if($resultCheck == 1)
            return true;
        else
            return false;
    }

    return mysqli_fetch_assoc($result);

}
//
//  EXAMPLE :
//  ------------ 
//  sqli_select(
//      (email,password),
//      (userinformation),
//      array("username" => "foo"), 
//      $database_connection
//  );
//
// OR
//
//  sqli_select(
//      (email,password),
//      (userinformation),
//      array("username" => "foo"), 
//      $database_connection
//  ); 
//
// -------------------------
// << SQL/SQLI SELECT ALL >>
// -------------------------
//
//
// <-> Use this function to select all values from SQL database <->
//
//  sqli_select_all(
//      [REQ] (string) $database_name,
//      [REQ] (assoc array) $targets, 
//      [REQ] (mysqli_connect()) $database_connection
//      [OPT] (string) $returnType, [DEF] associative array
//  ); 
// 
//  Supported return types :
//  ------------------------
//      - asoc - returns associatve array --> [DEF] <--
//      - result - returns mysqli_result
//      - count - returns count of rows in mysqli result
// 
//  Errors and returns :
//  ------------------------
//      - returns/throws (ArgumentTypeError) for any argument missmatch, 
//      - returns/throws (emptyParameterError) for calls with empty parameters, 
//      - returns/throws (sqlError) for any SQL-related error 
//      - returns (by default) associative array as final output (can be changed)
//
function sqli_select_all($tableName,$targets,$conn,$returnType = "assoc") {
    
    if(!is_string($tableName))
        throw new Exception('ArgumentTypeError - second parameter must be a string !');
    else if(!is_assoc($targets))
        throw new Exception('ArgumentTypeError - third parameter must be an associative array !');
    else if(!is_a($conn,'mysqli'))
        throw new Exception('ArgumentTypeError - fourh parameter must be an valid mysqli connection !');
    else if(!is_string($returnType))
        throw new Exception('ArgumentTypeError - fifth parameter must be a string !');
    
    if(empty($tableName))
        throw new Exception('emptyParameterError - second parameter can not not be empty');
    else if(empty($targets))
        throw new Exception('emptyParameterError - third parameter can not not be empty');
    else if(empty($conn))
        throw new Exception('emptyParameterError - fourth parameter can not not be empty');

    if($returnType != "assoc" && $returnType != 'result' && $returnType != 'count')
        throw new Exception('returnTypeError - invalid return type !');

    $targetsSize = count($targets);
    $targetKeys = array_keys($targets);
    
    $prepSql = "SELECT * FROM " . $tableName . " WHERE ";
    
    for($i = 0; $i < $targetsSize; ++$i) {
        $prepSql .= $targetKeys[$i];
        $prepSql .= " = ";
        $prepSql .= "?";
         
        if($i < $targetsSize-1)
            $prepSql .= " AND ";
            
    }  
    
    $parameters = array();
    for($i=0; $i<$targetsSize;$i++) {
        array_push($parameters,$targets[$targetKeys[$i]]);   
    }

    $stmt = mysqli_stmt_init($conn);

    if(!mysqli_stmt_prepare($stmt,$prepSql))
        throw new Exception ('SQLError - check your SQL parameters');

    mysqli_stmt_bind_param($stmt, str_pad("",$targetsSize,"s"), ...$parameters);
    mysqli_stmt_execute($stmt);
    
    if($returnType == "assoc") {
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    } else if ($returnType == "result")
        return mysqli_stmt_get_result($stmt);
    else if ($returnType == "count") {
        mysqli_stmt_store_result($stmt);
        return mysqli_stmt_num_rows($stmt);        
    }

    return mysqli_fetch_assoc($result);

}
//
//  EXAMPLE :
//  ------------ 
//  sqli_select(
//      (userinformation),
//      array("username" => "foo"), 
//      $database_connection
//  ); 
//
//
// ---------------------
// << SQL/SQLI INSERT >>
// ---------------------
//
//
// <-> Use this function to insert specific values in SQL database <->
//
//  sqli_insert(
//      [REQ] (string) $tableName,
//      [REQ] (assoc array) $values, 
//      [REQ] (mysqli_connect()) $database_connection
//  ); 
// 
//  Errors and returns :
//  ------------------------
//      - returns/throws (ArgumentTypeError) for any argument missmatch, 
//      - returns/throws (emptyParameterError) for calls with empty parameters, 
//      - returns/throws (sqlError) for any SQL-related error 
//      - returns (by default) true if all executes without error
//
function sqli_insert($tableName,$values,$conn) {
    
    if(!is_string($tableName))
        throw new Exception('ArgumentTypeError - first parameter must be a string !');
    else if(!is_assoc($values))
        throw new Exception('ArgumentTypeError - second parameter must be an associative array !');
    else if(!is_a($conn,'mysqli'))
        throw new Exception('ArgumentTypeError - third parameter must be an valid mysqli connection !');
    
    if(empty($tableName))
        throw new Exception('emptyParameterError - first parameter can not not be empty');
    else if(empty($values))
        throw new Exception('emptyParameterError - second parameter can not not be empty');
    else if(empty($conn))
        throw new Exception('emptyParameterError - third parameter can not not be empty');

    $valuesSize = count($values);
    $valuesKeys = array_keys($values);
    
    $prepSql = "INSERT INTO " . $tableName . " (";
    
    for($i = 0; $i < $valuesSize; ++$i) {
        $prepSql .= $valuesKeys[$i];
        if($i < $valuesSize-1)
            $prepSql .= ",";
    }  

    $prepSql .= ") VALUES (";

    for($i = 0; $i < $valuesSize; ++$i) {
        $prepSql .= "?";
        if($i < $valuesSize-1)
            $prepSql .= ",";
    } 

    $prepSql .= ")";

    $parameters = array();
    for($i=0; $i<$valuesSize;++$i) {
        array_push($parameters,$values[$valuesKeys[$i]]);   
    }

    $stmt = mysqli_stmt_init($conn);

    if(!mysqli_stmt_prepare($stmt,$prepSql))
        throw new Exception ('SQLError - check your SQL parameters');

    mysqli_stmt_bind_param($stmt, str_pad("",$valuesSize,"s"), ...$parameters);
    mysqli_stmt_execute($stmt);
    
    return true;

}
//
//  EXAMPLE :
//  ---------
// sqli_insert(
//      array(
//          "firstName"=>"foo",
//          "lastName"=>"bar",
//          "username"=>"foobar"
//      ),
//      $table_name,
//      $database_connection
// );
//
//
// ---------------------
// << SQL/SQLI CHECK INSERT >>
// ---------------------
//
// <-> This function first checks if the value exist in the database <->
// <-> Use this function to insert unqie values into database <->
//
//  sqli_insert(
//      [REQ] (assoc array) $values_to_check,
//      [REQ] (string) $tableName,
//      [REQ] (assoc array) $values, 
//      [REQ] (mysqli_connect()) $database_connection
//  ); 
// 
//  Errors and returns :
//  ------------------------
//      - returns/throws (ArgumentTypeError) for any argument missmatch, 
//      - returns/throws (emptyParameterError) for calls with empty parameters, 
//      - returns/throws (sqlError) for any SQL-related error 
//      - returns (by default) true if insert was successful or false if there are tables that exist with same parameter
//
function sqli_check_insert($checkValues,$tableName,$values,$conn) {
    
    if(!is_assoc($checkValues))
        throw new Exception('ArgumentTypeError - first parameter must be a string !');
    else if(!is_string($tableName))
        throw new Exception('ArgumentTypeError - first parameter must be a string !');
    else if(!is_assoc($values))
        throw new Exception('ArgumentTypeError - second parameter must be an associative array !');
    else if(!is_a($conn,'mysqli'))
        throw new Exception('ArgumentTypeError - third parameter must be an valid mysqli connection !');
    
    if(empty($checkValues))
        throw new Exception('emptyParameterError - first parameter can not not be empty');
    else if(empty($tableName))
        throw new Exception('emptyParameterError - first parameter can not not be empty');
    else if(empty($values))
        throw new Exception('emptyParameterError - second parameter can not not be empty');
    else if(empty($conn))
        throw new Exception('emptyParameterError - third parameter can not not be empty');

    $valuesKeys = array_keys($checkValues);
  
    if(sqli_select($valuesKeys[0],$tableName,array($valuesKeys[0] => $checkValues[$valuesKeys[0]]),$conn,"exists"))
        return false;
    
    $valuesSize = count($values);
    $valuesKeys = array_keys($values);
    
    $prepSql = "INSERT INTO " . $tableName . " (";
    
    for($i = 0; $i < $valuesSize; ++$i) {
        $prepSql .= $valuesKeys[$i];
        if($i < $valuesSize-1)
            $prepSql .= ",";
    }  

    $prepSql .= ") VALUES (";

    for($i = 0; $i < $valuesSize; ++$i) {
        $prepSql .= "?";
        if($i < $valuesSize-1)
            $prepSql .= ",";
    } 

    $prepSql .= ")";

    $parameters = array();
    for($i=0; $i<$valuesSize;++$i) {
        array_push($parameters,$values[$valuesKeys[$i]]);   
    }

    $stmt = mysqli_stmt_init($conn);

    if(!mysqli_stmt_prepare($stmt,$prepSql))
        throw new Exception ('SQLError - check your SQL parameters');

    mysqli_stmt_bind_param($stmt, str_pad("",$valuesSize,"s"), ...$parameters);
    mysqli_stmt_execute($stmt);
    
    return true;

}
//
//  EXAMPLE :
//  ---------
// sqli_check_insert(
//    array(
//          "username"=>"foo",
//      ),
//      $table_name,
//      array(
//          "firstName"=>"foo",
//          "lastName"=>"bar",
//          "username"=>"foobar"
//      ),
//      $database_connection
// );
//
//
// ---------------------
// << SQL/SQLI UPDATE >>
// ---------------------
//
//
// <-> This function first updates column inside table in database <->
//
//  sqli_update(
//      [REQ] (string) $tableName,
//      [REQ] (assoc array) $values, 
//      [REQ] (assoc array) $targets, 
//      [REQ] (mysqli_connect()) $database_connection
//  ); 
// 
//  Errors and returns :
//  ------------------------
//      - returns/throws (ArgumentTypeError) for any argument missmatch, 
//      - returns/throws (emptyParameterError) for calls with empty parameters, 
//      - returns/throws (sqlError) for any SQL-related error 
//      - returns (by default) true if all executes without error
//
function sqli_update($tableName,$values,$targets,$conn) {
    
    if(!is_string($tableName))
        throw new Exception('ArgumentTypeError - first parameter must be a string !');
    else if(!is_assoc($values))
        throw new Exception('ArgumentTypeError - second parameter must be an associative array !');
    else if(!is_a($conn,'mysqli'))
        throw new Exception('ArgumentTypeError - third parameter must be an valid mysqli connection !');
    
    if(empty($tableName))
        throw new Exception('emptyParameterError - first parameter can not not be empty');
    else if(empty($values))
        throw new Exception('emptyParameterError - second parameter can not not be empty');
    else if(empty($conn))
        throw new Exception('emptyParameterError - third parameter can not not be empty');
    
    $valuesSize = count($values);
    $valuesKeys = array_keys($values);
    
    $targetsSize = count($targets);
    $targetsKeys = array_keys($targets);

    $prepSql = "UPDATE " . $tableName . " SET ";
    
    for($i = 0; $i < $valuesSize; ++$i) {
        $prepSql .= $valuesKeys[$i];
        $prepSql .= " = ?";
        if($i < $valuesSize-1)
            $prepSql .= " AND ";
    }  

    $prepSql .= " WHERE ";

    for($i = 0; $i < $targetsSize; ++$i) {
        $prepSql .= $targetsKeys[$i];
        $prepSql .= "= ?";
        if($i < $valuesSize-1)
            $prepSql .= "AND ";
    } 

    $parameters = array();
    for($i=0; $i<$valuesSize;++$i) {
        array_push($parameters,$values[$valuesKeys[$i]]);   
    }

    for($i=0; $i<$targetsSize;++$i) {
        array_push($parameters,$targets[$targetsKeys[$i]]);   
    }

    $stmt = mysqli_stmt_init($conn);

    if(!mysqli_stmt_prepare($stmt,$prepSql))
        throw new Exception ('SQLError - check your SQL parameters');

    mysqli_stmt_bind_param($stmt, str_pad("",$valuesSize+$targetsSize,"s"), ...$parameters);
    mysqli_stmt_execute($stmt);
    
    return true;

}
//
//  EXAMPLE :
//  ---------
// sqli_update(
//    $tableName,   
//    array(
//          "firstName"=>"foo",
//          "lastName"=>"bar",
//      ),
//    array(
//          "username"=>"foobar",
//      ),
//      $database_connection
// );
//
//
// ------------
// UPDATES LOG:
// ------------
// < 06/08/2019 v1.0.0>
// :::::::::::::::::::::
// - Added sqli_select() and all documentation.
// ----------------------------------------------
// < 07/08/2019 v.1.0.1>
// ::::::::::::::::::::::
// - Added sqli_slect_all(),sqli_insert() and sqli_check_insert().
// - Added $returnType support for sqli_select().
// -------------------------------------------------
// < 08/08/2019 v.1.0.2>
// ::::::::::::::::::::::
// - Added sqli_update()
// - Return types renamed from "assoc","result","count","exist" to 'a','r','c','e'
// - Added $operators choice on sqli_select() call
// - Implemented "smart" optional parameter decision on sqli_select()
//
//
// -------
// TO DO :
// -------
// - bind parameters format
// - ALTER TABLE
// - DELETE 
// - AVG
// - BETWEEN
// - COUNT ?
// - IS NULL/IS NOT NULL
// - LIMIT
//
//
// ---------------------
// PROJECT INFORMATION : 
// ---------------------
// Made by Mulalić Almir 
// Created : 06/08/2019
// Last update : 08/08/2019 
// Current version : 1.0.2
//
//
?>