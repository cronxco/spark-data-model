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

        $schema->create(config('datamodel.objects_table'), function (Blueprint $table) {
            $table->string('object_uid')->primary();
            $table->string('object_concept')->index();
            $table->string('object_type')->index();
            $table->string('object_title')->index();
            $table->longText('object_content')->nullable();
            $table->json('object_metadata')->nullable();
            $table->string('object_url')->nullable();
            $table->string('object_image_url', 2048)->nullable();
            $table->string('object_image_cache', 2048)->nullable();
            $table->timestamp('object_time')->default(DB::raw('CURRENT_TIMESTAMP'))->index();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->index();
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'))->index();
            $table->softDeletes();
        });

        $schema->create(config('datamodel.events_table'), function (Blueprint $table) {
            $table->bigIncrements('event_id')->index();
            $table->string('source_uid')->index();
            $table->string('actor_uid')->nullable()->index();
            $table->json('actor_metadata')->nullable();
            $table->string('event_domain')->index();
            $table->string('event_service')->index();
            $table->string('event_action')->index();
            $table->bigInteger('event_value')->nullable();
            $table->integer('event_value_multiplier')->nullable();
            $table->string('event_value_unit')->nullable();
            $table->json('event_metadata')->nullable();
            $table->string('target_uid')->nullable()->index();
            $table->json('target_metadata')->nullable();
            $table->timestamp('event_time')->default(DB::raw('CURRENT_TIMESTAMP'))->index();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->index();
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'))->index();
            $table->softDeletes();
            $table->foreign('target_uid')->references('object_uid')->on(config('datamodel.objects_table'))->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('actor_uid')->references('object_uid')->on(config('datamodel.objects_table'))->onDelete('cascade')->onUpdate('cascade');
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

        $schema->dropIfExists(config('datamodel.events_table'));
    }
}
