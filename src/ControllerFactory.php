<?php

namespace GenerCodeOrm;


class ControllerFactory {

    static function make($model) {
        $controller = app()->make("\PressToJam\Controller\\" . str_replace("-", "", ucwords($model, "-")));
        return $controller;
    }
}