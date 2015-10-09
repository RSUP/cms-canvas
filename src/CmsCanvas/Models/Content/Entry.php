<?php 

namespace CmsCanvas\Models\Content;

use Lang, stdClass, Cache, DB, Auth;
use CmsCanvas\Content\Page\PageInterface;
use CmsCanvas\Database\Eloquent\Model;
use CmsCanvas\Content\Type\FieldType;
use CmsCanvas\Models\Content\Type\Field;
use CmsCanvas\Models\Language;
use CmsCanvas\Models\Content\Revision;
use CmsCanvas\Container\Cache\Page;
use CmsCanvas\Content\Entry\Render;
use CmsCanvas\Exceptions\PermissionDenied;
use CmsCanvas\Exceptions\Exception;
use CmsCanvas\Content\Entry\Builder\Entry as EntryBuilder;

class Entry extends Model implements PageInterface {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'entries';

    /**
     * The columns that can be mass-assigned.
     *
     * @var array
     */
    protected $fillable = [
        'title', 
        'url_title', 
        'route',
        'meta_title',
        'meta_keywords',
        'meta_description',
        'entry_status_id',
        'author_id',
        'created_at',
    ];

    /**
     * The columns that can NOT be mass-assigned.
     *
     * @var array
     */
    protected $guarded = ['id', 'updated_at'];

    /**
     * Manually manage the timestamps on this class
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The columns that can sorted with the query builder orderBy method.
     *
     * @var array
     */
    protected static $sortable = [
        'id', 
        'title', 
        'route', 
        'content_type_title', 
        'entry_status_name', 
        'updated_at',
    ];

    /**
     * The column to sort by if no session order by is defined.
     *
     * @var string
     */
    protected static $defaultSortColumn = 'updated_at';

    /**
     * The the sort order that the default column should be sorted by.
     *
     * @var string
     */
    protected static $defaultSortOrder = 'desc';

    /**
     * An object used to retrive cached data
     *
     * @var \CmsCanvas\Container\Cache\Page
     */
    protected $cache;

    /**
     * Defines a one to many relationship with content types
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contentType()
    {
        return $this->belongsTo('\CmsCanvas\Models\Content\Type', 'content_type_id');
    }

    /**
     * Defines a many to one relationship with user
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function author()
    {
        return $this->hasOne('\CmsCanvas\Models\User', 'id', 'author_id');
    }

    /**
     * Returns all data for all lanaguages for the current entry
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function allData()
    {
        return $this->hasMany('CmsCanvas\Models\Content\Entry\Data', 'entry_id', 'id')
            ->join('languages', 'entry_data.language_id', '=', 'languages.id')
            ->select('entry_data.*', 'languages.locale');
    }

    /**
     * Returns all revisions for the current entry
     * in order of newest to oldest
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function revisions()
    {
        return $this->hasMany('CmsCanvas\Models\Content\Revision', 'resource_id', 'id')
            ->where('resource_type_id', Revision::ENTRY_RESOURCE_TYPE_ID)
            ->orderBy('id', 'desc');
    }

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        self::deleting(function($entry) {
            $entry->validateForDeletion();
        });
    }

    /**
     * Save the model to the database.
     *
     * @param array $options
     * @return bool
     */
    public function save(array $options = [])
    {
        $time = $this->freshTimestamp();
        $this->setUpdatedAt($time);

        return parent::save($options);
    }

    /**
     * Queries for content type fields with entry data
     *
     * @return \CmsCanvas\Models\Content\Type\Field|Collection
     */
    public function getContentTypeFields()
    {
        if ($this->cache != null) {
            return $this->cache->getContentTypeFields();
        }

        $entry = $this;
        $locale = Lang::getLocale();

        $query = Field::with('type')
            ->select('content_type_fields.*', 'entry_data.data', 'entry_data.metadata')
            ->join('content_types', 'content_types.id', '=', 'content_type_fields.content_type_id')
            ->leftJoin(
                DB::raw('(`entry_data` inner join `languages` on `entry_data`.`language_id` = `languages`.`id` and `languages`.`locale` = \''.$locale.'\')'), 
                function($join) use($entry) {
                    $join->on('entry_data.content_type_field_id', '=', 'content_type_fields.id')
                        ->where('entry_data.entry_id', '=', $entry->id);
                }
            )
            ->where('content_types.id', $entry->content_type_id);

        return $query->get();
    }

    /**
     * Returns an array of transalated data for the current entry
     *
     * @return array
     */
    public function getRenderedData()
    {
        if ($this->cache == null) {
            $entry = $this;

            $cache = Cache::rememberForever($this->getRouteName(), function() use($entry) {
                return new Page($entry->id, 'entry');
            });

            return $cache->getRenderedData();
        } else {
            $contentTypeFields = $this->getContentTypeFields();

            $locale = Lang::getLocale();
            $data = [];

            foreach ($contentTypeFields as $contentTypeField) {
                $fieldType = FieldType::factory(
                    $contentTypeField, 
                    $this, 
                    $locale, 
                    $contentTypeField->data, 
                    $contentTypeField->metadata
                );
                $data[$contentTypeField->short_tag] = $fieldType->render();
            }

            $data['title'] = $this->title;
            $data['entry_id'] = $this->id;
            $data['created_at'] = $this->created_at;
            $data['updated_at'] = $this->updated_at;

            return $data;
        }
    }

    /**
     * Returns a render instance
     *
     * @param array $parameters
     * @return \CmsCanvas\Content\Entry\Builder\Entry
     */
    public function newEntryBuilder($parameters = [])
    {
        return new EntryBuilder($this, $parameters);
    }

