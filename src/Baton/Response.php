<?php
/**
 * Created by Pangodream.
 * Date: 03/11/2018
 * Time: 11:59
 */

namespace Baton;


class Response
{
    private $resultCode = null;
    private $resultDescription = null;
    private $executionTime = null;
    private $resultData = null;

    public function init(){
        $this->resultCode = null;
        $this->resultDescription = null;
        $this->executionTime = null;
        $this->resultData = null;
        return $this;
    }
    /**
     * @return integer
     */
    public function getResultCode()
    {
        return $this->resultCode;
    }

    /**
     * @param integer $resultCode
     */
    public function setResultCode($resultCode)
    {
        $this->resultCode = $resultCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getResultDescription()
    {
        return $this->resultDescription;
    }

    /**
     * @param string $resultDescription
     */
    public function setResultDescription($resultDescription)
    {
        $this->resultDescription = $resultDescription;
        return $this;
    }

    /**
     * @return integer
     */
    public function getExecutionTime()
    {
        return $this->executionTime;
    }

    /**
     * @param integer $executionTime
     */
    public function setExecutionTime($executionTime)
    {
        $this->executionTime = $executionTime;
        return $this;
    }

    /**
     * @return array
     */
    public function getResultData()
    {
        return $this->resultData;
    }

    /**
     * @param array $resultData
     */
    public function setResultData($resultData)
    {
        $this->resultData = $resultData;
        return $this;
    }

}