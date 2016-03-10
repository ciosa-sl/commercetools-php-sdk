<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */


namespace Commercetools\Core\Product;

use Commercetools\Core\ApiTestCase;
use Commercetools\Core\Model\Category\Category;
use Commercetools\Core\Model\Category\CategoryDraft;
use Commercetools\Core\Model\Common\LocalizedString;
use Commercetools\Core\Model\Common\Money;
use Commercetools\Core\Model\Common\PriceDraft;
use Commercetools\Core\Model\Common\PriceDraftCollection;
use Commercetools\Core\Model\Product\LocalizedSearchKeywords;
use Commercetools\Core\Model\Product\ProductDraft;
use Commercetools\Core\Model\Product\SearchKeyword;
use Commercetools\Core\Model\Product\SearchKeywords;
use Commercetools\Core\Model\ProductType\AttributeDefinition;
use Commercetools\Core\Model\ProductType\AttributeDefinitionCollection;
use Commercetools\Core\Model\ProductType\ProductType;
use Commercetools\Core\Model\ProductType\ProductTypeDraft;
use Commercetools\Core\Model\ProductType\StringType;
use Commercetools\Core\Model\State\State;
use Commercetools\Core\Model\State\StateDraft;
use Commercetools\Core\Model\State\StateReferenceCollection;
use Commercetools\Core\Model\TaxCategory\TaxCategory;
use Commercetools\Core\Model\TaxCategory\TaxCategoryDraft;
use Commercetools\Core\Model\TaxCategory\TaxRate;
use Commercetools\Core\Model\TaxCategory\TaxRateCollection;
use Commercetools\Core\Model\Type\FieldDefinition;
use Commercetools\Core\Model\Type\FieldDefinitionCollection;
use Commercetools\Core\Model\Type\Type;
use Commercetools\Core\Model\Type\TypeDraft;
use Commercetools\Core\Model\Type\StringType as CustomStringType;
use Commercetools\Core\Request\Categories\CategoryCreateRequest;
use Commercetools\Core\Request\Categories\CategoryDeleteRequest;
use Commercetools\Core\Request\Products\Command\ProductAddPriceAction;
use Commercetools\Core\Request\Products\Command\ProductAddToCategoryAction;
use Commercetools\Core\Request\Products\Command\ProductAddVariantAction;
use Commercetools\Core\Request\Products\Command\ProductChangeNameAction;
use Commercetools\Core\Request\Products\Command\ProductChangePriceAction;
use Commercetools\Core\Request\Products\Command\ProductChangeSlugAction;
use Commercetools\Core\Request\Products\Command\ProductPublishAction;
use Commercetools\Core\Request\Products\Command\ProductRemoveFromCategoryAction;
use Commercetools\Core\Request\Products\Command\ProductRemovePriceAction;
use Commercetools\Core\Request\Products\Command\ProductRemoveVariantAction;
use Commercetools\Core\Request\Products\Command\ProductRevertStagedChangesAction;
use Commercetools\Core\Request\Products\Command\ProductSetAttributeAction;
use Commercetools\Core\Request\Products\Command\ProductSetAttributeInAllVariantsAction;
use Commercetools\Core\Request\Products\Command\ProductSetCategoryOrderHintAction;
use Commercetools\Core\Request\Products\Command\ProductSetDescriptionAction;
use Commercetools\Core\Request\Products\Command\ProductSetMetaDescriptionAction;
use Commercetools\Core\Request\Products\Command\ProductSetMetaKeywordsAction;
use Commercetools\Core\Request\Products\Command\ProductSetMetaTitleAction;
use Commercetools\Core\Request\Products\Command\ProductSetPriceCustomFieldAction;
use Commercetools\Core\Request\Products\Command\ProductSetPriceCustomTypeAction;
use Commercetools\Core\Request\Products\Command\ProductSetPricesAction;
use Commercetools\Core\Request\Products\Command\ProductSetSearchKeywordsAction;
use Commercetools\Core\Request\Products\Command\ProductSetSKUAction;
use Commercetools\Core\Request\Products\Command\ProductSetTaxCategoryAction;
use Commercetools\Core\Request\Products\Command\ProductTransitionStateAction;
use Commercetools\Core\Request\Products\Command\ProductUnpublishAction;
use Commercetools\Core\Request\Products\ProductCreateRequest;
use Commercetools\Core\Request\Products\ProductDeleteRequest;
use Commercetools\Core\Request\Products\ProductUpdateRequest;
use Commercetools\Core\Request\ProductTypes\ProductTypeCreateRequest;
use Commercetools\Core\Request\ProductTypes\ProductTypeDeleteRequest;
use Commercetools\Core\Request\States\StateCreateRequest;
use Commercetools\Core\Request\States\StateDeleteRequest;
use Commercetools\Core\Request\TaxCategories\TaxCategoryCreateRequest;
use Commercetools\Core\Request\TaxCategories\TaxCategoryDeleteRequest;
use Commercetools\Core\Request\Types\TypeCreateRequest;
use Commercetools\Core\Request\Types\TypeDeleteRequest;

