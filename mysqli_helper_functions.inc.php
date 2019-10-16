<?php

/* FUNCTIONS THAT YOU CAN CALL BUT ARE NOT PART OF API ITSELF*/
/* THEY ARE USED TO MAKE THE CODE MORE UDNERSTANDABLE AND EASIER TO READ */

function is_assoc($arr) {
    return array_keys($arr) !== range(0, count($arr) - 1);
}

/* MAIN FUNCTIONS */

function mysqli_single_query($prepSql,$param,$conn,$returnType = 'r') {
        
    if (!is_string($prepSql))
        throw new Exception ('argumentTypeError - SQL statement must be a string');
    else if(!is_string($param) && !is_array($param))
        throw new Exception ('argumentTypeError - Parameters must be an array or string');
    else if(!is_a($conn,'mysqli'))
        throw new Exception('argumentTypeError - Connection must be a valid mysqli connection !');
    
    
    if(empty($prepSql))
        throw new Exception('emptyArgumentError - SQL statement cannot be empty ');
    else if(empty($param))
        throw new Exception('emptyArgumentError - Parameters array cannot be empty ');
    else if(empty($conn))
        throw new Exception('emptyArgumentError - Connection cannot be empty ');

    $stmt = mysqli_stmt_init($conn);

    if(!mysqli_stmt_prepare($stmt,$prepSql))
        throw new Exception ('SQLError - check your SQL parameters');
    
    if(is_array($param)) {
        $paramType=array();
        for($i=0;$i<count($param);$i++) {
            if(is_string($param[$i]))
                array_push($paramType,'s');
            else if(is_int($param[$i]))
                array_push($paramType,'i');
            else if(is_double($param[$i]))
                array_push($paramType,'d');
            else if(is_a($param[$i],'blob'))
                array_push($paramType,'b');
        }
        
        mysqli_stmt_bind_param($stmt, implode($paramType) , ...$param);
    } else if(is_string($param)) {
        $paramType = '';
        if(is_string($param))
            $paramType = 's';
        else if(is_int($param))
            $paramType = 'i';
        else if(is_double($param))
            $paramType = 'd';
        else if(is_a($param,'blob'))
            $paramType = 'b';

        mysqli_stmt_bind_param($stmt, $paramType, $param);
    }
    
    mysqli_stmt_execute($stmt);

    if($returnType == 'r')
        return mysqli_stmt_get_result($stmt);
    else if($returnType == 'a'){
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    } else if ($returnType == 'c') {
        mysqli_stmt_store_result($stmt);
        return mysqli_stmt_num_rows($stmt);  
    } else if ($returnType == 'e') {
        mysqli_stmt_store_result($stmt);
        $resultCheck = mysqli_stmt_num_rows($stmt);
        if($resultCheck == 1)
            return true;
        else
            return false;
    }

    return true;
}

function mysqli_multi_query($requests,$conn) {
    
    if(count($requests) < 2)
        throw new Exception('argumentCountError - This function requires minimum of 2 sql requests');
    
    if (!is_array($requests))
        throw new Exception ('argumentTypeError - Requests must be an array');
    else if(!is_a($conn,'mysqli'))
        throw new Exception('argumentTypeError - Connection must be a valid mysqli connection !');

    foreach($requests as $row) {
        if(!is_array($row))
            throw new Exception('argumentTypeError - All requests parameters must be an array');
        else if(empty($row))
            throw new Exception('emptyArgumentError - All requests parameters cannot be empty');
    }

    if(empty($conn))
        throw new Exception('emptyArgumentError - Connection cannot be empty !');
    
    for($i=0;$i<count($requests);$i++) {    
        if($requests[$i][2] == null)
            break;
        else if($requests[$i][2] == 'c') {
            $count = mysqli_single_execute($requests[$i][0],$requests[$i][1],$conn,'c');
            if($requests[$i][2] == '==') {
                if($count != $requests[$i][3]) 
                    return false;
            }   else if($requests[$i][2] == '===') {
                if($count !== $requests[$i][2]) 
                    return false;
            }   else if($requests[$i][2] == '!=') {
                if($count == $requests[$i][2]) 
                    return false;
            }   else if($requests[$i][2] == '!==') {
                if($count === $requests[$i][2]) 
                    return false;
            }   else if($requests[$i][2] == '<') {
                if($count > $requests[$i][2]) 
                    return false;
            }   else if($requests[$i][2] == '>') {
                if($count < $requests[$i][2]) 
                    return false;
            }   else if($requests[$i][2] == '<=') {
                if($count > $requests[$i][2]) 
                    return false;
            }   else if($requests[$i][2] == '>=') {
                if($count < $requests[$i][2]) 
                    return false;
            }   else if($requests[$i][2] == '%') {
                if($count % $requests[$i][2] == 1) 
                    return false;
            } else 
                throw new Error('operatorTypeError - Invalid operator for count method');       

        } else if($requests[$i][2] == 'e') {
            $exist = mysqli_single_execute($requests[$i][0],$requests[$i][1],$conn,'e');
            if($exist != $requests[$i][3])
                return false;
            
        } else { 
            mysqli_single_execute($requests[$i][0],$requests[$i][1],$conn,'r');
            break;
        }
    }


    return true;
}


