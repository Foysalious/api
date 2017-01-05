<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Partner extends Model {
    protected $guarded = [
        'id',
    ];

    protected $resourcePivotColumns = ['designation', 'department', 'is_verified', 'verification_note', 'created_by', 'created_by_name', 'created_at', 'updated_by', 'updated_by_name', 'updated_at'];
    protected $categoryPivotColumns = ['id', 'experience', 'response_time_min', 'response_time_max', 'commission', 'is_verified', 'verification_note', 'created_by', 'created_by_name', 'created_at', 'updated_by', 'updated_by_name', 'updated_at'];
    protected $servicePivotColumns = ['id', 'description', 'options', 'prices', 'is_published', 'discount', 'discount_start_date', 'discount_start_date', 'is_verified', 'verification_note', 'created_by', 'created_by_name', 'created_at', 'updated_by', 'updated_by_name', 'updated_at'];

    public function basicInformations()
    {
        return $this->hasOne(PartnerBasicInformation::class);
    }

    public function bankInformations()
    {
        return $this->hasOne(PartnerBankInformation::class);
    }

    public function admins()
    {
        return $this->belongsToMany(Resource::class)
            ->where('resource_type', constants('RESOURCE_TYPES')['Admin'])
            ->withPivot($this->resourcePivotColumns);
    }

    public function operationResources()
    {
        return $this->belongsToMany(Resource::class)
            ->where('resource_type', constants('RESOURCE_TYPES')['Operation'])
            ->withPivot($this->resourcePivotColumns);
    }

    public function financeResources()
    {
        return $this->belongsToMany(Resource::class)
            ->where('resource_type', constants('RESOURCE_TYPES')['Finance'])
            ->withPivot($this->resourcePivotColumns);
    }

    public function resources()
    {
        return $this->belongsToMany(Resource::class)
            ->where('resource_type', constants('RESOURCE_TYPES')['Handyman'])
            ->withPivot($this->resourcePivotColumns);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class)->withPivot($this->categoryPivotColumns);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class);
    }

    public function locations()
    {
        return $this->belongsToMany(Location::class);
    }

    public function getLocationsList()
    {
        return $this->locations->lists('id')->toArray();
    }

    public function getLocationsNames()
    {
        return $this->locations->lists('name')->toArray();
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function orders()
    {
        return $this->hasMany(PartnerOrder::class);
    }

    public function jobs()
    {
        return $this->hasManyThrough(Job::class, PartnerOrder::class);
    }

    public function partner_orders()
    {
        return $this->hasMany(PartnerOrder::class);
    }

    public function commission($service_id)
    {
        $service_category = Service::find($service_id)->category->id;
        return $this->categories()->find($service_category)->pivot->commission;
    }

}
