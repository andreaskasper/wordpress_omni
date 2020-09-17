<?php

namespace plugins\goo1\omni;

class FreshdeskTicket {

    private $_ticketdata = array("tags" => array());

    function __construct() {

    }

    function __get($key) {
        switch ($key) {
            case "subject": return $this->_ticketdata["subject"] ?? null;
            case "description": return $this->_ticketdata["description"] ?? null;
        }
    }

    function __set($key, $value) {
        switch ($key) {
            case "subject": 
                $this->_ticketdata["subject"] = $value;
                return true;
            case "message":
            case "description": 
                $this->_ticketdata["description"] = $value;
                return true;
            case "name": 
                $this->_ticketdata["name"] = $value;
                return true;
            case "email": 
                $this->_ticketdata["email"] = $value;
                return true;
            case "facebook_id":
                $this->_ticketdata["facebook_id"] = $value;
                return true;
            case "priority": 
                $this->_ticketdata["priority"] = $value;
                return true;
            case "type":
                $this->_ticketdata["type"] = $value;
                return true;
        }
    }

    public function addTag($txt) {
        $this->_ticketdata["tags"][] = $txt;
        return true;
    }



    public function upload() {
        $d = array();
        if (!empty($this->_ticketdata["subject"])) $d["subject"] = $this->_ticketdata["subject"];
        if (!empty($this->_ticketdata["description"])) $d["description"] = $this->_ticketdata["description"];
        if (!empty($this->_ticketdata["name"])) $d["name"] = $this->_ticketdata["name"];
        if (!empty($this->_ticketdata["email"])) $d["email"] = $this->_ticketdata["email"];
        if (!empty($this->_ticketdata["facebook_id"])) $d["facebook_id"] = $this->_ticketdata["facebook_id"];
        if (!empty($this->_ticketdata["type"])) $d["type"] = $this->_ticketdata["type"];
        if (count($this->_ticketdata["tags"]) > 0) $d["tags"] = $this->_ticketdata["tags"];
        $d["status"] = $this->_ticketdata["status"] ?? 2;
        $d["priority"] = $this->_ticketdata["priority"]+0 ?? 2;
        $ch = curl_init("https://goo1.de/freshdesk/apiproxy.php?p=/api/v2/tickets");
        $payload = json_encode($d);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        $result = curl_exec($ch);
        curl_close($ch);
        $resp = json_decode($result, true);
        return $resp;
    }

}