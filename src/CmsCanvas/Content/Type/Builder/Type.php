<?php 

namespace CmsCanvas\Content\Type\Builder;

use StringView;
use CmsCanvas\Models\Content\Type as ContentTypeModel;
use CmsCanvas\Content\Type\Render;

class Type {

    /**
     * @var \CmsCanvas\Models\Content\Type
     */
    protected $contentType;

    /**
     * Parameters added to the route
     *
     * @var array
     */
    protected $parameters;

    /**
     * Rendered data
     *
     * @var array
     */
    protected $renderedData;

    /**
     * Constructor
     *
     * @param  \CmsCanvas\Models\Content\Type  $contentType
     * @param  array $parameters
     * @param  array $renderedData
     * @return void
     */
    public function __construct(ContentTypeModel $contentType, $parameters = [])
    {
        $this->contentType = $contentType;
        $this->parameters = $parameters;
        $this->renderedData = $this->contentType->getRenderedData();
    }

    /**
     * Returns the content type model instance
     *
     * @return \CmsCanvas\Models\Content\Type
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * Returns a render instance
     *
     * @return \CmsCanvas\Content\Type\Render
     */
    public function render()
    {
        $render = new Render($this);
        $this->setParameter('self', $render);

        return $render;
    }

    /**
     * Generates a view with the content type's data
     *
     * @return string
     */
    public function renderContents()
    {
        $data = array_merge($this->renderedData, $this->parameters);

        $template = ($this->contentType->layout === null) ? '' : $this->contentType->layout;
        $content = StringView::make($template)
            ->cacheKey($this->contentType->getRouteName())
            ->updatedAt($this->contentType->updated_at->timestamp)
            ->with($data);

        return $content;
    }

    /**
     * Returns parameters and rendered data
     *
     * @param  string  $key
     * @return mixed
     */
    public function getData($key)
    {
        if (isset($this->parameters[$key])) {
            return $this->parameters[$key];
        }

        if (isset($this->renderedData[$key])) {
            return $this->renderedData[$key];
        }        

        return null;
    }

    /**
     * Adds a parameter to the paramaters array
     *
     * @param  string  $key
     * @param  string  $value
     * @return void
     */
    public function setParameter($key, $value)
    {
        $this->parameters[$key] = $value;
    }

}
