<?php

namespace App\Http\Controllers\Api;

use App\Models\Videoconference;
use Orion\Concerns\DisableAuthorization;
use Orion\Http\Controllers\RelationController;

class VideoconferenceStudgroupController extends RelationController
{
    use DisableAuthorization;

    protected $model = Videoconference::class;
    // protected $policy = AdminPolicy::class;
    protected $relation = 'studgroups';

    protected $pivotFillable = ['vc_id', 'studgroup_id'];
    protected $pivotJson = ['studgroup_id'];
}
