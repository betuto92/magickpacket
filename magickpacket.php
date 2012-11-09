<?php

/**
 * Build and send a Magick Packet to WakeUp On Lan (WOL) a remote host
 * @package magickpacket
 * @author Alberto Iriberri <airiberri@gmail.com>
 * 
 */

class magickPacket{
    private $socket;
    /**
     * wakeUp
     * Tries to wake up host with specified MAC Address
     * @param type $macAddr
     * @throws Exception 'Invalid mac address.'
     */
    public function wakeUp($macAddr){
        //Check $macAddr
        if(!$this->validateMacAddr($macAddr)){
            throw new Exception('Invalid mac address.');
        }else{
            //Create MagickPacket
            $magickPacket=$this->createPacketData($macAddr);
            //Create the instance socket to send UDP packets
            $this->createSocket();
            //Send 5 packets waiting 1 second after each one
            for($count=0; $count<5;$count++){
                $this->sendPacket($magickPacket);
                sleep(1);
            }   
            //Close the instance socket
            $this->closeSocket();
            
            //Note: There is no way to know if the packets reached the host
            //so we return nothing
        }        
    }
    /**
     * sendPacket
     * Sends data through instance socket
     * @param string $data Packet data to be sent
     */
    private function sendPacket($data){
        if(!isset($this->socket)){
            $this->createSocket();
        }
        socket_sendto($this->socket,$data,strlen($data),0,'255.255.255.255',9);
    }
    /**
     * createSocket
     * Creates the instance socket to send UDP / Broadcast packets
     * @throws Exception 'Socket not created.'
     */
    private function createSocket(){
        //Create a socket to send UDP packets
        $this->socket=socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);  
        if(!$this->socket){
            throw new Exception('Socket not created');
        }else{
            //Set broadcast option for socket
            socket_set_option($this->socket, SOL_SOCKET, SO_BROADCAST,1);
        }
    } 
    /**
     * closeSocket
     * Closes the instance socket
     */
    private function closeSocket(){
        socket_close($this->socket);
    }
    /**
     * createPacketdata
     * Composes the data content of a MagickPacket for the specified mac
     * @param string $macAddr Mac Address
     */
    private function createPacketData($macAddr){
        //Convert mac address to uppercase
        $lcMacAddr=  strtoupper($macAddr);
        //Remove separators (':', '-') in case they are present
        $cleanMacAddr=  str_replace(array(':','-'),array('',''),$lcMacAddr);
        //Convert hex string to 6 byte string
        $mac='';
        for($start=0;$start<12;$start+=2){
            $mac.=chr(hexdec(substr($cleanMacAddr,$start,2)));
        }
        //Build the MagickPaket initial string (6 FF value bytes)
        $mpIni=  str_repeat(chr(255), 6);
        //Build the MagickPacket address data (16 times 6 byte string)
        $mpAddr=str_repeat($mac,16);
        //Concatenate initial string and address data
        $magickPacket=$mpIni.$mpAddr;
        //Return magick packet
        return $magickPacket;
    }
    /**
     * validateMacAddr
     * Validates readable mac address format
     * @param string $macAddr Mac address 
     *                        Valid formats are:
     *                           - h1h2h3h4h5h6
     *                           - h1:h2:h3:h4:h5:h6
     *                           - h1-h2-h3-h4-h5-h6
     * @return boolean
     */
    private function validateMacAddr($macAddr){
        //Check if format is correct: 
        return preg_match('/([a-fA-F0-9]{2}[:|\-]?){6}/',$macAddr);
    }
}