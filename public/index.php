<?php


use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

require '../includes/DbOperations.php';

$app = new \Slim\App([
    'settings'=>[
        'displayErrorDetails'=>true
    ]
]);

$app->add(new Tuupola\Middleware\HttpBasicAuthentication([
    "secure"=>false,
    "users" => [
        "dodozkung" => "123456",
    ]
]));

/* 
    endpoint: createuser
    parameters: email, password, name, school
    method: POST
*/
$app->post('/createuser', function(Request $request, Response $response){

    if(!haveEmptyParameters(array('username', 'password', 'name', 'address', 'idcard', 'passconfirm', 'phone'), $request, $response)){
        
        $request_data = $request->getParsedBody(); 

        // $balance = $request_data['balance'];
        $username = $request_data['username'];
        $password = $request_data['password'];
        $name = $request_data['name'];
        $address = $request_data['address'];
        $idcard = $request_data['idcard'];
        $passconfirm = $request_data['passconfirm'];
        $phone = $request_data['phone'];
        // $status = $request_data['status']; 

        $hash_password = password_hash($password, PASSWORD_DEFAULT);

        $db = new DbOperations; 

        $wallet_id = $db->GenID();
        if ($wallet_id == '000000'){
            $result = $db->createUser($wallet_id ,'0.00', 'admin', '$2y$10$.l6pXvqtAovr/JK46RY5U.f87JxqYkZX6q2bpxpIzRzj.kB9TwER.', 'ภัทรพล รอดเดช', '1624/5', '1234567890123', '000000', '0642428663', 'admin','on');
            $wallet_id = "000001";
        }
        

        $result = $db->createUser($wallet_id ,'0.00', $username, $hash_password, $name, $address, $idcard, $passconfirm, $phone, 'user','on');
        
        if($result == USER_CREATED){

            $message = array(); 
            $message['error'] = false; 
            $message['Text'] = $wallet_id;
            $message['message'] = 'User created successfully';

            $response->write(json_encode($message));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(201);

        }else if($result == USER_FAILURE){

            $message = array(); 
            $message['error'] = true; 
            $message['message'] = 'Some error occurred';

            $response->write(json_encode($message));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(201);    

        }else if($result == USER_EXISTS){
            $message = array(); 
            $message['error'] = true; 
            $message['message'] = 'User Already Exists';

            $response->write(json_encode($message));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(201);    
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(422);    
});

$app->post('/userlogin', function(Request $request, Response $response){

    if(!haveEmptyParameters(array('username', 'password'), $request, $response)){
        $request_data = $request->getParsedBody(); 

        $username = $request_data['username'];
        $password = $request_data['password'];
        
        $db = new DbOperations; 

        $result = $db->userLogin($username, $password);

        if($result == USER_AUTHENTICATED){
            
            $user = $db->getUserByEmail($username);
            $response_data = array();

            $response_data['error']=false; 
            $response_data['message'] = 'Login Successful';
            $response_data['user']=$user; // $request_data[user].user[wal]

            $response->write(json_encode($response_data));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);    

        }else if($result == USER_NOT_FOUND){
            $response_data = array();

            $response_data['error']=true; 
            $response_data['message'] = 'User not exist';

            $response->write(json_encode($response_data));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);    

        }else if($result == USER_PASSWORD_DO_NOT_MATCH){
            $response_data = array();

            $response_data['error']=true; 
            $response_data['message'] = 'Invalid credential';

            $response->write(json_encode($response_data));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);  
        }
    }

    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(422);    
});

$app->get('/allusers', function(Request $request, Response $response){

    $db = new DbOperations; 

    $users = $db->getAllUsers();

    $response_data = array();

    $response_data['error'] = false; 
    $response_data['users'] = $users; 

    $response->write(json_encode($response_data));

    return $response
    ->withHeader('Content-type', 'application/json')
    ->withStatus(200);  

});


$app->put('/updateuser/{id}', function(Request $request, Response $response, array $args){

    $id = $args['id'];

    if(!haveEmptyParameters(array('email','name','school'), $request, $response)){

        $request_data = $request->getParsedBody(); 
        $email = $request_data['email'];
        $name = $request_data['name'];
        $school = $request_data['school']; 
     

        $db = new DbOperations; 

        if($db->updateUser($email, $name, $school, $id)){
            $response_data = array(); 
            $response_data['error'] = false; 
            $response_data['message'] = 'User Updated Successfully';
            $user = $db->getUserByEmail($email);
            $response_data['user'] = $user; 

            $response->write(json_encode($response_data));

            return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(200);  
        
        }else{
            $response_data = array(); 
            $response_data['error'] = true; 
            $response_data['message'] = 'Please try again later';
            $user = $db->getUserByEmail($email);
            $response_data['user'] = $user; 

            $response->write(json_encode($response_data));

            return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(200);  
              
        }

    }
    
    return $response
    ->withHeader('Content-type', 'application/json')
    ->withStatus(200);  

});

