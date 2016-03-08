<?php 

namespace CmsCanvas\Models;

use CmsCanvas\Database\Eloquent\Model;

class Permission extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'permissions';

    /**
     * The columns that can be mass-assigned.
     *
     * @var array
     */
    protected $fillable = ['name', 'key_name'];

    /**
     * The columns that can NOT be mass-assigned.
     *
     * @var array
     */
    protected $guarded = ['id', 'editable_flag', 'created_at', 'updated_at'];

    /**
     * The columns that can sorted with the query builder orderBy method.
     *
     * @var array
     */
    protected static $sortable = ['name', 'key_name'];

    /**
     * The column to sort by if no session order by is defined.
     *
     * @var string
     */
    protected static $defaultSortColumn = 'name';

    /**
     * Defines a many to many relationship with roles
     *
     * @return BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany('\CmsCanvas\Models\Role', 'role_permissions', 'permission_id', 'role_id');
    }

    /**
     * Check if the permission is assigned to the specified role
     *
     * @param string $name
     * @return bool
     */
    public function hasRole($name)
    {
        foreach ($this->roles as $role) {
            if ($role->name == $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sets data order by using a custom object
     *
     * @param Builder $query
     * @param OrderBy $orderBy
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function scopeApplyOrderBy($query, \CmsCanvas\Container\Database\OrderBy $orderBy)
    {
        if (in_array($orderBy->getColumn(), self::$sortable)) {
            $query->orderBy($orderBy->getColumn(), $orderBy->getSort()); 
        }

        return $query;
    } 

    /**
     * Filters and queries using a custom object
     *
     * @param Builder $query
     * @param object $filter
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function scopeApplyFilter($query, $filter)
    {
        if ( isset($filter->search) && $filter->search != '') {
            $query->where('name', 'LIKE', "%{$filter->search}%");
            $query->orWhere('key_name', 'LIKE', "%{$filter->search}%");
        }

        return $query;
    }

}