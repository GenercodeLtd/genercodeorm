<?php
namespace GenerCodeOrm\Cells;

abstract class ReferenceTypes
{
    const PRIMARY = 0;
    const PARENT = 1;
    const OWNER = 2;
    const REFERENCE = 3;
    const RECURSIVE = 4;
    const CIRCULAR = 5;
}