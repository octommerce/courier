<?php namespace Octommerce\Courier\Couriers;

use Octommerce\Courier\Contracts\Courier;
use Http;
use Exception;
use SystemException;
use Octommerce\Courier\Exceptions\ServerTimeoutException;

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

                $http->timeout(10);
            });

            switch($response->code) {
                case 0:
                    throw new ServerTimeoutException('Server down');
                case 200:
                    return $this->sortByPriceAsc(json_decode($response->body, true)['price']);
                default:
                    throw new Exception($this->getErrorMessage($response, 'Failed to get Price'));
            }
        }catch(Exception $e){
            switch($response->code) {
                case 200:
                    $message = data_get(json_decode($response->body, true), 'error', 'Sorry, there has some problem on Shipping Price');
                    throw new Exception($message);
                default:
                    throw new Exception($this->getErrorMessage($response, 'Failed to get Price'));
            }
        }
    }

    public function track($awb) 
    {
        try {
            $url = "http://apiv2.jne.co.id:10101/tracing/api/list/cnote/".$awb;

            $response = Http::post($url,function($http){
                $http->data('username', $this->getUsername());
                $http->data('api_key', $this->getApiKey());

                $http->timeout(1);
            });

            switch($response->code) {
                case 0:
                    throw new ServerTimeoutException('Server down');
                case 200:
                    return $response->body;
                default:
                    throw new Exception($this->getErrorMessage($response, 'Failed to get track'));
            }
        }catch(Exception $e){
            throw $e;
        }
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
        try {
            $url = "http://apiv2.jne.co.id:10102/tracing/api/generatecnote";

            $response = Http::post($url, function($http) use ($data) {
                $http->data('username', $this->getUsername());
                $http->data('api_key', $this->getApiKey());
                $http->data('OLSHOP_BRANCH', 'CGK000');
                $http->data('OLSHOP_CUST', $data['OLSHOP_CUST']);
                $http->data('OLSHOP_ORDERID', $data['OLSHOP_ORDERID']);
                $http->data('OLSHOP_SHIPPER_NAME', $data['OLSHOP_SHIPPER_NAME']);
                $http->data('OLSHOP_SHIPPER_ADDR1', $data['OLSHOP_SHIPPER_ADDR1']);
                $http->data('OLSHOP_SHIPPER_ADDR2', $data['OLSHOP_SHIPPER_ADDR2']);
                $http->data('OLSHOP_SHIPPER_CITY', $data['OLSHOP_SHIPPER_CITY']);
                $http->data('OLSHOP_SHIPPER_REGION', $data['OLSHOP_SHIPPER_REGION']);
                $http->data('OLSHOP_SHIPPER_ZIP', $data['OLSHOP_SHIPPER_ZIP']);
                $http->data('OLSHOP_SHIPPER_PHONE', $data['OLSHOP_SHIPPER_PHONE']);

                $http->data('OLSHOP_RECEIVER_NAME', $data['OLSHOP_RECEIVER_NAME']);
                $http->data('OLSHOP_RECEIVER_ADDR1', $data['OLSHOP_RECEIVER_ADDR1']);
                $http->data('OLSHOP_RECEIVER_ADDR2', $data['OLSHOP_RECEIVER_ADDR2']);
                $http->data('OLSHOP_RECEIVER_CITY', $data['OLSHOP_RECEIVER_CITY']);
                $http->data('OLSHOP_RECEIVER_REGION', $data['OLSHOP_RECEIVER_REGION']);
                $http->data('OLSHOP_RECEIVER_ZIP', $data['OLSHOP_RECEIVER_ZIP']);
                $http->data('OLSHOP_RECEIVER_PHONE', $data['OLSHOP_RECEIVER_PHONE']);

                $http->data('OLSHOP_QTY', $data['OLSHOP_QTY']);
                $http->data('OLSHOP_WEIGHT', $data['OLSHOP_WEIGHT']);
                $http->data('OLSHOP_GOODSDESC', $data['OLSHOP_GOODSDESC']);
                $http->data('OLSHOP_GOODSVALUE', $data['OLSHOP_GOODSVALUE']);
                $http->data('OLSHOP_GOODSTYPE', $data['OLSHOP_GOODSTYPE']);
                $http->data('OLSHOP_INST', $data['OLSHOP_INST']);
                $http->data('OLSHOP_INS_FLAG', $data['OLSHOP_INS_FLAG']);
                $http->data('OLSHOP_ORIG', $data['OLSHOP_ORIG']);
                $http->data('OLSHOP_DEST', $data['OLSHOP_DEST']);
                $http->data('OLSHOP_SERVICE', $data['OLSHOP_SERVICE']);
                $http->data('OLSHOP_COD_FLAG', $data['OLSHOP_COD_FLAG']);
                $http->data('OLSHOP_COD_AMOUNT', $data['OLSHOP_COD_AMOUNT']);
            });

            if ($response->code != 200) {
                throw new Exception($this->getErrorMessage($response));
            }
        }catch(Exception $e){
            throw new SystemException($e->getMessage());
        }

        return $response->body;
    }

    public function availableServices()
    {
        return [
            'JTR'      => 'JTR',
            'JTR18'    => 'JTR18',
            'JTR250'   => 'JTR250',
            'JTR<150'  => 'JTR<150',
            'JTR>250'  => 'JTR>250',
            'OKE15'    => 'OKE15',
            'OKE19'    => 'OKE19',
            'POPBOX'   => 'POPBOX',
            'REG15'    => 'REG15',
            'REG19'    => 'REG19',
            'SPS15'    => 'SPS15',
            'SPS19'    => 'SPS19',
            'YES15'    => 'YES15',
            'YES19'    => 'YES19',
            'CTC15'    => 'CTC15',
            'CTC19'    => 'CTC19',
            'CTCSPS15' => 'CTCSPS15',
            'CTCSPS19' => 'CTCSPS19',
            'CTCYES15' => 'CTCYES15',
            'CTCYES19' => 'CTCYES19',
            'PELIKAN' => 'PELIKAN',
        ];
    }

    protected function sortByPriceAsc($costs)
    {
        return collect($costs)->sortBy('price');
    }

    private function getErrorMessage($response, $defaultMsg = null)
    {
        $body = json_decode($response->body);

        if (isset($body->error)) return $body->error;

        if (isset($body->detail)) return $body->detail[0]->reason;

        if (is_null($defaultMsg)) return $response->body;

        return $defaultMsg;
    }
}
