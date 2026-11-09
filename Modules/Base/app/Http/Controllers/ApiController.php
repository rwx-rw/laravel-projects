<?php

namespace Modules\Base\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Modules\Base\Traits\ApiResponse;

class ApiController extends Controller {
    use AuthorizesRequests,ValidatesRequests,ApiResponse;
}
