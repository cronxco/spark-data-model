<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDataModelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $schema = Schema::connection(config('datamodel.connection'));

        $schema->create(config('datamodel.table'), function (Blueprint $table) {
            $table->bigIncrements('event_id')->index();
            $table->string('source_uid')->index();
            $table->string('actor_id')->index();
            $table->longText('actor_metadata')->nullable();
            $table->string('event_service')->index();
            $table->string('event_action')->index();
            $table->longText('event_payload')->nullable();
            $table->longText('event_metadata')->nullable();
            $table->string('target_id')->index();
            $table->longText('target_metadata')->nullable();
            $table->timestamp('event_time')->default(DB::raw('CURRENT_TIMESTAMP'))->index();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->index();
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'))->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $schema = Schema::connection(config('datamodel.connection'));

        $schema->dropIfExists(config('datamodel.table'));
    }
}
