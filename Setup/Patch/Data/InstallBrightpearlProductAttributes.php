<?php

namespace Bsitc\Brightpearl\Setup\Patch\Data;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Catalog\Model\Product\Attribute\Repository;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Setup\CategorySetup;

class InstallBrightpearlProductAttributes implements DataPatchInterface, PatchRevertableInterface
{

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    /**
     * Repository
     */
    protected $attributeRepository;
    
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        Repository $attributeRepository,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->attributeRepository = $attributeRepository;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        foreach ($this->getAttributeData() as $attributeCode => $attributeData) {
            try {
                $attribute = $this->attributeRepository->get($attributeCode);
                $attributeData = array_merge($attribute->getData(), $attributeData);
                $attribute->setData($attributeData);
                $this->attributeRepository->save($attribute);
            } catch (NoSuchEntityException $e) {
                $eavSetup->addAttribute(
                    $attributeData['entity_type_id'],
                    $attributeCode,
                    $attributeData
                );
            }

            // For some reason the Page Builder flag will on set if you
            // use this update attribute method.
            if (array_key_exists("is_pagebuilder_enabled", $attributeData)) {
                if ($attributeData['is_pagebuilder_enabled']) {
                    $eavSetup->updateAttribute(
                        $attributeData['entity_type_id'],
                        $attributeCode,
                        [
                            'is_pagebuilder_enabled' => 1,
                            'is_html_allowed_on_front' => 1,
                            'is_wysiwyg_enabled' => 1
                        ]
                    );
                }
            }
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        foreach ($this->getAttributeData() as $attributeCode => $attributeData) {
            $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);
        }
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
        
        ];
    }


    /**
     * @return array
     */
    public function getAttributeData()
    {
        return [
            'bp_brand' => [
                'entity_type_id' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
                'group' => 'Brightpearl',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Bp Brand',
                'input' => 'select',
                'note' => 'Brand',
                'class' => '',
                'source' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '0',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => false,
                'unique' => false,
                'option' => [
                    'values' => [],
                ]
            ],
            'bp_collection' => [
                'entity_type_id' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
                'group' => 'Brightpearl',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Bp Collection',
                'input' => 'select',
                'note' => 'Collection',
                'class' => '',
                'source' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '0',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => false,
                'unique' => false,
                'option' => [
                    'values' => [],
                ]
            ],
            'colour' => [
                'entity_type_id' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
                'group' => 'Brightpearl',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Bp Colour',
                'input' => 'select',
                'note' => 'Collection',
                'class' => '',
                'source' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '0',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => false,
                'unique' => false,
                'option' => [
                    'values' => [],
                ]
            ],
            'size' => [
                'entity_type_id' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
                'group' => 'Brightpearl',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Size',
                'input' => 'select',
                'note' => 'Collection',
                'class' => '',
                'source' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '0',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => false,
                'unique' => false,
                'option' => [
                    'values' => [],
                ]
            ],
            'bp_condition' => [
                'entity_type_id' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
                'type' => 'text',
                'group' => 'Brightpearl',
                'backend' => '',
                'frontend' => '',
                'label' => 'Bp Condition',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => ''
            ],
            'bp_featured' => [
                'entity_type_id' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
                'group' => 'Brightpearl',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Bp Featured',
                'input' => 'boolean',
                'class' => '',
                'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '0',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false,
            ],
            'bp_upc' => [
                'entity_type_id' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
                'type' => 'text',
                'group' => 'Brightpearl',
                'backend' => '',
                'frontend' => '',
                'label' => 'Bp UPC',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => ''
            ],
            'bp_isbn' => [
                'entity_type_id' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
                'type' => 'text',
                'group' => 'Brightpearl',
                'backend' => '',
                'frontend' => '',
                'label' => 'Bp ISBN',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => ''
            ],
            'bp_ean' => [
                'entity_type_id' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
                'type' => 'text',
                'group' => 'Brightpearl',
                'backend' => '',
                'frontend' => '',
                'label' => 'Bp EAN',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => ''
            ],
            'bp_mpn' => [
                'entity_type_id' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
                'type' => 'text',
                'group' => 'Brightpearl',
                'backend' => '',
                'frontend' => '',
                'label' => 'Bp MPN',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => ''
            ],
            'bp_barcode' => [
                'entity_type_id' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
                'type' => 'text',
                'group' => 'Brightpearl',
                'backend' => '',
                'frontend' => '',
                'label' => 'Bp Barcode',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => ''
            ],
            'bp_nominalcodestock' => [
                'entity_type_id' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
                'type' => 'text',
                'group' => 'Brightpearl',
                'backend' => '',
                'frontend' => '',
                'label' => 'Bp NominalCodeStock',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => ''
            ],
            'bp_nominalcodepurchases' => [
                'entity_type_id' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
                'type' => 'text',
                'group' => 'Brightpearl',
                'backend' => '',
                'frontend' => '',
                'label' => 'Bp NominalCodePurchases',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => ''
            ],
            'bp_nominalcodesales' => [
                'entity_type_id' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
                'type' => 'text',
                'group' => 'Brightpearl',
                'backend' => '',
                'frontend' => '',
                'label' => 'Bp NominalCodeSales',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => ''
            ],
            'bp_seasons' => [
                'entity_type_id' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
                'type' => 'text',
                'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'frontend' => '',
                'group' => 'Brightpearl',
                'label' => 'BP Seasons',
                'input' => 'multiselect',
                'class' => '',
                'option' => ['values' => [],],
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'wysiwyg_enabled' => true,
                'unique' => false,
                'apply_to' => ''
            ],
            'season' => [
                'entity_type_id' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
                'type' => 'text',
                'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'frontend' => '',
                'group' => 'Brightpearl',
                'label' => 'Season',
                'input' => 'multiselect',
                'class' => '',
                'option' => ['values' => [],],
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'wysiwyg_enabled' => true,
                'unique' => false,
                'apply_to' => ''
            ],
            'bp_product_group_id' => [
                'entity_type_id' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
                'type' => 'text',
                'group' => 'Brightpearl',
                'backend' => '',
                'frontend' => '',
                'label' => 'Bp Product Group Id',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => ''
            ],
            'bp_category'=> [
                'entity_type_id' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
                'type' => 'text',
                'group' => 'Brightpearl',
                'label' => 'Bp Categories',
                'input' => 'textarea',
                'source' => '',
                'frontend' => '',
                'required' => false,
                'backend' => '',
                'sort_order' => '30',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'default' => null,
                'visible' => true,
                'user_defined' => true,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'unique' => false,
                'apply_to' => 'simple',
                'used_in_product_listing' => false,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'option' => ['values' => [""]]
            ]
            
        ];
    }
}
