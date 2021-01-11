<?php 

    class DbOperations{

        private $con; 
    
        function __construct(){
            require_once dirname(__FILE__) . '/DbConnect.php';
            $db = new DbConnect; 
            $this->con = $db->connect(); 
        }

        public function createUser($balance, $username, $password, $name, $address, $idcard, $passconfirm, $phone, $status){
           if(!$this->isEmailExist($username)){
                $stmt = $this->con->prepare("INSERT INTO members (balance, username, password, name, address, idcard, passconfirm, phone, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'P')");
                $stmt->bind_param("ssssssss", $balance, $username, $password, $name, $address, $idcard, $passconfirm, $phone);
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
            $stmt = $this->con->prepare("SELECT wallet_id, balance, username, name, idcard, passconfirm, phone, status FROM members WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute(); 
            $stmt->bind_result($wallet_id, $balance, $username, $name, $idcard, $passconfirm, $phone, $status);
            $stmt->fetch(); 
            $user = array(); 
            $user['wallet_id'] = $wallet_id; 
            $user['balance']=$balance; 
            $user['username'] = $username; 
            $user['name'] = $name;
            $user['idcard'] = $idcard;
            $user['passconfirm'] = $passconfirm;
            $user['phone'] = $phone; 
            $user['status'] = $status;
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

        public function deleteUser($id){
            $stmt = $this->con->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $id);
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
    }