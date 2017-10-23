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
        $this->page['couriers'] = $this->getCouriers();
    }

    public function onSelectCourier()
    {
        $courierManager = CourierManager::instance();

        $courier = $courierManager->findByAlias(post('courier'));

        $this->page['countries'] = $courier->getCountries();
    }

    public function getCouriers()
    {
        return CourierManager::instance()->getCouriers($activeOnly = false);
    }
}