class ProductUpdateRequestTest extends ApiTestCase
{
    /**
     * @var ProductType
     */
    private $productType;

    /**
     * @var ProductDeleteRequest
     */
    private $productDeleteRequest;

    /**
     * @var Category
     */
    private $category;

    /**
     * @var TaxCategory
     */
    private $taxCategory;

    /**
     * @var string
     */
    private $region;

    /**
     * @var State
     */
    private $state1;

    /**
     * @var State
     */
    private $state2;

    /**
     * @var StateDeleteRequest[]
     */
    private $stateCleanupRequests;

    /**
     * @var Type
     */
    private $type;

    public function testChangeName()
    {
        $draft = $this->getDraft('change-name');
        $channel = $this->createProduct($draft);

        $name = $this->getTestRun() . '-new name';
        $request = ProductUpdateRequest::ofIdAndVersion($channel->getId(), $channel->getVersion())
            ->addAction(
                ProductChangeNameAction::ofName(
                    LocalizedString::ofLangAndText('en', $name)
                )
            )
        ;
        $response = $request->executeWithClient($this->getClient());
        $result = $request->mapResponse($response);
        $this->productDeleteRequest->setVersion($result->getVersion());

        $this->assertInstanceOf('\Commercetools\Core\Model\Product\Product', $result);
        $this->assertNotSame($name, $result->getMasterData()->getCurrent()->getName()->en);
        $this->assertSame($name, $result->getMasterData()->getStaged()->getName()->en);
        $this->assertNotSame($channel->getVersion(), $result->getVersion());
    }

    public function testSetDescription()
    {
        $draft = $this->getDraft('set-description');
        $channel = $this->createProduct($draft);

        $description = $this->getTestRun() . '-new description';
        $request = ProductUpdateRequest::ofIdAndVersion($channel->getId(), $channel->getVersion())
            ->addAction(
                ProductSetDescriptionAction::ofDescription(
                    LocalizedString::ofLangAndText('en', $description)
                )
            )
        ;
        $response = $request->executeWithClient($this->getClient());
        $result = $request->mapResponse($response);
        $this->productDeleteRequest->setVersion($result->getVersion());

        $this->assertInstanceOf('\Commercetools\Core\Model\Product\Product', $result);
        $this->assertSame($description, $result->getMasterData()->getStaged()->getDescription()->en);
        $this->assertNotSame($channel->getVersion(), $result->getVersion());
    }

    public function testChangeSlug()
    {
        $draft = $this->getDraft('change-slug');
        $channel = $this->createProduct($draft);

        $slug = $this->getTestRun() . '-new-slug';
        $request = ProductUpdateRequest::ofIdAndVersion($channel->getId(), $channel->getVersion())
            ->addAction(
                ProductChangeSlugAction::ofSlug(
                    LocalizedString::ofLangAndText('en', $slug)
                )
            )
        ;
        $response = $request->executeWithClient($this->getClient());
        $result = $request->mapResponse($response);
        $this->productDeleteRequest->setVersion($result->getVersion());

        $this->assertInstanceOf('\Commercetools\Core\Model\Product\Product', $result);
        $this->assertNotSame($slug, $result->getMasterData()->getCurrent()->getSlug()->en);
        $this->assertSame($slug, $result->getMasterData()->getStaged()->getSlug()->en);
        $this->assertNotSame($channel->getVersion(), $result->getVersion());
    }

