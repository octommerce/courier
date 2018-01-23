<?php namespace Octommerce\Courier;

use Event;
use Backend;
use System\Classes\PluginBase;
use Octommerce\Courier\Classes\CourierManager;
use RainLab\User\Models\User;
use Octommerce\Courier\Models\Settings;

/**
 * courier Plugin Information File
 */
class Plugin extends PluginBase
{
    public $require = ['RainLab.User'];

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

	public function registerSettings()
	{
		return [
			'courier' => [
				'label'       => 'Courier',
				'description' => 'Manage courier plugin by octommerce',
				'category'    => 'Courier',
				'icon'        => 'icon-truck',
                'class'       => 'Octommerce\Courier\Models\Settings',
				'order'       => 500,
				'keywords'    => 'courier shipping'
			]
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

        User::extend(function($model) {
            $model->hasOne['location'] = [
                'Octommerce\Courier\Models\Location', 
                'key'      => 'code',
                'otherKey' => 'location_code'
            ];
        });

        Event::listen('cms.page.beforeDisplay', function($controller, $url, $page) {
            $controller->vars['oc_courier_settings'] = Settings::instance();
        });
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
                'json_decode'             => function($json, $assoc = false) {
                    return json_decode($json, $assoc);
                }
            ]
        ];
    }
}
