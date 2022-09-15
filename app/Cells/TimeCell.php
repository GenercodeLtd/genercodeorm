<?php

namespace GenerCodeOrm\Cells;

class TimeCell extends MetaCell {

    protected $format;

    function setValidation($min, $max) {
        $this->min = $min;
        $this->max = $max;
    }


    function map($value) {
        if (is_array($value)) {
            foreach($value as $key=>$val) {
                $this->validateSize($this->getTimestamp($val));
                if ($this->last_error != ValidationRules::OK) {
                    return null;
                } 
            }
        } else {
            $this->validateSize($this->getTimestamp($value));
            if ($this->last_error != ValidationRules::OK) {
                return null;
            }
        }
        return $value;
    }
    

    function getTimestamp($date) {
        $d = \DateTime::createFromFormat('Y-m-d\TH:i', $date);
        if (!$d) {
            throw new \Exception("Datetime could not be created from date: " . $date);
        }

        /*
        //check if value is already timestamp
        if (is_numeric($value)) return $value;
		$datetime = \DateTime::createFromFormat( $format, $value);
		$timestamp = $datetime->getTimestamp();
		return $timestamp;
        */
        return $d->getTimestamp();
    }

    
    function toSchema() {
        $arr=parent::toSchema();
        $arr["type"] = "time";
        $arr["format"] = $this->format;
        return $arr;
    }


    static function convertTimestamp($timestamp, $format)
	{
	  return date($format, $timestamp);
	}
	
	static function convertTimestampDZ($timestamp, $format)
	{
	
	  return date($format, $timestamp);
	}
	

	static function getDurationStamp($duration)
	{
		$hours = -1;
		$mins = -1;
		$secs = -1;
	
		$stamp = 0;
		
		if (isset($duration['hours']))
		{
			$stamp += $duration['hours'] * 60 * 60;
		}
		
		if (isset($duration['mins']))
		{
			$stamp += $duration['mins'] * 60;
		}
		
		if (isset($duration['secs']))
		{
			$stamp += round($duration['secs']);
		}
		return $stamp;
	}
	
	
	static function convertDurationStamp($stamp, $format)
	{
		$durations = array();
		if (strpos($format, "H") !== false) 
		{
			$hours = floor($stamp / (60 * 60));
			$stamp -= $hours * 60 * 60;
			$durations['hours'] = $hours;
		}
		if (strpos($format, "i") !== false) 
		{
			$mins = floor($stamp / 60);
			$stamp -= $mins * 60;
			$durations['mins'] = ($mins < 10) ? "0" . $mins : $mins;
		}
		
		if (strpos($format, "s") !== false)
		{
			$stamp = round($stamp);
			$durations['secs'] = ($stamp < 10) ? "0" . $stamp : $stamp;
		}
		
		
		return implode(":", $durations);
	}


	static function getTimestampDZ($value, $format)
	{
		//, new \DateTimeZone(\DateTimeZone::EUROPE)
		return $value;
		$datetime = \DateTime::createFromFormat( $format, $value);
		$timestamp = $datetime->getTimestamp();
		return $timestamp;
	}

}