<?php

namespace Logistics\Http\Controllers\Tenant\Api;

use Illuminate\Http\Request;
use Logistics\DB\Tenant\Client;
use Logistics\Http\Controllers\Controller;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($branchId)
    {
        return (new Client())->getClientAsList($branchId);
    }
}
