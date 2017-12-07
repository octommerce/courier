<?php namespace Octommerce\Courier;

use Backend;
use System\Classes\PluginBase;
use Octommerce\Courier\Classes\CourierManager;

/**
 * courier Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'octommerce.courier::lang.plugin.name',
            'description' => 'octommerce.courier::lang.plugin.description',
            'author'      => 'Surahman',
            'icon'        => 'icon-ship'
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Boot method, called right before the request route.
     *
     * @return array
     */
    public function boot()
    {
        CourierManager::instance()->addCourier('Octommerce\Courier\Couriers\Jne');
    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return [
            'Octommerce\Courier\Components\Cost' => 'cost',
        ];
    }

    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return []; // Remove this line to activate

        return [
            'octommerce.courier.some_permission' => [
                'tab' => 'courier',
                'label' => 'Some permission'
            ],
        ];
    }

    /**
     * Registers back-end navigation items for this plugin.
     *
     * @return array
     */
    public function registerNavigation()
    {
        return []; // Remove this line to activate

        return [
            'courier' => [
                'label'       => 'courier',
                'url'         => Backend::url('octommerce/courier/mycontroller'),
                'icon'        => 'icon-leaf',
                'permissions' => ['octommerce.courier.*'],
                'order'       => 500,
            ],
        ];
    }

    /**
     * Register new Twig variables
     * @return array
     */
    public function registerMarkupTags()
    {
        return [
            'functions' => [
                'form_select_province'    => ['Octommerce\Courier\Models\Location', 'formSelectProvince'],
                'form_select_city'        => ['Octommerce\Courier\Models\Location', 'formSelectCity'],
                'form_select_district'    => ['Octommerce\Courier\Models\Location', 'formSelectDistrict'],
                'form_select_subdistrict' => ['Octommerce\Courier\Models\Location', 'formSelectSubdistrict'],
            ]
        ];
    }
}
