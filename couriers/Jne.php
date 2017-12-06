<?php namespace Octommerce\Courier\Couriers;

use Octommerce\Courier\Contracts\Courier;
use Http;
use Exception;
use SystemException;

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
        try {

            $url = "http://apiv2.jne.co.id:10101/tracing/api/pricedev";

            $response = Http::post($url,function($http) use ($data){
                $http->data('username', $this->getUsername());
                $http->data('api_key', $this->getApiKey());
                $http->data('from', $data['from']);
                $http->data('thru', $data['thru']);
                $http->data('weight', $data['weight']);

            });

            if ($response->code != 200) {
                    throw new Exception($this->getErrorMessage($response, 'Failed to get Price'));
                }

        }catch(Exception $e){
            throw new SystemException($e->getMessage());
        }
        return $response->body;

    }

    public function track($awb) 
    {
        try {
            $url = "http://apiv2.jne.co.id:10101/tracing/api/list/cnote/".$awb;

            $response = Http::post($url,function($http){
                $http->data('username', $this->getUsername());
                $http->data('api_key', $this->getApiKey());
            });

            if ($response->code != 200) {
                    throw new Exception($this->getErrorMessage($response, 'Failed to get track'));
                }
        }catch(Exception $e){
            throw new SystemException($e->getMessage());
        }
        return $response->body;
    }

    public function getUsername()
    {
        return env('API_USERNAME');
    }

    public function getApiKey()
    {
        return env('API_KEY');
    }

    public function generateAwb($data) 
    {
    
    }

    private function getErrorMessage($response, $defaultMsg = 'Something went wrong')
    {
        $body = json_decode($response->body);

        if (isset($body->error)) return $body->error;

        return $defaultMsg;
    }
}