    public function testVariants()
    {
        $draft = $this->getDraft('add-variant');
        $product = $this->createProduct($draft);

        $sku = $this->getTestRun() . '-sku';
        $request = ProductUpdateRequest::ofIdAndVersion($product->getId(), $product->getVersion())
            ->addAction(
                ProductAddVariantAction::of()->setSku($sku)
            )
        ;
        $response = $request->executeWithClient($this->getClient());
        $result = $request->mapResponse($response);
        $this->productDeleteRequest->setVersion($result->getVersion());

        $this->assertInstanceOf('\Commercetools\Core\Model\Product\Product', $result);
        $this->assertEmpty($result->getMasterData()->getCurrent()->getVariants());
        $this->assertSame($sku, $result->getMasterData()->getStaged()->getVariants()->current()->getSku());
        $this->assertNotSame($product->getVersion(), $result->getVersion());
        $product = $result;

        $request = ProductUpdateRequest::ofIdAndVersion($product->getId(), $product->getVersion())
            ->addAction(
                ProductRemoveVariantAction::ofVariantId(
                    $result->getMasterData()->getStaged()->getVariants()->current()->getId()
                )
            )
        ;
        $response = $request->executeWithClient($this->getClient());
        $result = $request->mapResponse($response);
        $this->productDeleteRequest->setVersion($result->getVersion());

        $this->assertInstanceOf('\Commercetools\Core\Model\Product\Product', $result);
        $this->assertEmpty($result->getMasterData()->getCurrent()->getVariants());
        $this->assertEmpty($result->getMasterData()->getStaged()->getVariants());
        $this->assertNotSame($product->getVersion(), $result->getVersion());
    }

    public function testPrices()
    {
        $draft = $this->getDraft('add-price');
        $product = $this->createProduct($draft);

        $price = PriceDraft::ofMoney(Money::ofCurrencyAndAmount('EUR', 100));
        $request = ProductUpdateRequest::ofIdAndVersion($product->getId(), $product->getVersion())
            ->addAction(
                ProductAddPriceAction::ofVariantIdAndPrice(
                    $product->getMasterData()->getCurrent()->getMasterVariant()->getId(),
                    $price
                )
            )
        ;
        $response = $request->executeWithClient($this->getClient());
        $result = $request->mapResponse($response);
        $this->productDeleteRequest->setVersion($result->getVersion());

        $variant = $result->getMasterData()->getStaged()->getMasterVariant();
        $this->assertInstanceOf('\Commercetools\Core\Model\Product\Product', $result);
        $this->assertEmpty($result->getMasterData()->getCurrent()->getMasterVariant()->getPrices());
        $this->assertSame(
            $price->getValue()->getCentAmount(),
            $variant->getPrices()->current()->getValue()->getCentAmount()
        );
        $this->assertNotSame($product->getVersion(), $result->getVersion());
        $product = $result;

        $price = PriceDraft::ofMoney(Money::ofCurrencyAndAmount('EUR', 200));
        $request = ProductUpdateRequest::ofIdAndVersion($product->getId(), $product->getVersion())
            ->addAction(
                ProductChangePriceAction::ofPriceIdAndPrice(
                    $variant->getPrices()->current()->getId(),
                    $price
                )
            )
        ;
        $response = $request->executeWithClient($this->getClient());
        $result = $request->mapResponse($response);
        $this->productDeleteRequest->setVersion($result->getVersion());

        $variant = $result->getMasterData()->getStaged()->getMasterVariant();
        $this->assertInstanceOf('\Commercetools\Core\Model\Product\Product', $result);
        $this->assertEmpty($result->getMasterData()->getCurrent()->getMasterVariant()->getPrices());
        $this->assertSame(
            $price->getValue()->getCentAmount(),
            $variant->getPrices()->current()->getValue()->getCentAmount()
        );
        $this->assertNotSame($product->getVersion(), $result->getVersion());
        $product = $result;

        $price = PriceDraft::ofMoney(Money::ofCurrencyAndAmount('EUR', 300));
        $prices = PriceDraftCollection::of()->add($price);
        $request = ProductUpdateRequest::ofIdAndVersion($product->getId(), $product->getVersion())
            ->addAction(
                ProductSetPricesAction::ofVariantIdAndPrices(
                    $variant->getId(),
                    $prices
                )
            )
        ;
        $response = $request->executeWithClient($this->getClient());
        $result = $request->mapResponse($response);
        $this->productDeleteRequest->setVersion($result->getVersion());

        $variant = $result->getMasterData()->getStaged()->getMasterVariant();
        $this->assertInstanceOf('\Commercetools\Core\Model\Product\Product', $result);
        $this->assertEmpty($result->getMasterData()->getCurrent()->getMasterVariant()->getPrices());
        $this->assertSame(
            $price->getValue()->getCentAmount(),
            $variant->getPrices()->current()->getValue()->getCentAmount()
        );
        $this->assertNotSame($product->getVersion(), $result->getVersion());
        $product = $result;

        $request = ProductUpdateRequest::ofIdAndVersion($product->getId(), $product->getVersion())
            ->addAction(
                ProductRemovePriceAction::ofPriceId(
                    $variant->getPrices()->current()->getId()
                )
            )
        ;
        $response = $request->executeWithClient($this->getClient());
        $result = $request->mapResponse($response);
        $this->productDeleteRequest->setVersion($result->getVersion());

        $this->assertInstanceOf('\Commercetools\Core\Model\Product\Product', $result);
        $this->assertEmpty($result->getMasterData()->getCurrent()->getMasterVariant()->getPrices());
        $this->assertEmpty($result->getMasterData()->getStaged()->getMasterVariant()->getPrices());
        $this->assertNotSame($product->getVersion(), $result->getVersion());
    }

