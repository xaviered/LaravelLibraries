<?php

namespace ixavier\LaravelLibraries\Data\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Query;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ixavier\LaravelLibraries\Data\Models\MetaDefinition;
use ixavier\LaravelLibraries\Data\Models\Placement;
use ixavier\LaravelLibraries\Data\Models\Relationships\MetaValue;
use ixavier\LaravelLibraries\Data\Models\Model;

class InitMigration extends Migration
{
    /** @var Collection Names of the tables that will be created */
    private $tables;

    /**
     * BaseMigration constructor.
     */
    public function __construct()
    {
        $this->loadTableNames();
    }

    /**
     * Loads table info for db
     */
    private function loadTableNames(): void
    {
        $this->tables = new Collection([
            'model' => (new Model())->getTable(),
            'metaDefinition' => (new MetaDefinition())->getTable(),
            'metaValue' => (new MetaValue())->getTable(),
            'placement' => (new Placement())->getTable(),
        ]);
    }

    /**
     * Helper function to get table db builder
     *
     * @param string $tableName Table name from $this->tables
     * @return Query\Builder
     */
    private function db(string $tableName): Query\Builder
    {
        return DB::table($this->tables->get($tableName));
    }

    /**
     * @return Query\Builder Helper method to get model table
     */
    public function modelTable(): Query\Builder
    {
        return $this->db('model');
    }

    /**
     * @return Query\Builder Helper method to get metaDefinition table
     */
    public function metaDefinitionTable(): Query\Builder
    {
        return $this->db('metaDefinition');
    }

    /**
     * @return Query\Builder Helper method to get metaValue table
     */
    public function metaValueTable(): Query\Builder
    {
        return $this->db('metaValue');
    }

    /**
     * @return Query\Builder Helper method to get metaValue table
     */
    public function placementTable(): Query\Builder
    {
        return $this->db('placement');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tables->get('model'), function (Blueprint $table) {
            $table->bigIncrements('id')->primary()->unique();
            $table->timestamps();
            $table->softDeletes();
            $table->string('title');
            $table->string('type')->index();
            $table->string('href')->nullable();
            $table->bigInteger('alias_id', false, true)
                ->nullable()
                ->index();
            $table->bigInteger('updated_by', false, true)
                ->nullable()
                ->index();
            $table->bigInteger('created_by', false, true)
                ->nullable()
                ->index();
            $table->text('content')->nullable();
        });

        // @todo: This will be on a yaml file. global and on a per project basis
        Schema::create($this->tables->get('metaDefinition'), function (Blueprint $table) {
            $table->bigIncrements('id')->primary()->unique();
            $table->timestamps();
            $table->softDeletes();
            $table->string('title');
            $table->string('type'); // for multi values, this will be `json`
            $table->string('description')->nullable();
        });

        // @todo: All meta will be store on this table, see if we can store in different tables
        // change all code to get from this table
        Schema::create($this->tables->get('metaValue'), function (Blueprint $table) {
            $table->bigIncrements('id')->primary()->unique();
            $table->timestamps();
//            $table->softDeletes();
            $table->string('title');
            $table->string('name')->index();
            $table->string('type'); // for multi values, this will be `json`
            $table->string('description')->nullable();
            $table->text('value');
            $table->bigInteger('model_id', false, true)->index();
            $table->bigInteger('meta_definition_id', false, true)->index();
            $table->unique(['model_id', 'meta_definition_id', 'name']);
        });

        Schema::create($this->tables->get('placement'), function (Blueprint $table) {
            $table->bigIncrements('id')->primary()->unique();
            $table->timestamps();
            $table->softDeletes();
            $table->bigInteger('model_id', false, true);
            $table->bigInteger('parent_id', false, true)->nullable();
            $table->json('children')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->tables->get('metaValue'));
        Schema::dropIfExists($this->tables->get('metaDefinition'));
        Schema::dropIfExists($this->tables->get('placement'));
        Schema::dropIfExists($this->tables->get('model'));
    }

//    create table objects(id integer, title varchar(100), objecttype varchar(100));
//    create table placements(id integer, objectid integer, parentid integer, children JSON);
//
//    -- sites
//    insert into objects(id, title, objecttype) values(1, "Referee", "site");
//
//    -- sports
//    insert into objects(id, title, objecttype) values(2, "EcuaVolley", "sport");
//    insert into objects(id, title, objecttype) values(3, "Handball", "sport");
//    insert into objects(id, title, objecttype) values(4, "Soccer", "sport");
//
//    -- games
//    insert into objects(id, title, objecttype) values(5, "Edi vs. beto", "game");
//
//    -- sites placements
//    insert into placements(id, objectid, parentid, children) values(1, 1, 0, "[2, 3, 4]");
//
//    -- sports placements
//    insert into placements(id, objectid, parentid, children) values(2, 2, 1, "[5]");
//    insert into placements(id, objectid, parentid, children) values(3, 3, 1, null);
//    insert into placements(id, objectid, parentid, children) values(4, 4, 1, null);
//
//    -- games placements
//    insert into placements(id, objectid, parentid, children) values(5, 5, 2, null);
//
//    -- select parent of a given object
//    select obj.* from objects as obj
//      join placements as place on place.objectid = 5 and place.parentid = obj.id;
//
//    -- -- select children of a given object
//    select obj.* from objects as obj
//      join placements as place on place.objectid = 1 and JSON_CONTAINS(place.children, CAST(obj.id AS JSON))
//
//

}
