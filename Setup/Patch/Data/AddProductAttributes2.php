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

class AddProductAttributes2 implements DataPatchInterface, PatchRevertableInterface
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
            'leadtime' => [
                'entity_type_id' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
                'group' => 'Product Details',
                'type' => 'text',
                'backend' => '',
                'frontend' => '',
                'label' => 'Delivery Lead Time',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => 'simple,virtual',
            ],
			'leadtimemsg' => [
                'entity_type_id' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
                'group' => 'Product Details',
                'type' => 'text',
                'backend' => '',
                'frontend' => '',
                'label' => 'Delivery Lead Time Message',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => 'simple,virtual',
            ]
        ];
    }
}
