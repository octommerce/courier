<?php namespace Octommerce\Courier\Components;

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

    public function onGetCosts()
    {
        $this->page['costs'] = $this->getCosts();
    }

    protected function getCosts()
    {
        //TODO: Get selected courier
        $courierAlias = 'jne';
        $courier = $this->getCourier($courierAlias);

        return $courier->getCosts([
            'from'   => $this->getShippingFrom(),
            'thru'   => $this->getThru(),
            'weight' => $this->getWeight()
        ]);
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

    protected function getThru()
    {
        $code = post('subdistrict');

        //TODO: select dynamic courier code
        return Location::whereCode($code)->first()->jne_code;
    }

    protected function getWeight()
    {
        //TODO: Get products weight from cart
        return 1;
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
