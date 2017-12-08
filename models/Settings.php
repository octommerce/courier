<?php namespace Octommerce\Courier\Models;

use Model;
use Octommerce\Courier\Models\Location;

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
}
