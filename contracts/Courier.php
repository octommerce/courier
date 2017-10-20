<?php namespace Octommerce\Courier\Contracts;

abstract class Courier
{
    abstract public function getCountries();
    
    abstract public function getStates($country = null);

    abstract public function getCities($state);

    abstract public function getDistricts($city);

    abstract public function hasInsurance($data);

    abstract public function isFree($data);

    abstract public function getCosts($data);
}
