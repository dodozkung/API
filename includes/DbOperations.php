<?php 

    

    class DbOperations{


        private $con; 
    
        function __construct(){
            require_once dirname(__FILE__) . '/DbConnect.php';
            $db = new DbConnect; 
            $this->con = $db->connect(); 
        }

        public function createUser($wallet_id ,$balance, $username, $password, $name, $address, $idcard, $passconfirm, $phone, $status, $status2){
            
           if(!$this->isEmailExist($username)){
                $stmt = $this->con->prepare("INSERT INTO members (wallet_id , balance, username, password, name, address, idcard, passconfirm, phone, status, status2) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssssssss",$wallet_id, $balance, $username, $password, $name, $address, $idcard, $passconfirm, $phone, $status, $status2);
                if($stmt->execute()){
                    return USER_CREATED; 
                }else{
                    return USER_FAILURE;
                }
           }
           return USER_EXISTS; 
        }

    

        public function userLogin($username, $password){
            if($this->isEmailExist($username)){
                $hashed_password = $this->getUsersPasswordByEmail($username); 
                if(password_verify($password, $hashed_password)){
                    return USER_AUTHENTICATED;
                }else{
                    return USER_PASSWORD_DO_NOT_MATCH; 
                }
            }else{
                return USER_NOT_FOUND; 
            }
        }

        private function getUsersPasswordByEmail($username){
            $stmt = $this->con->prepare("SELECT password FROM members WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute(); 
            $stmt->bind_result($password);
            $stmt->fetch(); 
            return $password; 
        }

        public function getAllUsers(){
            $stmt = $this->con->prepare("SELECT id, email, name, school FROM users;");
            $stmt->execute(); 
            $stmt->bind_result($id, $email, $name, $school);
            $users = array(); 
            while($stmt->fetch()){ 
                $user = array(); 
                $user['id'] = $id; 
                $user['email']=$email; 
                $user['name'] = $name; 
                $user['school'] = $school; 
                array_push($users, $user);
            }             
            return $users; 
        }

        public function getUserByEmail($username){
            $stmt = $this->con->prepare("SELECT N_ID, wallet_id, balance, username, name, idcard, passconfirm, phone, status, status2 FROM members WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute(); 
            $stmt->bind_result($N_ID, $wallet_id, $balance, $username, $name, $idcard, $passconfirm, $phone, $status, $status2);
            $stmt->fetch(); 
            $user = array(); 
            $user['N_ID'] = $N_ID; 
            $user['wallet_id'] = $wallet_id; 
            $user['balance']=$balance; 
            $user['username'] = $username; 
            $user['name'] = $name;
            $user['idcard'] = $idcard;
            $user['passconfirm'] = $passconfirm;
            $user['phone'] = $phone; 
            $user['status'] = $status;
            $user['status2'] = $status2;
            return $user; 
        }

        public function updateUser($email, $name, $school, $id){
            $stmt = $this->con->prepare("UPDATE users SET email = ?, name = ?, school = ? WHERE id = ?");
            $stmt->bind_param("sssi", $email, $name, $school, $id);
            if($stmt->execute())
                return true; 
            return false; 
        }

        public function updatePassword($currentpassword, $newpassword, $email){
            $hashed_password = $this->getUsersPasswordByEmail($email);
            
            if(password_verify($currentpassword, $hashed_password)){
                
                $hash_password = password_hash($newpassword, PASSWORD_DEFAULT);
                $stmt = $this->con->prepare("UPDATE users SET password = ? WHERE email = ?");
                $stmt->bind_param("ss",$hash_password, $email);

                if($stmt->execute())
                    return PASSWORD_CHANGED;
                return PASSWORD_NOT_CHANGED;

            }else{
                return PASSWORD_DO_NOT_MATCH; 
            }
        }

        public function deleteUser($wallet_id){
            $stmt = $this->con->prepare("DELETE FROM members WHERE wallet_id = ?");
            $stmt->bind_param("i", $wallet_id);
            if($stmt->execute())
                return true; 
            return false; 
        }

        private function isEmailExist($username){
            $stmt = $this->con->prepare("SELECT wallet_id FROM members WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute(); 
            $stmt->store_result(); 
            return $stmt->num_rows > 0;  
        }

        public function getDataUser($wallet_id){
            $stmt = $this->con->prepare("SELECT balance, name, idcard, status FROM members WHERE wallet_id = ?");
            $stmt->bind_param("i", $wallet_id);
            $stmt->execute(); 
            $stmt->bind_result($balance, $name, $idcard, $status);
            $stmt->fetch(); 
            $user = array();  
            $user['balance']=$balance; 
            $user['name'] = $name;
            $user['idcard'] = $idcard;
            $user['status'] = $status;
            return $user; 
        }

        public function SeachUser($wallet_id){
            $torf = false;
            $stmt = $this->con->prepare("SELECT name FROM members WHERE wallet_id = ?");
            $stmt->bind_param("i", $wallet_id);
            $stmt->execute(); 
            $stmt->bind_result($name);
            $stmt->fetch(); 
            $user = array();  
            // $user['balance']=$balance; 
            if($name != null){

            $user['name1'] = $name;

            return $user;
        }else{
            $user = false;
            return $user; 

        }
            // $user['idcard2'] = $idcard;
            // $user['status'] = $status;
        }

        // public function chSeachUser($wallet_id){
        //     if($this->SeachUser($wallet_id)){
        //         $WID = $this->SeachUser($wallet_id); 
        //         if($WID = $wallet_id){
        //             return WIDFOUND;
        //         }else{
        //             return WIDNOTFOUND; 
        //         }
        //     }else{
        //         return USER_NOT_FOUND; 
        //     }
        // }
        

        // private function chwalletid($wallet_id){
        //     $stmt = $this->con->prepare("SELECT wallet_id FROM members WHERE wallet_id = ?");
        //     $stmt->bind_param("i", $wallet_id);
        //     $stmt->execute(); 
        //     $stmt->bind_result($name);
        //     $stmt->fetch(); 
        //     $user = array();  
        //     // $user['balance']=$balance; 
        //     $user['name1'] = $name;
        //     // $user['idcard2'] = $idcard;
        //     // $user['status'] = $status;
        //     return $user; 
        // }
        

        public function UpdateData($value1,$value2,$sql){
            $stmt = $this->con->prepare($sql);
            $stmt->bind_param("di", $value2,$value1);
            $stmt->execute();
               
        }
        public function SearchData($value1,$sql){
            $stmt = $this->con->prepare($sql);
            $stmt->bind_param("i", $value1);
            $stmt->execute();
            $stmt->bind_result($data);
            $stmt->fetch();  //กระจายข้อมูลจาก db ให้อยู่ในรูปแบบ array
            return $data;
        }
        public function Search2Data($value1,$sql){ // "is"
            $stmt = $this->con->prepare($sql);
            $stmt->bind_param("i", $value1);
            $stmt->execute();
            $stmt->bind_result($data1,$data2);
            $stmt->fetch();  //กระจายข้อมูลจาก db ให้อยู่ในรูปแบบ array
            $result = array();  
            $result['result1'] = $data1;
            $result['result2'] = $data2;
            return $result;
        }

        public function Transfer($wallet_id,$EndAccID,$Amout){
            $cantransfer = false; // เช็คโอนได้ไหม
            (float)$oldBalance_UserTranfor = $this->SearchData($wallet_id,"select balance from members where wallet_id = ?");
            (float)$oldBalance_UserReceive = $this->SearchData($EndAccID,"select balance from members where wallet_id = ?");

            

            if((float)$oldBalance_UserTranfor >= (float)$Amout){
                $cantransfer = true;
            }
            if($cantransfer){
                $this->UpdateData($wallet_id,(float)$oldBalance_UserTranfor-(float)$Amout,"UPDATE members SET balance= ?  WHERE wallet_id = ?");
                $this->UpdateData($EndAccID,(float)$oldBalance_UserReceive+(float)$Amout,"UPDATE members SET balance= ?  WHERE wallet_id = ?");
            }
            
            return $cantransfer;

        }

        public function TransitionT($wallet_id, $Data , $Typetransfer, $Amount, $EndAccID){
            $stmt = $this->con->prepare("INSERT INTO transactions (wallet_id, Date, Typetransfer, Amount, EndAccID) VALUES (?, NOW(), ?, ?, ?)");
            $stmt->bind_param("ssss", $wallet_id , $Typetransfer, $Amount, $EndAccID);
            $stmt->execute();
            // if($stmt->execute()){
            //     return TransitionTrue; 
            // }else{
            //     return TransitionFalse;
            // }
        }

        public function checkPassCon($wallet_id){
            $stmt = $this->con->prepare("SELECT name FROM members WHERE wallet_id = ?");
            $stmt->bind_param("i", $wallet_id);
            $stmt->execute(); 
            $stmt->bind_result($name);
            $stmt->fetch(); 
            $user = array();  
            // $user['balance']=$balance; 
            $user['name1'] = $name;
            // $user['idcard2'] = $idcard;
            // $user['status'] = $status;
            return $user; 
        }

        public function GenID(){   

            $stmt = $this->con->prepare("SELECT MAX(wallet_id) AS id FROM members");
            $stmt->execute(); 
            $stmt->bind_result($id);
            $stmt->fetch(); 
            $user = array();  
            $user['id']=$id; 
            //return $Test;
            if($user['id'] != ''){
                $count = $user['id'];
                $b = (int)$count;
                $x = $b + 1;
                $c = strval($x);
                for($i = 0;$i<6;$i++){ 
                    if(strlen($c) < 6){
                        $c = '0' . $c;
                    }
                }
                return $c;
            }else{
                return '000000';
            }
              
            
                
        
    }

        

        public function getlastid(){
            $stmt = $this->con->prepare("SELECT MAX(wallet_id) AS wallet_id FROM members");
            $stmt->execute(); 
            return $stmt->bind_result($wallet_id);
        }

        public function Report(){
            $stmt = $this->con->prepare("SELECT wallet_id ,N_ID ,Date ,Typetransfer ,Amount , EndAccID FROM transactions WHERE wallet_id = 000001");
            // $stmt->bind_param("i", $wallet_id);
            $stmt->execute(); 
            $stmt->bind_result($wallet_id, $N_ID, $Date, $Typetransfer, $Amount, $EndAccID);
            $user = array();  
            while($stmt->fetch()){
                $temp = array();
                $temp['wallet_id'] = $wallet_id; 
                $temp['N_ID'] = $N_ID; 
                $temp['Date'] = $Date; 
                $temp['Typetransfer'] = $Typetransfer; 
                $temp['Amount'] = $Amount; 
                $temp['EndAccID'] = $EndAccID; 
                array_push($user, $temp);
                }
            return $user;

        }

        public function Reportip($wallet_id){
            $stmt = $this->con->prepare("SELECT wallet_id ,N_ID ,Date ,Typetransfer ,Amount , EndAccID FROM transactions WHERE wallet_id = ?" );
            $stmt->bind_param("i", $wallet_id);
            $stmt->execute(); 
            $stmt->bind_result($wallet_id, $N_ID, $Date, $Typetransfer, $Amount, $EndAccID);
            $user = array();  
            while($stmt->fetch()){
                $temp = array();
                $temp['wallet_id'] = $wallet_id; 
                $temp['N_ID'] = $N_ID; 
                $temp['Date'] = $Date; 
                $temp['Typetransfer'] = $Typetransfer; 
                $temp['Amount'] = $Amount; 
                $temp['EndAccID'] = $EndAccID; 
                array_push($user, $temp);
                }
            return $user;

        }

        public function Reporttest($wallet_id,$EndAccID){
            $stmt = $this->con->prepare("SELECT wallet_id ,N_ID ,Date ,Typetransfer ,Amount , EndAccID FROM transactions WHERE wallet_id = ? or EndAccID = ? order by N_ID DESC " );
            $stmt->bind_param("ii", $wallet_id,$EndAccID);
            $stmt->execute(); 
            $stmt->bind_result($wallet_id, $N_ID, $Date, $Typetransfer, $Amount, $EndAccID);
            $user = array();  
            while($stmt->fetch()){
                $temp = array();
                $temp['wallet_id'] = $wallet_id; 
                $temp['N_ID'] = $N_ID; 
                $temp['Date'] = $Date; 
                $temp['Typetransfer'] = $Typetransfer; 
                $temp['Amount'] = $Amount; 
                $temp['EndAccID'] = $EndAccID; 
                array_push($user, $temp);
                }
            return $user;

        }

        public function Reportdw($EndAccID){
            $stmt = $this->con->prepare("SELECT wallet_id ,N_ID ,Date ,Typetransfer ,Amount , EndAccID FROM transactions WHERE EndAccID = ?  and Typetransfer in ('withdraw','deposit')  ");
            $stmt->bind_param("i",$EndAccID);
            $stmt->execute(); 
            $stmt->bind_result($wallet_id, $N_ID, $Date, $Typetransfer, $Amount, $EndAccID);
            $user = array();  
            while($stmt->fetch()){
                $temp = array();
                $temp['wallet_id'] = $wallet_id; 
                $temp['N_ID'] = $N_ID; 
                $temp['Date'] = $Date; 
                $temp['Typetransfer'] = $Typetransfer;
                $temp['Amount'] = $Amount; 
                $temp['EndAccID'] = $EndAccID; 
                array_push($user, $temp);
                }
            return $user;

        }

        function compareByTimeStamp($time1, $time2) 
{ 
    if (strtotime($time1) < strtotime($time2)) 
        return 1; 
    else if (strtotime($time1) > strtotime($time2))  
        return -1; 
    else
        return 0; 
} 
  

    }
