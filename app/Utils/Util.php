<?php

namespace App\Utils;

use App\Scoping\Scopes\CategoryScope;
use Illuminate\Support\Carbon;

class Util
{
    /**
     * Convert Date Unix Timestamp to string
     */
    public function convertUnixToString($data)
    {
        $unix_date = ($data - 25569) * 86400;

        return $unix_date;
    }

    /**
     * Get current time
     */
    public function now()
    {
        return Carbon::now('Africa/Lagos')->toDateTimeString();
    }

    // Question Scopes
    public function scopes()
    {
        return [
            'category' => new CategoryScope()
        ];
    }
}