$app->put('/updatepassword', function(Request $request, Response $response){

    if(!haveEmptyParameters(array('currentpassword', 'newpassword', 'email'), $request, $response)){
        
        $request_data = $request->getParsedBody(); 

        $currentpassword = $request_data['currentpassword'];
        $newpassword = $request_data['newpassword'];
        $email = $request_data['email']; 

        $db = new DbOperations; 

        $result = $db->updatePassword($currentpassword, $newpassword, $email);

        if($result == PASSWORD_CHANGED){
            $response_data = array(); 
            $response_data['error'] = false;
            $response_data['message'] = 'Password Changed';
            $response->write(json_encode($response_data));
            return $response->withHeader('Content-type', 'application/json')
                            ->withStatus(200);

        }else if($result == PASSWORD_DO_NOT_MATCH){
            $response_data = array(); 
            $response_data['error'] = true;
            $response_data['message'] = 'You have given wrong password';
            $response->write(json_encode($response_data));
            return $response->withHeader('Content-type', 'application/json')
                            ->withStatus(200);
        }else if($result == PASSWORD_NOT_CHANGED){
            $response_data = array(); 
            $response_data['error'] = true;
            $response_data['message'] = 'Some error occurred';
            $response->write(json_encode($response_data));
            return $response->withHeader('Content-type', 'application/json')
                            ->withStatus(200);
        }
    }

    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(422);  
});

$app->delete('/deleteuser/{id}', function(Request $request, Response $response, array $args){
    $wallet_id = $args['id'];

    $db = new DbOperations; 

    $response_data = array();

    if($db->deleteUser($wallet_id)){
        $response_data['error'] = false; 
        $response_data['message'] = 'User has been deleted';    
    }else{
        $response_data['error'] = true; 
        $response_data['message'] = 'Plase try again later';
    }

    $response->write(json_encode($response_data));

    return $response
    ->withHeader('Content-type', 'application/json')
    ->withStatus(200);
});

function haveEmptyParameters($required_params, $request, $response){
    $error = false; 
    $error_params = '';
    $request_params = $request->getParsedBody(); 

    foreach($required_params as $param){
        if(!isset($request_params[$param]) || strlen($request_params[$param])<=0){
            $error = true; 
            $error_params .= $param . ', ';
        }
    }

    if($error){
        $error_detail = array();
        $error_detail['error'] = true; 
        $error_detail['message'] = 'Required parameters ' . substr($error_params, 0, -2) . ' are missing or empty';
        $response->write(json_encode($error_detail));
    }
    return $error; 
}

$app->post('/getDataUser', function(Request $request, Response $response){

    if(!haveEmptyParameters(array('wallet_id'), $request, $response)){
        $request_data = $request->getParsedBody(); 

        $wallet_id = $request_data['wallet_id'];

    $db = new DbOperations; 

    $user = $db->getDataUser($wallet_id);


    $response_data = array();

    $response_data['error'] = false; 
    $response_data['user'] = $user; 

    $response->write(json_encode($response_data));

    return $response
    ->withHeader('Content-type', 'application/json')
    ->withStatus(200);  
    
    }

});

$app->post('/SeachUser', function(Request $request, Response $response){

    if(!haveEmptyParameters(array('wallet_id'), $request, $response)){
        $request_data = $request->getParsedBody(); 

        $wallet_id = $request_data['wallet_id'];

    $db = new DbOperations; 

    $user = $db->SeachUser($wallet_id);


    // $response_data = array();
    if($user != false){

    $response_data['error'] = true; 
    $response_data['message'] = 'Success'; 
    $response_data['user'] = $user; 

    $response->write(json_encode($response_data));

    return $response
    ->withHeader('Content-type', 'application/json')
    ->withStatus(200); 
}else{
    $response_data['error'] = false; 
    $response_data['message'] = 'Fail'; 
    $response_data['user'] = $user;
     

    $response->write(json_encode($response_data));

    return $response
    ->withHeader('Content-type', 'application/json')
    ->withStatus(200); 
} 
    
    }

});

// $app->post('/SeachUser', function(Request $request, Response $response){

