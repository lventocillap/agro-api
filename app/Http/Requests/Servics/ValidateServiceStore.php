<?php

declare(strict_types=1);

namespace App\Http\Requests\Servics;

use Illuminate\Http\Request;

trait ValidateServiceStore
{
    public function validateServiceStore(Request $request)
    {
        $request->validate([
            'title' => 'required|string|min:3',
            'description' => 'required|string|min:5'
        ]);
    }
}