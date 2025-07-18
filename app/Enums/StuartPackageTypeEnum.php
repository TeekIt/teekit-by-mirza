<?php

namespace App\Enums;

enum StuartPackageTypeEnum: string
{
/** 
     * Official values are taken from Stuart API documentation.
     * https://api-docs.stuart.com/?_gl=1*vyc3iu*_gcl_au*MzExMDE3NDk0LjE3NTE4ODk0NjcuMTkwMTY5NDc5OS4xNzUyNjk1MDk5LjE3NTI2OTUyOTM.#e4bc63c0-a8c5-4a73-a0ed-b4ddcd5533a8
     */
    case EXTRA_SMALL = 'xsmall';
    case SMALL = 'small';
    case MEDIUM = 'medium';
    case LARGE = 'large';
    case EXTRA_LARGE = 'xlarge';
}
