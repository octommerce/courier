<?php namespace Octommerce\Courier\Couriers;

use Octommerce\Courier\Contracts\Courier;

class Jne extends Courier
{
    public function details()
    {
        return [
            'name'        => 'JNE',
            'alias'       => 'jne',
            'description' => 'Kurir JNE',
        ];
    }

    public function getCountries() 
    {
        return [
            'id' => 'Indonesia',
            'en' => 'England',
        ];
    }
    
    public function getStates($country = null) 
    {
        return [
            'ac' => 'Aceh',
            'ab' => 'Ambon',
        ];
    }

    public function getCities($state) 
    {
    
    }

    public function getDistricts($city) 
    {
    
    }

    public function hasInsurance($data) 
    {
    
    }

    public function isFree($data) 
    {
    
    }

    public function getCosts($data) 
    {
    
    }

    public function track($awb) 
    {
    
    }

    public function generateAwb($data) 
    {
    
    }
}
