<?php

namespace App\View\Composers;

use Illuminate\View\View;
use App\Models\Patient;

class ChildRecordComposer
{
    public function compose(View $view)
    {
        $mothers = Patient::whereHas('prenatalRecords', function ($q) {
            $q->where('status', 'completed');
        })->get();
        
        $view->with('mothers', $mothers);
    }
}