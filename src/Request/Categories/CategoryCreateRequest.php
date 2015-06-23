<?php
/**
 * @author @ct-jensschulze <jens.schulze@commercetools.de>
 * @created: 26.01.15, 15:57
 */

namespace Sphere\Core\Request\Categories;


use Sphere\Core\Model\Category\CategoryDraft;
use Sphere\Core\Model\Common\Context;
use Sphere\Core\Request\AbstractCreateRequest;

/**
 * Class CategoryCreateRequest
 * @package Sphere\Core\Request\Categories
 * @link http://dev.sphere.io/http-api-projects-categories.html#create-category
 */
class CategoryCreateRequest extends AbstractCreateRequest
{
    protected $resultClass = '\Sphere\Core\Model\Category\Category';

    /**
     * @param CategoryDraft $category
     * @param Context $context
     */
    public function __construct(CategoryDraft $category, Context $context = null)
    {
        parent::__construct(CategoriesEndpoint::endpoint(), $category, $context);
    }

    /**
     * @param CategoryDraft $category
     * @param Context $context
     * @return static
     */
    public static function ofDraft(CategoryDraft $category, Context $context = null)
    {
        return new static($category, $context);
    }
}
