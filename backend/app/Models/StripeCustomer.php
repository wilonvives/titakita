<?php

namespace TitaKita\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class StripeCustomer extends BaseModel
{
    use SoftDeletes;
}
