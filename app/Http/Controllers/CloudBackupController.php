<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CloudBackupController extends Controller
{
    public function index()
    {
        return view('midwife.cloudbackup.index');
    }
}