    public function testAttribute()
    {
        $draft = $this->getDraft('attribute');
        $product = $this->createProduct($draft);

        $attributeValue = $this->getTestRun() . '-new value';
        $request = ProductUpdateRequest::ofIdAndVersion($product->getId(), $product->getVersion())
            ->addAction(
                ProductSetAttributeAction::ofVariantIdAndName(
                    $product->getMasterData()->getCurrent()->getMasterVariant()->getId(),
                    'testField'
                )->setValue($attributeValue)
            )
        ;
        $response = $request->executeWithClient($this->getClient());
        $result = $request->mapResponse($response);
        $this->productDeleteRequest->setVersion($result->getVersion());

        $variant = $result->getMasterData()->getStaged()->getMasterVariant();
        $variant->getAttributes()->setAttributeDefinitions($this->getProductType()->getAttributes());
        $this->assertInstanceOf('\Commercetools\Core\Model\Product\Product', $result);
        $this->assertEmpty($result->getMasterData()->getCurrent()->getMasterVariant()->getPrices());
        $this->assertSame(
            $attributeValue,
            $variant->getAttributes()->getByName('testField')->getValue()
        );
        $this->assertNotSame($product->getVersion(), $result->getVersion());
        $product = $result;

        $attributeValue = $this->getTestRun() . '-new all value';
        $request = ProductUpdateRequest::ofIdAndVersion($product->getId(), $product->getVersion())
            ->addAction(
                ProductSetAttributeInAllVariantsAction::ofName(
                    'testField'
                )->setValue($attributeValue)
            )
        ;
        $response = $request->executeWithClient($this->getClient());
        $result = $request->mapResponse($response);
        $this->productDeleteRequest->setVersion($result->getVersion());

        $variant = $result->getMasterData()->getStaged()->getMasterVariant();
        $variant->getAttributes()->setAttributeDefinitions($this->getProductType()->getAttributes());

        $this->assertInstanceOf('\Commercetools\Core\Model\Product\Product', $result);
        $this->assertEmpty($result->getMasterData()->getCurrent()->getMasterVariant()->getPrices());
        $this->assertSame(
            $attributeValue,
            $variant->getAttributes()->getByName('testField')->getValue()
        );
        $this->assertNotSame($product->getVersion(), $result->getVersion());
    }

