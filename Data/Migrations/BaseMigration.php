<?php

namespace ixavier\LaravelLibraries\Data\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use ixavier\LaravelLibraries\Data\Models\MetaDefinition;
use ixavier\LaravelLibraries\Data\Models\Placement;
use ixavier\LaravelLibraries\Data\Models\Relationships\MetaValue;
use ixavier\LaravelLibraries\Data\Models\Model;

abstract class BaseMigration extends Migration
{
    /** @var array Models for this migration */
    protected $models = [];

    /** @var Collection Names of the tables that will be created */
    protected $tables;

    public function __construct()
    {
        $this->loadTableNames();
    }

    /**
     * BaseMigration constructor.
     */
    public function loadTableNames()
    {
        $this->tables = new Collection([
            'model' => (new Model())->getTable(),
            'metaDefinition' => (new MetaDefinition())->getTable(),
            'metaValue' => (new MetaValue())->getTable(),
            'placement' => (new Placement())->getTable(),
        ]);
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tables->get('model'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->softDeletes();
            $table->string('title');
            $table->string('type');
            $table->bigInteger('deleted_by');
            $table->bigInteger('updated_by');
            $table->text('content');
        });

        Schema::create($this->tables->get('metaDefinition'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->softDeletes();
            $table->string('title');
            $table->string('name');
            $table->string('type'); // for multi values, this will be `json`
            $table->string('description');
            $table->bigInteger('model_id');
        });

        Schema::create($this->tables->get('metaValue'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
//            $table->softDeletes();
            $table->bigInteger('model_id');
            $table->bigInteger('meta_definition_id');
            $table->text('value');
        });

        Schema::create($this->tables->get('placement'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->softDeletes();
            $table->bigInteger('model_id');
            $table->bigInteger('parent_id');
            $table->json('children');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach ($this->tables as $table) {
            Schema::dropIfExists($table->get('metaValue'));
            Schema::dropIfExists($table->get('metaDefinition'));
            Schema::dropIfExists($table->get('model'));
        }
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
