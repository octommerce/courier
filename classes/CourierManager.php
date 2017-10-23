<?php namespace Octommerce\Courier\Classes;

use ApplicationException;

class CourierManager
{
    use \October\Rain\Support\Traits\Singleton;

    /**
     * @var array couriers
     */
    protected $couriers = [];

    /**
     * Initialize this singleton.
     */
    protected function init()
    {
    }

    /**
     * Add courier class
     *
     * @param string $class
     * @return array classes
     */
    public function addCourier($class)
    {
        $baseCourier = '\\Octommerce\\Courier\\Contracts\Courier';
        $courier = new $class;

        if ( ! $courier instanceof $baseCourier) {
            throw new ApplicationException(sprintf('class %s must instanceof %s', $courier, $baseCourier));
        }

        // Don't add courier twice
        if (in_array($courier->alias, $this->couriers)) return $this->couriers;

        $this->couriers[$courier->alias] = [
            'name'        => $courier->name,
            'description' => $courier->description,
            'class'       => $class
        ];

        return $this->couriers;
    }
}