    public function testCategory()
    {
        $draft = $this->getDraft('category');
        $product = $this->createProduct($draft);

        $category = $this->getCategory();
        $request = ProductUpdateRequest::ofIdAndVersion($product->getId(), $product->getVersion())
            ->addAction(ProductAddToCategoryAction::ofCategory($category->getReference()))
        ;
        $response = $request->executeWithClient($this->getClient());
        $result = $request->mapResponse($response);
        $this->productDeleteRequest->setVersion($result->getVersion());

        $this->assertInstanceOf('\Commercetools\Core\Model\Product\Product', $result);
        $this->assertEmpty($product->getMasterData()->getCurrent()->getCategories());
        $this->assertSame(
            $category->getId(),
            $result->getMasterData()->getStaged()->getCategories()->current()->getId()
        );
        $this->assertNotSame($product->getVersion(), $result->getVersion());
        $product = $result;

        $orderHint = '0.9' . trim((string)mt_rand(1, 1000), '0');
        $request = ProductUpdateRequest::ofIdAndVersion($product->getId(), $product->getVersion())
            ->addAction(ProductSetCategoryOrderHintAction::ofCategoryId($category->getId())->setOrderHint($orderHint))
        ;
        $response = $request->executeWithClient($this->getClient());
        $result = $request->mapResponse($response);
        $this->productDeleteRequest->setVersion($result->getVersion());

        $this->assertInstanceOf('\Commercetools\Core\Model\Product\Product', $result);
        $this->assertEmpty($product->getMasterData()->getCurrent()->getCategoryOrderHints());
        $this->assertSame(
            $orderHint,
            $result->getMasterData()->getStaged()->getCategoryOrderHints()[$category->getId()]
        );
        $this->assertNotSame($product->getVersion(), $result->getVersion());
        $product = $result;

        $category = $this->getCategory();
        $request = ProductUpdateRequest::ofIdAndVersion($product->getId(), $product->getVersion())
            ->addAction(ProductRemoveFromCategoryAction::ofCategory($category->getReference()))
        ;
        $response = $request->executeWithClient($this->getClient());
        $result = $request->mapResponse($response);
        $this->productDeleteRequest->setVersion($result->getVersion());

        $this->assertInstanceOf('\Commercetools\Core\Model\Product\Product', $result);
        $this->assertEmpty($result->getMasterData()->getCurrent()->getCategories());
        $this->assertEmpty($result->getMasterData()->getStaged()->getCategories());
        $this->assertNotSame($product->getVersion(), $result->getVersion());
    }

    public function testTaxCategory()
    {
        $draft = $this->getDraft('tax-category');
        $product = $this->createProduct($draft);

        $taxCategory = $this->getTaxCategory();
        $request = ProductUpdateRequest::ofIdAndVersion($product->getId(), $product->getVersion())
            ->addAction(ProductSetTaxCategoryAction::of()->setTaxCategory($taxCategory->getReference()))
        ;
        $response = $request->executeWithClient($this->getClient());
        $result = $request->mapResponse($response);
        $this->productDeleteRequest->setVersion($result->getVersion());

        $this->assertInstanceOf('\Commercetools\Core\Model\Product\Product', $result);
        $this->assertSame(
            $taxCategory->getId(),
            $result->getTaxCategory()->getId()
        );
        $this->assertNotSame($product->getVersion(), $result->getVersion());
    }

    public function testSku()
    {
        $draft = $this->getDraft('sku');
        $product = $this->createProduct($draft);

        $sku = $this->getTestRun() . 'sku';
        $request = ProductUpdateRequest::ofIdAndVersion($product->getId(), $product->getVersion())
            ->addAction(
                ProductSetSKUAction::ofVariantId(
                    $product->getMasterData()->getCurrent()->getMasterVariant()->getId()
                )->setSku($sku)
            )
        ;
        $response = $request->executeWithClient($this->getClient());
        $result = $request->mapResponse($response);
        $this->productDeleteRequest->setVersion($result->getVersion());

        $this->assertInstanceOf('\Commercetools\Core\Model\Product\Product', $result);
        $this->assertSame(
            $sku,
            $result->getMasterData()->getStaged()->getMasterVariant()->getSku()
        );
        $this->markTestSkipped('SKU not yet stageable');
        $this->assertNull($result->getMasterData()->getCurrent()->getMasterVariant()->getSku());
        $this->assertNotSame($product->getVersion(), $result->getVersion());
    }

    public function testSearchKeyword()
    {
        $draft = $this->getDraft('keyword');
        $product = $this->createProduct($draft);

        $keyword = $this->getTestRun() . '-keyword';
        $request = ProductUpdateRequest::ofIdAndVersion($product->getId(), $product->getVersion())
            ->addAction(
                ProductSetSearchKeywordsAction::ofKeywords(
                    LocalizedSearchKeywords::of()->setAt(
                        'en',
                        SearchKeywords::of()->add(SearchKeyword::of()->setText($keyword))
                    )
                )
            )
        ;
        $response = $request->executeWithClient($this->getClient());
        $result = $request->mapResponse($response);
        $this->productDeleteRequest->setVersion($result->getVersion());

        $this->assertInstanceOf('\Commercetools\Core\Model\Product\Product', $result);
        $this->assertEmpty($result->getMasterData()->getCurrent()->getSearchKeywords());
        $this->assertSame(
            $keyword,
            $result->getMasterData()->getStaged()->getSearchKeywords()->en->current()->getText()
        );
        $this->assertNotSame($product->getVersion(), $result->getVersion());
    }