    /**
     * Creates new builder item instances for a collection 
     *
     * @param  \CmsCanvas\Models\Content\Entry|collection
     * @return \CmsCanvas\Content\Entry\Builder\Entry|array
     */
    public static function newEntryBuilderCollection($entries)
    {
        $entryBuilders = [];
        $entryCount = count($entries);
        $counter = 1;

        foreach ($entries as $entry) {
            $entryBuilder = $entry->newEntryBuilder();

            $entryBuilder->setIndex($counter - 1);

             if ($counter !== 1) {
                $entryBuilder->setFirstFlag(false);
            }

            if ($counter !== $entryCount) {
                $entryBuilder->setLastFlag(false);
            }

            $entryBuilders[] = $entryBuilder;
            $counter++;
        }

        return $entryBuilders;
    }

    /**
     * Returns a render instance
     *
     * @param array $parameters
     * @return \CmsCanvas\Content\Entry\Render
     */
    public function render($parameters = [])
    {
        return $this->newEntryBuilder($parameters)->render();
    }

    /**
     * Renders an entry page from cache
     *
     * @param \CmsCanvas\Container\Cache\Page $cache
     * @return self
     */
    public function setCache(\CmsCanvas\Container\Cache\Page $cache)
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * Unsets the cache object on the current entry
     *
     * @return self
     */
    public function clearCache()
    {
        $this->cache = null;

        return $this;
    }

    /**
     * Returns the route name for the entry
     *
     * @return string
     */
    public function getRouteName()
    {
        $locale = Lang::getLocale();

        return 'entry.'.$this->id.'.'.$locale;
    }

    /**
     * Returns the full route for the entry.
     * If a route has not been set then it will return the dynamic route.
     *
     * @return string|null
     */
    public function getPreferredRoute()
    {
        if ($this->getRoute() !== null) {
            return $this->getRoute();
        } elseif ($this->getDynamicRoute() !== null) {
            return $this->getDynamicRoute();
        }

        return null;
    }

    /**
     * Returns the full route for the entry
     *
     * @return string|null
     */
    public function getRoute()
    {
        if ($this->route !== null && $this->route !== '') {
            return '/'.$this->route;
        }

        return null;
    }

    /**
     * Returns the dynamic route name for the entry
     *
     * @return string
     */
    public function getDynamicRouteName()
    {
        return $this->getRouteName().'.dynamic';
    }

    /**
     * Generates a dynamic route using the
     * content type's route and the entry's url title.
     *
     * @return string|null
     */
    public function getDynamicRoute()
    {
        if ($this->url_title !== null && $this->url_title !== '' 
            && $this->contentType->getRoute() !== null
        ) {
            return $this->contentType->getRoute().'/'.$this->url_title;
        }

        return null;
    }

    /**
     * Remove the first and last forward slash from the route
     *
     * @param string $value
     * @return string
     */
    public function getRouteAttribute($value)
    {
        return trim($value, '/');
    }

    /**
     * Remove the first and last forward slash from the route
     *
     * @param string $value
     * @return void
     */
    public function setRouteAttribute($value)
    {
        $value = trim($value, '/');

        if ($value === '') {
            $value = null;
        }

        $this->attributes['route'] = $value;
    }

    /**
     * Checks if the current entry can be deleted.
     *
     * @throws \CmsCanvas\Exceptions\PermissionDenied
     * @throws \CmsCanvas\Exceptions\Exception
     * @return bool|string
     */
    public function validateForDeletion()
    {
        $permission = null;

        if ($this->contentType->adminEntryDeletePermission != null) {
            $permission = $this->contentType->adminEntryDeletePermission->key_name;
        }

        if ($permission != null && ! Auth::user()->can($permission)) {
            throw new PermissionDenied(
                $permission,
                "You do not have permission to delete the entry \"{$this->title}\","
                . " please refer to your system administrator."
            );
        }

        if ($this->isHomePage()) {
            throw new Exception(
                "The entry \"{$this->title}\" can not be deleted because it set as the default home page"
            );
        }

        if ($this->isCustom404Page()) {
            return Exception(
                "The entry \"{$this->title}\" can not be deleted because it set as the default custom 404 page."
            );
        }

        return true;
    }

    /**
     * Sets data order by using a custom object
     *
     * @param Builder $query
     * @param OrderBy $orderBy
     * @return Builder
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
     * @return Builder
     */
    public function scopeApplyFilter($query, $filter)
    {
        if (isset($filter->search) && $filter->search != '') {
            $query->where('entries.title', 'LIKE', "%{$filter->search}%");
        }

        if ( ! empty($filter->content_type_id)) {
            $query->where('content_type_id', $filter->content_type_id); 
        }

        if ( ! empty($filter->entry_status_id)) {
            $query->where('entry_status_id', $filter->entry_status_id); 
        }

        return $query;
    }

    /**
     * Checks if the current entry is set as the default 
     * home page in the settings
     *
     * @return bool
     */
    public function isHomePage()
    {
        return ($this->id == \Config::get('cmscanvas::config.site_homepage'));
    }

    /**
     * Checks if the current entry is set as the default 
     * custom 404 page in the settings
     *
     * @return bool
     */
    public function isCustom404Page()
    {
        return ($this->id == \Config::get('cmscanvas::config.custom_404'));
    }

    /**
     * Returns date fields as a carbon instance
     *
     * @return array
     */
    public function getDates()
    {
        return ['created_at', 'updated_at'];
    }

}