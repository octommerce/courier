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
            $courier = end($couriers);

            $this->fetchShippingDestinations($courier);
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
     * @param string $courier Courier alias
     */
    public function fetchShippingDestinations($courier)
    {
        $courierManager = CourierManager::instance();

        $courier = $courierManager->findByAlias($courier);
        $this->page['countries'] = $countries = $courier->getCountries();

        if (empty($countries)) {
            $this->page['states'] = $courier->getStates();
        }
    }

    public function getCouriers()
    {
        return CourierManager::instance()->getCouriers($activeOnly = false);
    }
}