//     if(!haveEmptyParameters(array('wallet_id'), $request, $response)){
//         $request_data = $request->getParsedBody(); 

//         $wallet_id = $request_data['wallet_id'];

//     $db = new DbOperations; 

//     $result = $db->chSeachUser($wallet_id);

//         if($result == WIDFOUND){
            
//             $user = $db->SeachUser($wallet_id);
//             // $response_data = array();

//             $response_data['error']=false; 
//             // $response_data['message'] = 'Login Successful';
//             $response_data['user']=$user; // $request_data[user].user[wal]

//             $response->write(json_encode($response_data));

//             return $response
//                 ->withHeader('Content-type', 'application/json')
//                 ->withStatus(200);    

//         }else($result == WIDNOTFOUND){
//             $response_data = array();

//             $response_data['error']=true; 
//             // $response_data['message'] = 'User not exist';

//             $response->write(json_encode($response_data));

//             return $response
//                 ->withHeader('Content-type', 'application/json')
//                 ->withStatus(200);    

//         }
//     }

//     return $response
//         ->withHeader('Content-type', 'application/json')
//         ->withStatus(422);    
// });

$app->post('/Transfer', function(Request $request, Response $response){

    if(!haveEmptyParameters(array('wallet_id','EndAccID','Amout'), $request, $response)){
        $request_data = $request->getParsedBody(); 

        $wallet_id = $request_data['wallet_id'];
        $EndAccID = $request_data['EndAccID'];
        $Amout = $request_data['Amout'];

    $db = new DbOperations; 

    $users = $db->Transfer((int)$wallet_id,(int)$EndAccID,(float)$Amout);

    if($users = true){
    $asd = $db->TransitionT($wallet_id, date("Y-m-d H:i:s") , 'Transfer' , $Amout, $EndAccID);

    $response_data = array();

    $response_data['error'] = false; 
    $response_data['message'] = 'Success'; 
    $response_data['user'] = $users; 
    }
    else{
        $response_data['message'] = 'Fail'; 
    }

    $response->write(json_encode($response_data));

    return $response
    ->withHeader('Content-type', 'application/json')
    ->withStatus(200);  
    


    }

});

$app->post('/TransferQR', function(Request $request, Response $response){

    if(!haveEmptyParameters(array('wallet_id','EndAccID','Amout'), $request, $response)){
        $request_data = $request->getParsedBody(); 

        $wallet_id = $request_data['wallet_id'];
        $EndAccID = $request_data['EndAccID'];
        $Amout = $request_data['Amout'];

    $db = new DbOperations; 

    $users = $db->Transfer((int)$wallet_id,(int)$EndAccID,(float)$Amout);

    if($users = true){
    $asd = $db->TransitionT($wallet_id, date("Y-m-d H:i:s") , 'Pay' , $Amout, $EndAccID);

    $response_data = array();

    $response_data['error'] = false; 
    $response_data['message'] = 'Success'; 
    $response_data['user'] = $users; 
    }
    else{
        $response_data['message'] = 'Fail'; 
    }

    $response->write(json_encode($response_data));

    return $response
    ->withHeader('Content-type', 'application/json')
    ->withStatus(200);  
    


    }

});

$app->get('/Report', function(Request $request, Response $response ,array $args){

    // $wallet_id = $args['id'];

    // if(!haveEmptyParameters(array('wallet_id'), $request, $response)){
        // $request_data = $request->getParsedBody(); 

        // $wallet_id = $request_data['wallet_id'];

    $db = new DbOperations; 

    $user = $db->Report();


    // $response_data = array();

    // $response_data['error'] = false; 
    // $response_data['user'] = $user; 
    $response_data = $user; 

    $response->write(json_encode($response_data));

    return $response
    ->withHeader('Content-type', 'application/json')
    ->withStatus(200);  
    
    // }

}
);

$app->post('/Reportip', function(Request $request, Response $response ,array $args){

    // $wallet_id = $args['id'];

    if(!haveEmptyParameters(array('wallet_id'), $request, $response)){
        $request_data = $request->getParsedBody(); 

        $wallet_id = $request_data['wallet_id'];

    $db = new DbOperations; 

    $user = $db->Reportip($wallet_id);


    // $response_data = array();

    // $response_data['error'] = false; 
    // $response_data['user'] = $user; 
    $response_data = $user; 

    $response->write(json_encode($response_data));

    return $response
    ->withHeader('Content-type', 'application/json')
    ->withStatus(200);  
    

}
}
);

