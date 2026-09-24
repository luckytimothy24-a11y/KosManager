<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\LaporanController as BaseController;
use Illuminate\Http\Request;

class LaporanController extends BaseController
{
    // Inherits all methods from App\Http\Controllers\LaporanController.
    // Super Admin uses the same unified report logic with accessibleKosIds()
    // inherited from the base controller, which already handles role-based
    // authorization (super_admin → all kos, admin → assigned kos, owner → own kos).
}
