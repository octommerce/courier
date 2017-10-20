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
        $courier = '\\Octommerce\\Courier\\Contracts\Courier';

        if ( ! (new $class) instanceof $courier) {
            throw new ApplicationException(sprintf('class %s must instanceof %s', $class, $courier));
        }

        // Don't add courier twice
        if (in_array($class, $this->couriers)) return $this->couriers;

        $this->couriers[] = $class;

        return $this->couriers;
    }
}