    public function testMetaTitle()
    {
        $draft = $this->getDraft('meta-title');
        $product = $this->createProduct($draft);

        $metaTitle = $this->getTestRun() . '-meta-title';
        $request = ProductUpdateRequest::ofIdAndVersion($product->getId(), $product->getVersion())
            ->addAction(
                ProductSetMetaTitleAction::of()->setMetaTitle(
                    LocalizedString::ofLangAndText('en', $metaTitle)
                )
            )
        ;
        $response = $request->executeWithClient($this->getClient());
        $result = $request->mapResponse($response);
        $this->productDeleteRequest->setVersion($result->getVersion());

        $this->assertInstanceOf('\Commercetools\Core\Model\Product\Product', $result);
        $this->assertNull($result->getMasterData()->getCurrent()->getMetaTitle());
        $this->assertSame(
            $metaTitle,
            $result->getMasterData()->getStaged()->getMetaTitle()->en
        );
        $this->assertNotSame($product->getVersion(), $result->getVersion());
    }

    public function testMetaDescription()
    {
        $draft = $this->getDraft('meta-description');
        $product = $this->createProduct($draft);

        $metaDescription = $this->getTestRun() . '-meta-description';
        $request = ProductUpdateRequest::ofIdAndVersion($product->getId(), $product->getVersion())
            ->addAction(
                ProductSetMetaDescriptionAction::of()->setMetaDescription(
                    LocalizedString::ofLangAndText('en', $metaDescription)
                )
            )
        ;
        $response = $request->executeWithClient($this->getClient());
        $result = $request->mapResponse($response);
        $this->productDeleteRequest->setVersion($result->getVersion());

        $this->assertInstanceOf('\Commercetools\Core\Model\Product\Product', $result);
        $this->assertNull($result->getMasterData()->getCurrent()->getMetaDescription());
        $this->assertSame(
            $metaDescription,
            $result->getMasterData()->getStaged()->getMetaDescription()->en
        );
        $this->assertNotSame($product->getVersion(), $result->getVersion());
    }

    public function testMetaKeywords()
    {
        $draft = $this->getDraft('meta-keywords');
        $product = $this->createProduct($draft);

        $metaKeywords = $this->getTestRun() . '-meta-keywords';
        $request = ProductUpdateRequest::ofIdAndVersion($product->getId(), $product->getVersion())
            ->addAction(
                ProductSetMetaKeywordsAction::of()->setMetaKeywords(
                    LocalizedString::ofLangAndText('en', $metaKeywords)
                )
            )
        ;
        $response = $request->executeWithClient($this->getClient());
        $result = $request->mapResponse($response);
        $this->productDeleteRequest->setVersion($result->getVersion());

        $this->assertInstanceOf('\Commercetools\Core\Model\Product\Product', $result);
        $this->assertNull($result->getMasterData()->getCurrent()->getMetaDescription());
        $this->assertSame(
            $metaKeywords,
            $result->getMasterData()->getStaged()->getMetaKeywords()->en
        );
        $this->assertNotSame($product->getVersion(), $result->getVersion());
    }

    public function testRevertStagedChanges()
    {
        $draft = $this->getDraft('revert');
        $product = $this->createProduct($draft);

        $metaKeywords = $this->getTestRun() . '-meta-keywords';
        $request = ProductUpdateRequest::ofIdAndVersion($product->getId(), $product->getVersion())
            ->addAction(
                ProductSetMetaKeywordsAction::of()->setMetaKeywords(
                    LocalizedString::ofLangAndText('en', $metaKeywords)
                )
            )
        ;
        $response = $request->executeWithClient($this->getClient());
        $result = $request->mapResponse($response);
        $this->productDeleteRequest->setVersion($result->getVersion());

        $this->assertInstanceOf('\Commercetools\Core\Model\Product\Product', $result);
        $this->assertNull($result->getMasterData()->getCurrent()->getMetaDescription());
        $this->assertSame(
            $metaKeywords,
            $result->getMasterData()->getStaged()->getMetaKeywords()->en
        );
        $this->assertNotSame($product->getVersion(), $result->getVersion());
        $product = $result;

        $request = ProductUpdateRequest::ofIdAndVersion($product->getId(), $product->getVersion())
            ->addAction(ProductRevertStagedChangesAction::of())
        ;
        $response = $request->executeWithClient($this->getClient());
        $result = $request->mapResponse($response);
        $this->productDeleteRequest->setVersion($result->getVersion());

        $this->assertInstanceOf('\Commercetools\Core\Model\Product\Product', $result);
        $this->assertNull($result->getMasterData()->getCurrent()->getMetaDescription());
        $this->assertNull($result->getMasterData()->getStaged()->getMetaDescription());
        $this->assertNotSame($product->getVersion(), $result->getVersion());
    }

