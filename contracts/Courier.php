<?php namespace Octommerce\Courier\Contracts;

use ApplicationException;

abstract class Courier
{
    public function details()
    {
        return [
            'name'        => 'Unknown',
            'alias'       => 'unknown',
            'description' => 'Unknown',
        ];
    }

    public function __get($name)
    {
        if ($value = array_get($this->details(), $name)) {
            return $value;
        }

        throw new ApplicationException('The property doesn\'t exists');
    }

    abstract public function getCountries();
    
    abstract public function getStates($country = null);

    abstract public function getCities($state);

    abstract public function getDistricts($city);

    abstract public function hasInsurance($data);

    abstract public function isFree($data);

    abstract public function getCosts($data);

    abstract public function track($awb);

    abstract public function generateAwb($data);
}
