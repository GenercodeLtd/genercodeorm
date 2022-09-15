<?php
namespace GenerCodeOrm\Cells;

abstract class ValidationRules
{
    const OK = 0;
    const OutOfRangeMin = 1;
    const OutOfRangeMax = 2;
    const Characters = 3;
    const CharactersNegative = 4;
    const Unique = 5;
    const NullViolation = 6;
}