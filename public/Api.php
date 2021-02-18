<?php 
 
 /*
 * Created by Belal Khan
 * website: www.simplifiedcoding.net 
 * Retrieve Data From MySQL Database in Android
 */
 
 //database constants
 define('DB_HOST', 'localhost');
 define('DB_USER', 'root');
 define('DB_PASS', '');
 define('DB_NAME', 'ewallet');
 
 //connecting to database and getting the connection object
 $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
 
 //Checking if any error occured while connecting
 if (mysqli_connect_errno()) {
 echo "Failed to connect to MySQL: " . mysqli_connect_error();
 die();
 }
 
 //creating a query
 $stmt = $conn->prepare("SELECT `wallet_id`, `N_ID`, `Date`, `Typetransfer`, `Amount`, `EndAccID` FROM transition WHERE wallet_id = 20;");
 
 //executing the query 
 $stmt->execute();
 
 //binding results to the query 
 $stmt->bind_result($wallet_id, $N_ID, $Date, $Typetransfer, $Amount, $EndAccID);
 
 $products = array(); 
 
 //traversing through all the result 
 while($stmt->fetch()){
 $temp = array();
 $temp['wallet_id'] = $wallet_id; 
 $temp['N_ID'] = $N_ID; 
 $temp['Date'] = $Date; 
 $temp['Typetransfer'] = $Typetransfer; 
 $temp['Amount'] = $Amount; 
 $temp['EndAccID'] = $EndAccID; 
 array_push($products, $temp);
 }
 
 //displaying the result in json format 
 echo json_encode($products);