<?php

/**
 * Test macgickpacket class
 *
 * @author Alberto Iriberri <airiberri@gmail.com>
 * 
 */

require_once 'magickpacket.php';

$mpacket=new magickPacket();
try{
    $mpacket->wakeUp('00:11:22:AA:BB:CC');
    echo "Packets sent.";
}catch(Exception $e){
    echo $e->getMessage();
}