    public function testPublish()
    {
        $draft = $this->getDraft('publish');
        $product = $this->createProduct($draft);

        $metaKeywords = $this->getTestRun() . '-meta-keywords';
        $request = ProductUpdateRequest::ofIdAndVersion($product->getId(), $product->getVersion())
            ->addAction(
                ProductSetMetaKeywordsAction::of()->setMetaKeywords(
                    LocalizedString::ofLangAndText('en', $metaKeywords)
                )
            )
            ->addAction(ProductPublishAction::of())

        ;
        $response = $request->executeWithClient($this->getClient());
        $result = $request->mapResponse($response);
        $this->productDeleteRequest->setVersion($result->getVersion());

        $this->assertInstanceOf('\Commercetools\Core\Model\Product\Product', $result);
        $this->assertSame(
            $metaKeywords,
            $result->getMasterData()->getCurrent()->getMetaKeywords()->en
        );
        $this->assertSame(
            $metaKeywords,
            $result->getMasterData()->getStaged()->getMetaKeywords()->en
        );
        $this->assertNotSame($product->getVersion(), $result->getVersion());
        $product = $result;

        $request = ProductUpdateRequest::ofIdAndVersion($product->getId(), $product->getVersion())
            ->addAction(ProductUnpublishAction::of())
        ;
        $response = $request->executeWithClient($this->getClient());
        $result = $request->mapResponse($response);
        $this->productDeleteRequest->setVersion($result->getVersion());

        $this->assertInstanceOf('\Commercetools\Core\Model\Product\Product', $result);
        $this->assertFalse($result->getMasterData()->getPublished());
        $this->assertNotSame($product->getVersion(), $result->getVersion());
    }

    public function testTransitionStates()
    {
        $draft = $this->getDraft('publish');
        $product = $this->createProduct($draft);

        /**
         * @var State $state1
         * @var State $state2
         */
        list($state1, $state2) = $this->createStates('ProductState');
        $request = ProductUpdateRequest::ofIdAndVersion($product->getId(), $product->getVersion())
            ->addAction(
                ProductTransitionStateAction::ofState($state1->getReference())
            )
        ;
        $response = $request->executeWithClient($this->getClient());
        $result = $request->mapResponse($response);
        $this->productDeleteRequest->setVersion($result->getVersion());

        $this->assertInstanceOf('\Commercetools\Core\Model\Product\Product', $result);
        $this->assertSame(
            $state1->getId(),
            $result->getState()->getId()
        );
        $this->assertNotSame($product->getVersion(), $result->getVersion());
        $product = $result;

        $request = ProductUpdateRequest::ofIdAndVersion($product->getId(), $product->getVersion())
            ->addAction(
                ProductTransitionStateAction::ofState($state2->getReference())
            )
        ;
        $response = $request->executeWithClient($this->getClient());
        $result = $request->mapResponse($response);
        $this->productDeleteRequest->setVersion($result->getVersion());

        $this->assertInstanceOf('\Commercetools\Core\Model\Product\Product', $result);
        $this->assertSame(
            $state2->getId(),
            $result->getState()->getId()
        );
        $this->assertNotSame($product->getVersion(), $result->getVersion());
    }

