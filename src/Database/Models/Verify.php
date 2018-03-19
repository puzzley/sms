<?php
namespace Puzzley\SMS\Database\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Verify extends Eloquent
{
    protected $table;    
    protected $guarded = ['id', 'created_at', 'updated_at'];
    public function __construct()
    {
        $this->table = \Config::get('sms.table.verifications', 'puzzley_sms_verifications');
    }
}
