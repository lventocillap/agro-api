<?php

namespace App\Exceptions\Image;

use Exception;

class NotFoundImage extends Exception
{
    public function __construct()
    {
        parent::__construct('La imagen no existe', 404);
    }
}
