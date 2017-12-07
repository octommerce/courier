<?php namespace Octommerce\Courier\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateLocationsTable extends Migration
{
    public function up()
    {
        Schema::create('octommerce_courier_locations', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('code')->index();
            $table->string('jne_code')->nullable();
            $table->string('name');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('octommerce_courier_locations');
    }
}
