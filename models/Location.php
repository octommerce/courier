<?php namespace Octommerce\Courier\Models;

use Form;
use Model;
use Octommerce\Courier\Helpers\Location as LocationHelper;

/**
 * Location Model
 */
class Location extends Model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'octommerce_courier_locations';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

    /**
     * @var array Cache for nameList() method
     */
    protected static $nameList = [];

    public static function getNameList($locationCode = 'base')
    {
        if (isset(self::$nameList[$locationCode])) {
            return self::$nameList[$locationCode];
        }

        $arg = self::getArgument($locationCode);

        return self::$nameList[$locationCode] = self::whereRaw("code REGEXP '$arg'")
            ->orderBy('name')
            ->lists('name', 'code');
    }

    public function getParentAttribute()
    {
        $codes = explode('.', $this->code);

        if (count($codes) <= 1) return null;

        array_pop($codes);

        return self::whereCode(implode('.', $codes))->first();
    }

    public function children()
    {
        return self::where('code', 'like', $this->code . '.%');
    }

    public function getChildrenAttribute()
    {
        return $this->children()->get();
    }

    public static function getArgument($locationCode)
    {
        if ($locationCode == 'base') return '^[0-9]+$';

        return "^{$locationCode}.[0-9]+$";
    }

    public static function formSelectProvince($name, $selectedValue = null, $options = [])
    {
        return Form::select($name, self::getNameList(), $selectedValue, $options);
    }

    public static function formSelectCity($name, $province, $selectedValue = null, $options = [])
    {
        return Form::select($name, self::getNameList($province), $selectedValue, $options);
    }

    public static function formSelectDistrict($name, $city, $selectedValue = null, $options = [])
    {
        return Form::select($name, self::getNameList($city), $selectedValue, $options);
    }

    public static function formSelectSubdistrict($name, $district, $selectedValue = null, $options = [])
    {
        return Form::select($name, self::getNameList($district), $selectedValue, $options);
    }

    public function getProvinceAttribute()
    {
        $arg = LocationHelper::codeByLevel($this->code, 1);

        return self::where('code', $arg)->first();
    }

    public function getCityAttribute()
    {
        $arg = LocationHelper::codeByLevel($this->code, 2);

        return self::where('code', $arg)->first();
    }

    public function getDistrictAttribute()
    {
        $arg = LocationHelper::codeByLevel($this->code, 3);

        return self::where('code', $arg)->first();
    }

    public function getSubdistrictAttribute()
    {
        $arg = LocationHelper::codeByLevel($this->code, 4);

        return self::where('code', $arg)->first();
    }
}
