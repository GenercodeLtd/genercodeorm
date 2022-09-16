<?php

namespace GenerCode\Schemas;
use \GenerCodeOrm\Cells as Cell;


class Models extends \GenerCodeOrm\Schema {

    protected $cells = [];

function __construct($slug = "")
{
    parent::__construct($slug, "models", "models");

    $cell = new Cell\IdCell();
    $cell->alias = $this->alias;
    $cell->name = "id";
    $cell->reference_type = Cell\ReferenceTypes::PRIMARY;
    $cell->alias = "--id";
    $cell->system = true;
    $cell->setValidation(1, 18446744073709551615);
    $refs = [];
    $refs["fields"] = "fields";
    $refs["sections"] = "sections";
    $refs["states"] = "states";
    $cell->reference = $refs;
    $cell->immutable = true;
    $cell->model = $this->model;
    $this->cells[$cell->alias] = $cell;

    $cell = new Cell\IdCell();
    $cell->alias = $this->alias;
    $cell->name = "projects_id";
    $cell->alias = "--parent";
    $cell->reference_type = Cell\ReferenceTypes::PARENT;
    $cell->system = true;
    $cell->setValidation(1, 18446744073709551615);
    $cell->background = true;
    $cell->reference = "projects";
    $cell->immutable = true;
    $cell->model = $this->model;
    $this->cells[$cell->alias] = $cell;

    $cell = new Cell\IdCell();
    $cell->alias = $this->alias;
    $cell->name = "archive_id";
    $cell->alias = "--archive";
    $cell->setValidation(1, 18446744073709551615);
    $cell->immutable = true;
    $cell->model = $this->model;
    $cell->system = true;
    $cell->background = true;
    $this->cells[$cell->alias] = $cell;

    $cell = new Cell\NumberCell();
    $cell->alias = $this->alias;
    $cell->name = "_sort";
    $cell->setValidation(0, 65535);
    $cell->alias = "--sort";
    $cell->model = $this->model;
    $cell->system = true;
    $cell->background = true;
    $this->cells[$cell->alias] = $cell;

    $cell = new Cell\IdCell();
    $cell->alias = $this->alias;
    $cell->name = "_recursive_id";
    $cell->is_recursive = true;
    $cell->alias = "--recursive";
    $cell->setValidation(0, 18446744073709551615);
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
    $cell->name = "name";
    $cell->setValidation(1, 50, '', '[<>]+');
    $cell->alias = $this->alias;
    $cell->default = "";
    $cell->summary = true;
    $cell->model = $this->model;
    $cell->alias = "name";
    $this->cells[$cell->alias] = $cell;

    $cell = new Cell\IdCell();
    $cell->name = "has_profile_owner";
    $cell->setValidation(0, 4294967295);
    $cell->reference_type = Cell\ReferenceTypes::REFERENCE;
    $cell->reference = "has-profile-owner";
    $cell->alias = $this->alias;
    $cell->default = 0;
    $cell->model = $this->model;
    $cell->alias = "has-profile-owner";
    $this->cells[$cell->alias] = $cell;

    $cell = new Cell\StringCell();
    $cell->name = "primary_key";
    $cell->setValidation(0, 75, '', '[<>]+');
    $cell->alias = $this->alias;
    $cell->default = "";
    $cell->model = $this->model;
    $cell->alias = "primary-key";
    $this->cells[$cell->alias] = $cell;

    $cell = new Cell\StringCell();
    $cell->name = "parent_key";
    $cell->setValidation(0, 75, '', '[<>]+');
    $cell->alias = $this->alias;
    $cell->default = "";
    $cell->model = $this->model;
    $cell->alias = "parent-key";
    $this->cells[$cell->alias] = $cell;

    $cell = new Cell\FlagCell();
    $cell->name = "soft_parent";

    $cell->setValidation(0, 1);
    $cell->alias = $this->alias;
    $cell->default = 0;
    $cell->model = $this->model;
    $cell->alias = "soft-parent";
    $this->cells[$cell->alias] = $cell;

    $cell = new Cell\FlagCell();
    $cell->name = "is_recursive";

    $cell->setValidation(0, 1);
    $cell->alias = $this->alias;
    $cell->default = 0;
    $cell->model = $this->model;
    $cell->alias = "is-recursive";
    $this->cells[$cell->alias] = $cell;

    $cell = new Cell\FlagCell();
    $cell->name = "is_constant";

    $cell->setValidation(0, 1);
    $cell->alias = $this->alias;
    $cell->default = 0;
    $cell->model = $this->model;
    $cell->alias = "is-constant";
    $this->cells[$cell->alias] = $cell;

    $cell = new Cell\FlagCell();
    $cell->name = "audit";

    $cell->setValidation(0, 1);
    $cell->alias = $this->alias;
    $cell->default = 0;
    $cell->model = $this->model;
    $cell->alias = "audit";
    $this->cells[$cell->alias] = $cell;

    $cell = new Cell\FlagCell();
    $cell->name = "sort";

    $cell->setValidation(0, 1);
    $cell->alias = $this->alias;
    $cell->default = 0;
    $cell->model = $this->model;
    $cell->alias = "sort";
    $this->cells[$cell->alias] = $cell;

    $cell = new Cell\FlagCell();
    $cell->name = "archive";

    $cell->setValidation(0, 1);
    $cell->alias = $this->alias;
    $cell->default = 0;
    $cell->model = $this->model;
    $cell->alias = "archive";
    $this->cells[$cell->alias] = $cell;

    $cell = new Cell\FlagCell();
    $cell->name = "post_hooks";

    $cell->setValidation(0, 1);
    $cell->alias = $this->alias;
    $cell->default = 0;
    $cell->model = $this->model;
    $cell->alias = "post-hooks";
    $this->cells[$cell->alias] = $cell;

    $cell = new Cell\FlagCell();
    $cell->name = "put_hooks";

    $cell->setValidation(0, 1);
    $cell->alias = $this->alias;
    $cell->default = 0;
    $cell->model = $this->model;
    $cell->alias = "put-hooks";
    $this->cells[$cell->alias] = $cell;
}
}