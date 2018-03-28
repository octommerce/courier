<?php namespace Octommerce\Courier\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class AddOcShippingDataToCartsAndOrdersTables extends Migration
{
    public function up()
    {
        if ( ! Schema::hasColumn('octommerce_octommerce_carts', 'oc_shipping_data')) {
            Schema::table('octommerce_octommerce_carts', function(Blueprint $table) {
                $table->json('oc_shipping_data')->nullable()->after('user_id');
            });
        }

        if ( ! Schema::hasColumn('octommerce_octommerce_orders', 'oc_shipping_data')) {
            Schema::table('octommerce_octommerce_orders', function(Blueprint $table) {
                $table->json('oc_shipping_data')->nullable()->after('user_id');
            });
        }
    }

    public function down()
    {
        Schema::table('octommerce_octommerce_carts', function(Blueprint $table) {
            $table->dropColumn('oc_shipping_data');
        });

        Schema::table('octommerce_octommerce_orders', function(Blueprint $table) {
            $table->dropColumn('oc_shipping_data');
        });
    }
}

