<?php 

namespace CmsCanvas\Container\Cache;

use Theme;
use CmsCanvas\Support\Contracts\Page as PageInterface;
use CmsCanvas\Models\Content\Entry;
use CmsCanvas\Models\Content\Type;

class Page implements PageInterface {

    /**
     * An Entry or Content Type
     *
     * @var \CmsCanvas\Support\Contracts\Page
     */
    protected $resource;

    /**
     * Collection of content type fields with entry data
     *
     * @var \CmsCanvas\Models\Content\Type\Field|Collection
     */
    protected $contentTypeFields;

    /**
     * Defines the order in which to sort.
     *
     * @param  int $resourceId
     * @param  string $resourceType
     * @return void
     */
    public function __construct($resourceId, $resourceType = 'entry')
    {
        if ($resourceType == 'contentType') {
            $this->resource = Type::find($resourceId);
        } else {
            $this->resource = Entry::find($resourceId);
            $this->resource->contentType;
        }

        $this->contentTypeFields = $this->resource->getContentTypeFields(true);
    }

    /**
     * Renders the cached resource
     *
     * @param array $parameters
     * @return \CmsCanvas\Content\Entry\Render|\CmsCanvas\Content\Type\Render
     */
    public function render($parameters = [])
    {
        $this->resource->setCache($this);

        if ($this->resource instanceof Entry) {
            $builder = $this->resource->newEntryBuilder($parameters);
        } else {
            $builder = $this->resource->newContentTypeBuilder($parameters);
        }

        $content = $builder->render();
        $layoutName = $builder->getThemeLayout();

        if ($layoutName != null) {
            Theme::setLayout($layoutName);
            $layout = Theme::getLayout()->with($builder->getRenderedData());
            $layout->content = $content; // Note: this overrides any field data with the name content

            return $layout;
        }

        return $content;
    }

    /**
     * Get content type fields with data
     *
     * @return \CmsCanvas\Models\Content\Type\Field|Collection
     */
    public function getContentTypeFields()
    {
        return $this->contentTypeFields;
    }

    /**
     * Get the resource for the cache
     *
     * @return \CmsCanvas\Support\Contracts\Page
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Renders the cache as a page
     *
     * @param array $parameters
     * @return \CmsCanvas\Content\Entry\Render|\CmsCanvas\Content\Type\Render
     */
    public function renderPage($parameters = [])
    {
        // Add the resource instance to the service continer for global access
        app()->instance('CmsCanvasPageResource', $this->resource);

        $content = $this->render($parameters);

        if ($this->resource instanceof Entry) {
            $this->resource->includeThemeMetadata();
        }
        // TODO (diyphpdeveloper): consider using the rendered data cached in the resource builder
        // so that the page head does not have to render the fields again.
        $this->resource->includeThemePageHead();

        return $content;
    }

}