/* SIDE FUNCTIONS */

function mysqli_select($requests,$tableName,$targets,$conn,$returnType = 'a',$operators = "AND") {
    
    if(is_array($returnType) && $returnType == "AND" && $returnType == "OR") {
        $operators = $returnType;
        $returnType = 'a';
    }

    if(!is_string($requests) && !is_array($requests))
        throw new Exception('ArgumentTypeError - first parameter must be a string !');
    else if(!is_string($tableName))
        throw new Exception('ArgumentTypeError - second parameter must be a string or array!');
    else if(!is_assoc($targets))
        throw new Exception('ArgumentTypeError - third parameter must be an associative array !');
    else if(!is_a($conn,'mysqli'))
        throw new Exception('ArgumentTypeError - fourh parameter must be an valid mysqli connection !');
    else if(!is_string($returnType))
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

    $req = '';
    if(is_array($requests)) {
        $reqKeys = array_keys($requests);
        for($i = 0; $i<count($reqKeys); $i++){
            $req.=$requests[$reqKeys[$i]];
            
            if($i < count($reqKeys)-1)
                $req .= " AND ";
        }
    } else
        $req = $requests;

    $targetsSize = count($targets);
    $targetKeys = array_keys($targets);

    if(is_array($operators) && count($operators) != $targetsSize -1)
        throw new Exception('operatorsCountError - invalid number of operators submited !');
    
    $prepSql = "SELECT " . $req . " FROM " . $tableName . " WHERE ";

    for($i = 0; $i < $targetsSize; ++$i) {
        $prepSql .= $targetKeys[$i];
        $prepSql .= "?";
         
        if($i < $targetsSize-1) { 
            if(is_string($operators))
                $prepSql .= ' ' . $operators . ' ';
            else 
                $prepSql .= ' ' .$operators[$oper++] . ' ';
        }
           
    }  

    $parameters = array();
    $paramType = array();
    for($i=0; $i<$targetsSize;$i++) { 
        array_push($parameters,$targets[$targetKeys[$i]]);
        
        if(is_string($targets[$targetKeys[$i]]))
            array_push($paramType,'s');
        else if(is_int($targets[$targetKeys[$i]]))
            array_push($paramType,'i');
        else if(is_double($targets[$targetKeys[$i]]))
            array_push($paramType,'d');
        else if(is_a($targets[$targetKeys[$i]],'blob'))
            array_push($paramType,'b');
    }

    $stmt = mysqli_stmt_init($conn);

    if(!mysqli_stmt_prepare($stmt,$prepSql))
        throw new Exception ('SQLError - check your SQL parameters');

    mysqli_stmt_bind_param($stmt, implode($paramType) , ...$param);
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

function mysqli_select_all($tableName,$targets,$conn,$returnType = 'a',$operators = 'AND') {
    
    if(is_array($returnType) && $returnType == "AND" && $returnType == "OR") {
        $operators = $returnType;
        $returnType = 'a';
    }

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

    if($returnType != "a" && $returnType != 'r' && $returnType != 'c' && $returnType != 'e')
        throw new Exception('returnTypeError - invalid return type !');

    $targetsSize = count($targets);
    $targetKeys = array_keys($targets);
    
    $prepSql = "SELECT * FROM " . $tableName . " WHERE ";
    
    for($i = 0; $i < $targetsSize; ++$i) {
        $prepSql .= $targetKeys[$i];
        $prepSql .= "?";
         
        if($i < $targetsSize-1) { 
            if(is_string($operators))
                $prepSql .= ' ' . $operators . ' ';
            else 
                $prepSql .= ' ' .$operators[$oper++] . ' ';
        }
            
    }  

    $parameters = array();
    $paramType = array();

    for($i=0; $i<$targetsSize;$i++) {

        array_push($parameters,$targets[$targetKeys[$i]]);
        
        if(is_string($targets[$targetKeys[$i]]))
            array_push($paramType,'s');
        else if(is_int($targets[$targetKeys[$i]]))
            array_push($paramType,'i');
        else if(is_double($targets[$targetKeys[$i]]))
            array_push($paramType,'d');
        else if(is_a($targets[$targetKeys[$i]],'blob'))
            array_push($paramType,'b');
    }

    $stmt = mysqli_stmt_init($conn);
   
    if(!mysqli_stmt_prepare($stmt,$prepSql))
        throw new Exception ('SQLError - check your SQL parameters');

    mysqli_stmt_bind_param($stmt, implode($paramType) , ...$param);
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

function mysqli_insert($tableName,$values,$conn,$returnType='t') {
    
    if(!is_string($tableName))
        throw new Exception('ArgumentTypeError - first parameter must be a string !');
    else if(!is_assoc($values))
        throw new Exception('ArgumentTypeError - second parameter must be an associative array !');
    else if(!is_a($conn,'mysqli'))
        throw new Exception('ArgumentTypeError - third parameter must be an valid mysqli connection !');
    else if(!is_string($returnType))
        throw new Exception('ArgumentTypeError - fourth parameter must be an valid return type !');
    
    if(empty($tableName))
        throw new Exception('emptyParameterError - first parameter can not not be empty');
    else if(empty($values))
        throw new Exception('emptyParameterError - second parameter can not not be empty');
    else if(empty($conn))
        throw new Exception('emptyParameterError - third parameter can not not be empty');
    else if(empty($conn))
        throw new Exception('emptyParameterError - fourth parameter can not not be empty');

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
    $paramType = array();

    for($i=0; $i<$targetsSize;$i++) {

        array_push($parameters,$targets[$targetKeys[$i]]);
        
        if(is_string($targets[$targetKeys[$i]]))
            array_push($paramType,'s');
        else if(is_int($targets[$targetKeys[$i]]))
            array_push($paramType,'i');
        else if(is_double($targets[$targetKeys[$i]]))
            array_push($paramType,'d');
        else if(is_a($targets[$targetKeys[$i]],'blob'))
            array_push($paramType,'b');
    }

    $stmt = mysqli_stmt_init($conn);

    if(!mysqli_stmt_prepare($stmt,$prepSql))
        throw new Exception ('SQLError - check your SQL parameters');

    mysqli_stmt_bind_param($stmt, implode($paramType) , ...$param);    
    mysqli_stmt_execute($stmt);

    if($returnType=='t')
        return true;
    else if($returnType=='i')
        return mysqli_insert_id($conn);
}

function mysqli_update($tableName,$values,$targets,$conn) {
    
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
        $prepSql .= "?";
        if($i < $valuesSize-1)
            $prepSql .= " AND ";
    }  

    $prepSql .= " WHERE ";

    for($i = 0; $i < $targetsSize; ++$i) {
        $prepSql .= $targetsKeys[$i];
        $prepSql .= "?";
        if($i < $valuesSize-1)
            $prepSql .= "AND ";
    } 

    $parameters = array();
    $paramType = array();

    for($i=0; $i<$valuesSize;++$i) {
        array_push($parameters,$values[$valuesKeys[$i]]); 
        
        if(is_string($targets[$targetKeys[$i]]))
            array_push($paramType,'s');
        else if(is_int($targets[$targetKeys[$i]]))
            array_push($paramType,'i');
        else if(is_double($targets[$targetKeys[$i]]))
            array_push($paramType,'d');
        else if(is_a($targets[$targetKeys[$i]],'blob'))
            array_push($paramType,'b');
    }

    for($i=0; $i<$targetsSize;$i++) {

        array_push($parameters,$targets[$targetKeys[$i]]);
        
        if(is_string($targets[$targetKeys[$i]]))
            array_push($paramType,'s');
        else if(is_int($targets[$targetKeys[$i]]))
            array_push($paramType,'i');
        else if(is_double($targets[$targetKeys[$i]]))
            array_push($paramType,'d');
        else if(is_a($targets[$targetKeys[$i]],'blob'))
            array_push($paramType,'b');
    }

    $stmt = mysqli_stmt_init($conn);

    if(!mysqli_stmt_prepare($stmt,$prepSql))
        throw new Exception ('SQLError - check your SQL parameters');

    mysqli_stmt_bind_param($stmt, implode($paramType), ...$parameters);
    mysqli_stmt_execute($stmt);
    
    return true;

}

// -------
// TO DO :
// -------
// - DELETE !!!!!!!!!!!!!


?>