$app->post('/Reportdw', function(Request $request, Response $response ,array $args){

    // $wallet_id = $args['id'];

    if(!haveEmptyParameters(array('EndAccID'), $request, $response)){
        $request_data = $request->getParsedBody(); 

        $EndAccID = $request_data['EndAccID'];


    $db = new DbOperations; 

    $user1 = $db->Reportdw($EndAccID);


    // $response_data = array();

    // $response_data['error'] = false; 
    // $response_data['user'] = $user; 
    $response_data = $user1; 

    $response->write(json_encode($response_data));

    return $response
    ->withHeader('Content-type', 'application/json')
    ->withStatus(200);  
    }
}
);

$app->post('/Reportsum', function(Request $request, Response $response ,array $args){

    // $wallet_id = $args['id'];

    if(!haveEmptyParameters(array('wallet_id','EndAccID'), $request, $response)){
        $request_data = $request->getParsedBody(); 

        $wallet_id =  $request_data['wallet_id'];
        $EndAccID = $request_data['EndAccID'];


    $db = new DbOperations; 

    $user = $db->Reportip($wallet_id);
    $user1 = $db->Reportdw($EndAccID);

    

    $count = 0;
    for($i = 0; $i < count($user);$i++){
        $usersum[$count] = $user[$i];
        $count++;
    }
    for($i = 0; $i < count($user1);$i++){
        $usersum[$count] = $user1[$i];
        $count++;
    }

    // $abhg = sort($usersum,"compareByTimeStamp");
    
    $response_data = $usersum; 

    // $response_data = $user1; 


    $response->write(json_encode($response_data));

    return $response
    ->withHeader('Content-type', 'application/json')
    ->withStatus(200);  
    }
}



);

$app->post('/Reporttest', function(Request $request, Response $response ,array $args){

    // $wallet_id = $args['id'];

    if(!haveEmptyParameters(array('wallet_id','EndAccID'), $request, $response)){
        $request_data = $request->getParsedBody(); 

        $wallet_id =  $request_data['wallet_id'];
        $EndAccID = $request_data['EndAccID'];


    $db = new DbOperations; 

    $user = $db->Reporttest($wallet_id,$EndAccID);
    


    // $abhg = sort($usersum,"compareByTimeStamp");
    
    $response_data = $user; 

    // $response_data = $user1; 


    $response->write(json_encode($response_data));

    return $response
    ->withHeader('Content-type', 'application/json')
    ->withStatus(200);  
    }
}



);



// $app->post('/testid', function(Request $request, Response $response){

//     if(!haveEmptyParameters(array('id'), $request, $response)){
//         $request_data = $request->getParsedBody(); 

//         $id = $request_data['id'];

//     $db = new DbOperations; 

//     $user = $db->checkid($id);


//     // $response_data = array();
//     if($user)

//     $response_data['error'] = false; 
//     $response_data['user'] = $user; 

//     $response->write(json_encode($response_data));

//     return $response
//     ->withHeader('Content-type', 'application/json')
//     ->withStatus(200);  
    
//     }

// });

// $app->post('/TransitionT', function(Request $request, Response $response){

//     if(!haveEmptyParameters(array('wallet_id', 'Amount', 'EndAccID'), $request, $response)){
        
//         $request_data = $request->getParsedBody(); 

//         $wallet_id = $request_data['wallet_id'];
//         // $password = $request_data['password'];
//         // $Typetransfer = $request_data['Typetransfer'];
//         $Amount = $request_data['Amount'];
//         $EndAccID = $request_data['EndAccID'];

//         $db = new DbOperations; 

//         $result = $db->TransitionT($wallet_id, date("Y-m-d H:i:s") , 'Transfer' , $Amount, $EndAccID);
        
//         if($result == TransitionTrue){
            

//             $message = array(); 
//             $message['error'] = true; 
//             // $message['user'] = $result; 
//             $message['message'] = 'ทำรายการสำเร็จ';
            

//             $response->write(json_encode($message));

//             return $response
//                         ->withHeader('Content-type', 'application/json')
//                         ->withStatus(201);

//         }else {

//             $message = array(); 
//             $message['error'] = false; 
//             $message['message'] = 'ทำรายการไม่สำเร็จ';
//             $response_data['user'] = false; 

//             $response->write(json_encode($message));

//             return $response
//                         ->withHeader('Content-type', 'application/json')
//                         ->withStatus(422);    

//         }
//     }
//     return $response
//         ->withHeader('Content-type', 'application/json')
//         ->withStatus(422);    
// });


$app->run();

