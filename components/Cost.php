<?php namespace Octommerce\Courier\Components;

use Cms\Classes\ComponentBase;
use Octommerce\Courier\Classes\CourierManager;

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
        return $this->courierManager()->findByAlias($alias);
    }

    private function courierManager()
    {
        return CourierManager::instance();
    }
}
