<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class ActivityLog extends Model
{
    use SoftDeletes;
    protected $fillable = ['user_id', 'organization_id', 'event_id', 'action', 'description'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}