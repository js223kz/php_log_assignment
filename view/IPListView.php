<?php

namespace view;

use model\LogService;

require_once("view/ListView.php");

class IPListView extends ListView
{

    private static $numberOfTimes = "numberOfTimes";
    private static $ipURL = "ip";
    private static $pageTitle = "Logged IP-addresses";
    private $listItems = array();

    public function getIPList(){
        $this->setIPList();
        $this->renderHTML(Self::$pageTitle, $this->renderIPList());
    }

    private function setIPList(){
        $tempList = $this->getLogItemsList();

        foreach($tempList as $logItem){
            $num = $this->getNumberOfSessions($logItem[$this->ip], $tempList);
            $latest = $this->getLatestSession($logItem[$this->ip], $tempList);

            if ($this->checkIfUnique($logItem[$this->ip], $this->ip, $this->listItems)) {
                array_push($this->listItems, [$this->ip => $logItem[$this->ip],
                    $this->microTime => $latest,
                        Self::$numberOfTimes => $num]);
            }
        }
        $this->sortBy($this->microTime, $this->listItems);
    }

    private function getNumberOfSessions($valueToCheck, $tempList)
    {
        $sessionArray = array();

        foreach ($tempList as $logItem) {
            if ($valueToCheck == $logItem[$this->ip]) {
                if (!in_array($logItem[$this->sessionId], $sessionArray)) {
                    array_push($sessionArray, $logItem[$this->sessionId]);
                }
            }
        }
        return count($sessionArray);
    }

    private function getLatestSession($valueToCheck, $tempList)
    {
        $sessionDateArray = array();
        foreach ($tempList as $logItem) {
            $dateTime = $this->convertMicroTime($logItem[$this->microTime]);
            if ($valueToCheck == $logItem[$this->ip]) {
                if (!in_array($dateTime, $sessionDateArray)) {
                    array_push($sessionDateArray, $dateTime);
                }
            }
        }
        return end($sessionDateArray);
    }

    private function getIPUrl($ip) {
        return "?".self::$ipURL."=$ip";
    }

    public function ipLinkIsClicked() {
        if (isset($_GET[self::$ipURL]) ) {
            return true;
        }
        return false;
    }

    public function getIP() {
        assert($this->ipLinkIsClicked());
        return $_GET[self::$ipURL];
    }

    private function renderIPList()
    {
        $ret = "<ul>";
        foreach ($this->listItems as $ipAdresses) {
            $occurrences = $ipAdresses[Self::$numberOfTimes];
            $ip = $ipAdresses[$this->ip];
            $time = $ipAdresses[$this->microTime];
            $ipUrl = $this->getIPUrl($ip);

            $ret .= "<li>IP-address: <a href='$ipUrl'>$ip</a></li>";
            $ret .= "<li>Number of sessions:  $occurrences</li>";
            $ret .= "<li>Logged latest at:  $time</li>";
            $ret .= "<br>";
        }
        $ret .= "</ul>";
        return $ret;
    }
}
