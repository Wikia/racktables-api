<?php

include("api_includes.php");


switch($_SERVER['REQUEST_METHOD']) {

   case 'GET':
      $ret_array = array();
      if($_GET['host'] ) {
      
         $server_id = getServerId($_GET['host']);
         $ipv4_list = getObjectIPv4AllocationList($server_id);
         foreach($ipv4_list as $ip => $allocation){
            $allocation['addr'] = $ip;
            $ipv4_list[$ip] = $allocation;
         }

         $options = array( "addDecl" => true,
            "defaultTagName" => "ip",
            "indent" => "    ",
            "rootName" => $_GET['host']
         ); 
         $serializer = new XML_Serializer($options);
         $result = $serializer->serialize($ipv4_list); 

         if ( $result == true ) { 
             echo $serializer->getSerializedData();
         }

      }else{
         general_error();
      }

   break; 

   case 'POST':

      $options = array( 'keyAttribute' => 'addr', 'complexType' => 'array',
            "defaultTagName" => "ip");
      $xml_request =  @file_get_contents('php://input');
      $unserializer = &new XML_Unserializer($options);
      $result = $unserializer->unserialize($xml_request);
      if($result) {
         $server_id = getServerId($unserializer->getRootName());
         $ip_list = $unserializer->getUnserializedData();
         if ($server_id) {
            $rt_ip_list = getObjectIPv4AllocationList($server_id);
            foreach($ip_list as $ip) { 
               if ($ip[addr]) {  
                  compare_and_update($server_id, $ip, $rt_ip_list);
              } else { 
        
                 foreach ($ip as $ip_sub ) {
                   compare_and_update($server_id, $ip_sub, $rt_ip_list);
                 }
              }
            }
         }
      }

   break;
}
function compare_and_update ($server_id, $ip, $rt_ip_list){

   if($rt_ip_list[$ip['addr']]){

      # this makes the arrays comparable. 
      $rt_ip_list[$ip['addr']]["addr"] = $ip['addr']; 

      if ($rt_ip_list[$ip['addr']] != $ip){
         updateBond($ip['addr'], $server_id, $ip["osif"], $ip["type"]); 
      }  
   }else{
      bindIpToObject($ip['addr'], $server_id, $ip["osif"], $ip['type']);
   }
}

