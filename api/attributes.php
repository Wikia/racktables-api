<?php

#$script_mode = TRUE;
include("api_includes.php");

switch($_SERVER['REQUEST_METHOD']) {

case 'GET':

   if (  $_GET['host'] ) {
   
   $options = array( "addDecl" => true,
      "defaultTagName" => "Attribute",
      "indent" => "    ",
      "rootName" => $_GET['host']
   ); 
 
   $serializer = new XML_Serializer($options);
   $cn_tag= '{$cn_' . $_GET['host'] . '}'; 

   $ret_attributes = array();

   #this always comes back as an array of objects
   foreach ( scanRealmByText ('object', $cn_tag) as $server){
      $attributes = getAttrValues($server['id']); 

      # build array to return simplied attributes object
      foreach ( $attributes as $key => $value) { 
          $ret_attribute = array();
          $ret_attribute["id"] = $key;
          $ret_attribute["value"] = $value["value"];
          $ret_attribute["name"] = $value["name"];
          $ret_attributes[$key] = $ret_attribute; 
      } 

      $result = $serializer->serialize($ret_attributes); 

      if ( $result == true ) { 
         echo $serializer->getSerializedData();
      }

   }
} else{
   general_error();
}


   break;

case 'POST':

   $options = array(
      #'complexType' => 'array',
      'keyAttribute' => 'id'
   );

   #$xml_request =  http_get_request_body();
   $xml_request =  @file_get_contents('php://input');
   $unserializer = &new XML_Unserializer($options);
   $result = $unserializer->unserialize($xml_request);
   $server_id = getServerId($unserializer->getRootName());

   if ($server_id) {

   
      if ( $result == true ) {

         $data = $unserializer->getUnserializedData();
 
         if ($data["Attribute"]) {

            foreach ( $data as $attribute ) {
               if($data[id]){
                  if ($attribute["value"]) {
                     commitUpdateAttrValue($server_id, $attribute['id'] , $attribute['value']);
                  }
               } else {
                  $attributes = $attribute;
                  foreach($attributes as $attribute) {
                  if ($attribute["value"]) {
                     commitUpdateAttrValue($server_id, $attribute['id'] , $attribute['value']);
                  }
                }
              }
            }
         }
      }

   } else  {
       general_error(); 
   }
   break;
}



