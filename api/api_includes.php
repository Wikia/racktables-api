<?php 
include("XML/Serializer.php");
require_once "XML/Unserializer.php";
include ('../inc/init.php');
function general_error () {

   header('HTTP/1.1 400');
   echo "error\n";

}

function getServerId($host) {
   $cn_tag = getCNTag($host);
   $server_array =  scanRealmByText ('object', $cn_tag); 
   if (count($server_array) == 1 ) {
      foreach ( $server_array as $id => $server){
         return $id;
      }
   }else{
     return null;
   }
}

function getCNTag( $host ) {
   $cn_tag= '{$cn_' . $host . '}'; 
   return $cn_tag;
}

