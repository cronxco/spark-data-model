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
            $table->string('object_type')->index();
            $table->string('object_title')->index();
            $table->longText('object_content')->nullable();
            $table->json('object_metadata')->nullable();
            $table->string('object_url')->nullable();
            $table->string('object_image_path', 2048)->nullable();
            $table->timestamp('object_time')->default(DB::raw('CURRENT_TIMESTAMP'))->index();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->index();
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'))->index();
        });

        $schema->create(config('datamodel.events_table'), function (Blueprint $table) {
            $table->bigIncrements('event_id')->index();
            $table->string('source_uid')->index();
            $table->string('actor_uid')->nullable()->index();
            $table->json('actor_metadata')->nullable();
            $table->string('event_service')->index();
            $table->string('event_action')->index();
            $table->longText('event_payload')->nullable();
            $table->json('event_metadata')->nullable();
            $table->string('target_uid')->nullable()->index();
            $table->json('target_metadata')->nullable();
            $table->timestamp('event_time')->default(DB::raw('CURRENT_TIMESTAMP'))->index();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->index();
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'))->index();
            $table->foreign('target_uid')->references('object_uid')->on(config('datamodel.objects_table'));
            $table->foreign('actor_uid')->references('object_uid')->on(config('datamodel.objects_table'));
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