    public function testPriceCustomType()
    {
        $draft = $this->getDraft('price-custom-type');
        $product = $this->createProduct($draft);

        $request = ProductUpdateRequest::ofIdAndVersion($product->getId(), $product->getVersion())
            ->addAction(
                ProductAddPriceAction::ofVariantIdAndPrice(
                    $product->getMasterData()->getCurrent()->getMasterVariant()->getId(),
                    PriceDraft::ofMoney(Money::ofCurrencyAndAmount('EUR', 100))
                )
            )
        ;
        $response = $request->executeWithClient($this->getClient());
        $product = $request->mapResponse($response);
        $this->productDeleteRequest->setVersion($product->getVersion());


        $type = $this->getType('mytype', 'product-price');
        $price = $product->getMasterData()->getStaged()->getMasterVariant()->getPrices()->current();
        $request = ProductUpdateRequest::ofIdAndVersion($product->getId(), $product->getVersion())
            ->addAction(
                ProductSetPriceCustomTypeAction::ofType($type->getReference())
                    ->setPriceId($price->getId())
            )
        ;
        $response = $request->executeWithClient($this->getClient());
        $result = $request->mapResponse($response);
        $this->productDeleteRequest->setVersion($result->getVersion());

        $this->assertInstanceOf('\Commercetools\Core\Model\Product\Product', $result);
        $this->assertEmpty($result->getMasterData()->getCurrent()->getMasterVariant()->getPrices());
        $variant = $result->getMasterData()->getStaged()->getMasterVariant();
        $this->assertSame(
            $type->getId(),
            $variant->getPrices()->current()->getCustom()->getType()->getId()
        );
        $this->assertNotSame($product->getVersion(), $result->getVersion());
    }

    public function testPriceCustomField()
    {
        $draft = $this->getDraft('price-custom-type');
        $product = $this->createProduct($draft);

        $request = ProductUpdateRequest::ofIdAndVersion($product->getId(), $product->getVersion())
            ->addAction(
                ProductAddPriceAction::ofVariantIdAndPrice(
                    $product->getMasterData()->getCurrent()->getMasterVariant()->getId(),
                    PriceDraft::ofMoney(Money::ofCurrencyAndAmount('EUR', 100))
                )
            )
        ;
        $response = $request->executeWithClient($this->getClient());
        $product = $request->mapResponse($response);
        $this->productDeleteRequest->setVersion($product->getVersion());

        $type = $this->getType('mytype', 'product-price');
        $price = $product->getMasterData()->getStaged()->getMasterVariant()->getPrices()->current();
        $request = ProductUpdateRequest::ofIdAndVersion($product->getId(), $product->getVersion())
            ->addAction(
                ProductSetPriceCustomTypeAction::ofType($type->getReference())
                    ->setPriceId($price->getId())
            )
        ;
        $response = $request->executeWithClient($this->getClient());
        $product = $request->mapResponse($response);
        $this->productDeleteRequest->setVersion($product->getVersion());

        $price = $product->getMasterData()->getStaged()->getMasterVariant()->getPrices()->current();
        $fieldValue = $this->getTestRun() . '-value';
        $request = ProductUpdateRequest::ofIdAndVersion($product->getId(), $product->getVersion())
            ->addAction(
                ProductSetPriceCustomFieldAction::ofName('testField')
                    ->setPriceId($price->getId())
                    ->setValue($fieldValue)
            )
        ;
        $response = $request->executeWithClient($this->getClient());
        $result = $request->mapResponse($response);
        $this->productDeleteRequest->setVersion($result->getVersion());


        $this->assertInstanceOf('\Commercetools\Core\Model\Product\Product', $result);
        $this->assertEmpty($result->getMasterData()->getCurrent()->getMasterVariant()->getPrices());
        $variant = $result->getMasterData()->getStaged()->getMasterVariant();
        $this->assertSame(
            $fieldValue,
            $variant->getPrices()->current()->getCustom()->getFields()->getTestField()
        );
        $this->assertNotSame($product->getVersion(), $result->getVersion());
    }

    /**
     * @return ProductDraft
     */
    protected function getDraft($name)
    {
        $draft = ProductDraft::ofTypeNameAndSlug(
            $this->getProductType()->getReference(),
            LocalizedString::ofLangAndText('en', 'test-' . $this->getTestRun() . '-' . $name),
            LocalizedString::ofLangAndText('en', 'test-' . $this->getTestRun() . '-' . $name)
        );

        return $draft;
    }

    protected function createProduct(ProductDraft $draft)
    {
        $request = ProductCreateRequest::ofDraft($draft);
        $response = $request->executeWithClient($this->getClient());
        $product = $request->mapResponse($response);

        $this->cleanupRequests[] = $this->productDeleteRequest = ProductDeleteRequest::ofIdAndVersion(
            $product->getId(),
            $product->getVersion()
        );

        return $product;
    }
}
