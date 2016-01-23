<?php

require_once 'utils.php';

date_default_timezone_set('Australia/Brisbane');


//POSTING and SELECTING the scanned RFID tag
$p_RFID = $_POST['rfidscan']; //get and store from the web address
//$p_RFID = (string)$p_RFIDa;
echo "hello ".$_POST['rfidscan']." ";


//$sql = "SELECT Item_ID, Times_Worn FROM Item WHERE RFID_Tag = :p_rfid";
$stmt = $db->prepare("SELECT Item_ID, Times_Worn FROM Item WHERE RFID_Tag = :p_rfid");
$stmt->bindParam(":p_rfid", $p_RFID, PDO::PARAM_STR);
$stmt->execute();
$result = $stmt->fetch();
$item_id = $result["Item_ID"];

echo "item id: ".$item_id;
$times_worn = $result["Times_Worn"];

$new_times_worn = $times_worn + 1; //incrementing

//Get the temperature
//read the json file contents code from http://www.startutorial.com/articles/view/php-curl
//step1
$cSession = curl_init(); 
//step2
curl_setopt($cSession,CURLOPT_URL,"http://api.openweathermap.org/data/2.5/weather?q=Brisbane&&units=metric&APPID=3f66b3cd437c6662ec552179273e2832");
curl_setopt($cSession,CURLOPT_RETURNTRANSFER,true);
curl_setopt($cSession,CURLOPT_HEADER, false); 
//step3
$result=curl_exec($cSession);
//step4
curl_close($cSession);

$data = json_decode($result, true);
$temperature = $data[main][temp];

//INSERT Date_Worn into Wearing_Data
try 
{
    $stmt = $db->prepare("INSERT INTO Wearing_Data (Date_Worn, Item_ID, Temperature) VALUES (:date_worn, :item_id, :temperature)");
    
    $stmt->bindValue(":date_worn", date("Y-m-d H:i:s"), PDO::PARAM_STR);
    $stmt->bindValue(":item_id", $item_id, PDO::PARAM_STR);
    $stmt->bindValue(":temperature", $temperature, PDO::PARAM_STR);
    
    $stmt->execute();
                     
    echo "1 data pushed to Minimize db";
}
catch (PDOException $e)
{
    echo "Error: ", $e->getMessage();
}


//UPDATE Date_Last_Worn and Times_Worn into Item
try 
{
    $stmt = $db->prepare("UPDATE Item SET Times_Worn=:new_times_worn, Date_Last_Worn=:date_worn WHERE Item_ID=:item_id");
    
    $stmt->bindValue(":new_times_worn", $new_times_worn, PDO::PARAM_STR);
    $stmt->bindValue(":date_worn", date("Y-m-d H:i:s"), PDO::PARAM_STR);
    $stmt->bindValue(":item_id", $item_id, PDO::PARAM_STR);
    
    $stmt->execute();
                     
    echo "2 data pushed to Minimize db";
}
catch (PDOException $e)
{
    echo "Error: ", $e->getMessage();
}

$db = null;

?>