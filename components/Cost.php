<?php namespace Octommerce\Courier\Components;

use Auth;
use Cart;
use Cache;
use Event;
use ApplicationException;
use Cms\Classes\ComponentBase;
use Octommerce\Courier\Classes\CourierManager;
use Octommerce\Courier\Models\Location;
use Octommerce\Courier\Models\Settings;

class Cost extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'octommerce.courier::lang.cost.name',
            'description' => 'octommerce.courier::lang.cost.description'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $this->page['couriers'] = $couriers = $this->getCouriers();
        $this->page['shippingCosts'] = $this->loadDefaultShippingCosts();

        /**
         * If only one courier, get the available shipping destinations
         **/
        if (count($couriers) == 1) {
            $couriers = array_flip($couriers);
            $courierAlias = end($couriers);

            $this->fetchShippingDestinations($courierAlias);
        }
    }

    public function onSelectCourier()
    {
        $this->fetchShippingDestinations(post('courier'));
    }

    protected function loadDefaultShippingCosts()
    {
        $user = Auth::getUser();

        if ( ! $user || !isset($user->location) ) {
            return null;
        }

        return $this->getCosts($user->location->subdistrict->code);
    }

    public function onGetCosts()
    {
        $this->page['costs'] = $this->getCosts();
    }

    protected function getCosts($code = null)
    {
        //TODO: Get selected courier
        $courierAlias = 'jne';
        $courier = $this->getCourier($courierAlias);

        $costs = $courier->getCosts([
            'from'   => $this->getShippingFrom(),
            'thru'   => $this->getThru($code),
            'weight' => $this->getWeight()
        ]);

        /**
         * Cache shipping costs and give the name from cart id
         **/
        Cache::put(Cart::get()->id, $costs, 60);

        return $costs;
    }

    public function onSelectService()
    {
        $costs = Cache::get(Cart::get()->id);
        $costDetail = $this->getCostDetailByServiceCode($costs, post('service_code'));

        $this->saveLocationCodeToUser();

        Event::fire('octommerce.courier.afterSelectService', [Cart::get(), $costDetail]);
    }

    public function getDisableServices()
    {
        return Settings::get('disable_services');
    }

    private function saveLocationCodeToUser()
    {
        if ( ! $user = Auth::getUser()) {
            return;
        }

        $user->location_code = post('subdistrict');
        $user->save();
    }

    private function getCostDetailByServiceCode($costs, $serviceCode)
    {
        return collect($costs)->filter(function($cost) use ($serviceCode) {
            return $cost['service_code'] == $serviceCode;
        })->first();
    }

    protected function getShippingFrom()
    {
        $originCity = Settings::get('origin_city');
        $location = Location::whereCode($originCity)->first();

        if ( ! $location) {
            throw new ApplicationException('Please set shipping from on backend settings');
        }

        //TODO: dynamic courier 
        return $location->jne_code;
    }

    protected function getThru($code = null)
    {
        $code = $code ?: post('subdistrict');

        //TODO: select dynamic courier code
        return Location::whereCode($code)->first()->jne_code;
    }

    protected function getWeight()
    {
        return Cart::get()->total_weight ?: 1;
    }

    /**
     * Fetch shipping destinations
     * Set countries or states into page variable
     *
     * @param string $courierAlias Courier alias
     */
    public function fetchShippingDestinations($courierAlias)
    {
        $courier = $this->getCourier($courierAlias);
        $this->page['countries'] = $countries = $courier->getCountries();

        if (empty($countries)) {
            $this->page['states'] = $courier->getStates();
        }
    }

    public function getCouriers()
    {
        return $this->courierManager()->getCouriers($activeOnly = false);
    }

    protected function getCourier($alias = null)
    {
        if (is_null($alias)) {
            $alias = post('courier');
        }
        
        return $this->courierManager()->findByAlias($alias);
    }

    private function courierManager()
    {
        return CourierManager::instance();
    }
}
