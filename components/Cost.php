<?php namespace Octommerce\Courier\Components;

use Auth;
use Cart;
use Cache;
use Event;
use Exception;
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

        if ( ! $user || !isset($user->location_code) || empty($user->location_code)) {
            return null;
        }

        return $this->getCosts($user->location->subdistrict->code);
    }

    public function onGetCosts()
    {
        $locationCode = null;
        $validSubdistricts = $this->getSubdistricts($includeInvalid = false);
        $invalidSubdistricts = $this->getSubdistricts($includeInvalid = true);

        // Request comes when selecting district
        if ($invalidSubdistricts->count() > $validSubdistricts->count()) {
            $locationCode = $invalidSubdistricts->first()->code;
            $this->page['costs'] = $this->getCosts($locationCode);
            $this->page['thru'] = $locationCode;
        }

        // Request comes when selecting subdistrict
        if ($locationCode = array_get(post(), 'subdistrict')) {
            $this->page['costs'] = $this->getCosts();
            $this->page['thru'] = $locationCode;
        }
    }

    protected function getCosts($code = null)
    {
        //TODO: Get selected courier
        $courierAlias = 'jne';
        $courier = $this->getCourier($courierAlias);

        try {
            $costs = $courier->getCosts([
                'from'   => $this->getShippingFrom(),
                'thru'   => $this->getThru($code),
                'weight' => $this->getWeight()
            ]);
        } catch (Exception $e) {
            throw new ApplicationException($e->getMessage());
        }

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
        $cart = Cart::get();

        Event::fire('octommerce.courier.afterSelectService', [$cart, $costDetail]);

        $this->page['shipping_cost_detail'] = $costDetail;
        $this->page['cart'] = $cart;
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

        $user->location_code = post('thru');
        $user->save();
    }

    private function getCostDetailByServiceCode($costs, $serviceCode)
    {
        $costDetail = collect($costs)->filter(function($cost) use ($serviceCode) {
            return $cost['service_code'] == $serviceCode;
        })->first();

        return array_merge($costDetail, $this->getDiscountDetail($costDetail));
    }

    private function getDiscountDetail($costDetail)
    {
        if (Cart::get()->subtotal <= Settings::get('shipping_min_subtotal')) {
            return [
                'has_discount'         => false,
                'price_after_discount' => $costDetail['price']
            ];
        }

        $newPrice = $costDetail['price'] - Settings::get('shipping_max_subsidy');

        return [
            'has_discount'         => true,
            'price_after_discount' => $newPrice <= 0 ? 0 : $newPrice // Set to zero if discounted price negative
        ];
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

    public function hasChildren($locationCode, $includeInvalid = false)
    {
        return $this->getChildren($locationCode, $includeInvalid)->count() > 0;
    }

    /**
     * Check if the location has valid subdistrict (With location name)
     *
     * @param string $locationCode
     * @return boolean
     */
    public function getSubdistricts($includeInvalid = false)
    {
        // Ensure isn't subdistrict. So we don't need to check it
        if (array_get(post(), 'subdistrict')) return collect();

        $districtCode = array_get(post(), 'district');

        return $this->getChildren($districtCode, $includeInvalid);
    }

    protected function getChildren($locationCode, $includeInvalid = false)
    {
        $location = Location::whereCode($locationCode)->first();

        if ( ! $location) return collect();

        return $location->children->filter(function($location) use ($includeInvalid) {
            return ($location->name != '-' and $location->name != null) or $includeInvalid;
        });
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
