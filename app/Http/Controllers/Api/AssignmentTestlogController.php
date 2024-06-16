<?php

namespace App\Http\Controllers\Api;

use App\Models\Assignment;
use App\Policies\TeacherPolicy;
use App\Policies\TruePolicy;
use Orion\Concerns\DisableAuthorization;
use Orion\Http\Controllers\RelationController;

class AssignmentTestlogController extends RelationController
{
    use DisableAuthorization;

    protected $model = Assignment::class;
    protected $policy = TeacherPolicy::class;
    protected $relation = 'testlogs';

    protected $pivotFillable = ['user_id', 'assignment_id'];
    protected $pivotJson = ['uncorrect_answers'];
}
