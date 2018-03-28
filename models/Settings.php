<?php namespace Octommerce\Courier\Models;

use Model;
use Octommerce\Courier\Models\Location;
use Octommerce\Courier\Classes\CourierManager;

/**
 * Settings Model
 */
class Settings extends Model
{
	public $implement = ['System.Behaviors.SettingsModel'];

    // A unique code
    public $settingsCode = 'octommerce_courier_settings';

    // Reference to field configuration
    public $settingsFields = 'fields.yaml';

    public function getOriginProvinceOptions()
    {
        return Location::getNameList();
    }

    public function getOriginCityOptions()
    {
        if (empty($this->origin_province))
            return [];

        return Location::getNameList($this->origin_province);
    }

    public function getAllowedServicesOptions()
    {
        $cm = CourierManager::instance();
        $services = [];

        return collect($cm->getCouriers())->map(function($services, $alias) use ($cm) {
            return $cm->findByAlias($alias)->availableServices();
        })
        ->collapse()
        ->all();
    }
}
