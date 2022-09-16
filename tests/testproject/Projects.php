<?php

namespace GenerCode\Schemas;
use \GenerCodeOrm\Cells as Cell;


class Projects extends \GenerCodeOrm\Schema {

    function __construct($slug = "") {
        parent::__construct($slug, "projects", "projects");

        $cell = new Cell\IdCell();
        $cell->alias = "--id";
        $cell->name = "id";
        $cell->reference_type = Cell\ReferenceTypes::PRIMARY;
        $cell->system = true;
        $cell->setValidation(1, 18446744073709551615);
        $refs = [];
        $refs["models"] = "models";
        $refs["profiles"] = "profiles";
        $refs["dictionary-templates"] = "dictionary-templates";
        $refs["pages"] = "pages";
        $refs["sync-db-log"] = "sync-db-log";
        $cell->reference = $refs;
        $cell->immutable = true;
        $cell->model = $this->model;
        $this->cells[$cell->alias] = $cell;


        $cell = new Cell\IdCell();
        $cell->alias = $this->alias;
        $cell->alias = "--owner";
        $cell->name = "user_login_id";
        $cell->setValidation(1, 18446744073709551615);
        $cell->immutable = true;
        $cell->reference_type = Cell\ReferenceTypes::OWNER;
        $cell->reference = "user-login";
        $cell->model = $this->model;
        $cell->system = true;
        $cell->background = true;
        $this->cells[$cell->alias] = $cell;

        $cell = new Cell\TimeCell();
        $cell->alias = $this->alias;
        $cell->name = "date_created";
        $cell->alias = "--created";
        $cell->immutable = true;
        $cell->model = $this->model;
        $cell->system = true;
        $this->cells[$cell->alias] = $cell;


        $cell = new Cell\TimeCell();
        $cell->name = "last_updated";
        $cell->alias = $this->alias;
        $cell->alias = "--updated";
        $cell->immutable = true;
        $cell->model = $this->model;
        $cell->system = true;
        $this->cells[$cell->alias] = $cell;

        $cell = new Cell\StringCell();
        $cell->name = "domain";
        $cell->setValidation(0, 100, '', '[<>]+');
        $cell->alias = $this->alias;
        $cell->default = "";
        $cell->summary = true;
        $cell->model = $this->model;
        $cell->alias = "domain";
        $this->cells[$cell->alias] = $cell;

        $cell = new Cell\StringCell();
        $cell->name = "hosting_status";
        $cell->setValidation(0, 255, 'active|demo|notactive|cancelled|restricted', '[<>]+');
        $cell->alias = $this->alias;
        $cell->default = "";
        $cell->model = $this->model;
        $cell->alias = "hosting-status";
        $this->cells[$cell->alias] = $cell;

        $cell = new Cell\StringCell();
        $cell->name = "cfdist_id";
        $cell->setValidation(0, 30, '', '[<>]+');
        $cell->alias = $this->alias;
        $cell->default = "";
        $cell->model = $this->model;
        $cell->alias = "cfdist-id";
        $this->cells[$cell->alias] = $cell;

        $cell = new Cell\FlagCell();
        $cell->name = "termsnc";
        $cell->required = true;

        $cell->setValidation(1, 1);
        $cell->alias = $this->alias;
        $cell->default = 1;
        $cell->model = $this->model;
        $cell->alias = "termsnc";
        $this->cells[$cell->alias] = $cell;

        $cell = new Cell\StringCell();
        $cell->name = "import_code";
        $cell->setValidation(0, 255, '', '[<>]+');
        $cell->alias = $this->alias;
        $cell->default = "";
        $cell->model = $this->model;
        $cell->alias = "import-code";
        $this->cells[$cell->alias] = $cell;

        $cell = new Cell\NumberCell();
        $cell->name = "monthly_price";
        $cell->setValidation(0, 4294967295);
        $cell->round = 2;
        $cell->alias = $this->alias;
        $cell->default = 0;
        $cell->model = $this->model;
        $cell->alias = "monthly-price";
        $this->cells[$cell->alias] = $cell;

        $cell = new Cell\AssetCell();
        $cell->name = "src";
        $cell->dir = "projects/src/";
        $cell->name_template = "projects_src_%id.%ext";
        $cell->setValidation(0, null, '', '');
        $cell->alias = $this->alias;
        $cell->default = "";
        $cell->model = $this->model;
        $cell->alias = "src";
        $this->cells[$cell->alias] = $cell;

        $cell = new Cell\AssetCell();
        $cell->name = "custom_file";
        $cell->dir = "projects/custom-file/";
        $cell->name_template = "projects_custom_file_%id.%ext";
        $cell->setValidation(0, null, '', '');
        $cell->alias = $this->alias;
        $cell->default = "";
        $cell->model = $this->model;
        $cell->alias = "custom-file";
        $this->cells[$cell->alias] = $cell;

        $cell = new Cell\FlagCell();
        $cell->name = "process";

        $cell->setValidation(0, 1);
        $cell->alias = $this->alias;
        $cell->default = 0;
        $cell->model = $this->model;
        $cell->alias = "process";
        $this->cells[$cell->alias] = $cell;

    }

}
