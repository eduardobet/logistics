<?php

namespace Logistics\Http\Controllers\Tenant\Api;

use Illuminate\Http\Request;
use Logistics\DB\Tenant\Department;
use Logistics\Http\Controllers\Controller;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($parentId)
    {
        return (new Department())->getDepartmentAsList($parentId);
    }
}
