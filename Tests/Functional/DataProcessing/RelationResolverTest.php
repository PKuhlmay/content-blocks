<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace TYPO3\CMS\ContentBlocks\Tests\Functional\DataProcessing;

use TYPO3\CMS\ContentBlocks\DataProcessing\RelationResolver;
use TYPO3\CMS\ContentBlocks\Loader\LoaderFactory;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

final class RelationResolverTest extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = [
        'content_blocks',
    ];

    protected array $testExtensionsToLoad = [
        'typo3/sysext/content_blocks/Tests/Fixtures/Extensions/foo',
    ];

    protected array $pathsToProvideInTestInstance = [
        'typo3/sysext/content_blocks/Tests/Fixtures/TestFolder/' => 'fileadmin/',
    ];

    /**
     * @test
     */
    public function canResolveFileReferences(): void
    {
        $this->importCSVDataSet('typo3/sysext/content_blocks/Tests/Fixtures/DataSet/file_reference.csv');

        $tableDefinitionCollection = $this->get(LoaderFactory::class)->load();
        $tableDefinition = $tableDefinitionCollection->getTable('tt_content');
        $elementDefinition = $tableDefinition->getTypeDefinitionCollection()->getType('foo/bar');
        $fieldDefinition = $tableDefinition->getTcaColumnsDefinition()->getField('image');
        $dummyRecord = [
            'uid' => 1,
            'image' => 1,
        ];

        $relationResolver = new RelationResolver($tableDefinitionCollection, new FlexFormService());
        $result = $relationResolver->processField($fieldDefinition, $elementDefinition, $dummyRecord, 'tt_content');

        self::assertCount(1, $result);
        self::assertInstanceOf(FileReference::class, $result[0]);
    }

    /**
     * @test
     */
    public function canResolveFilesFromFolder(): void
    {
        $this->importCSVDataSet('typo3/sysext/content_blocks/Tests/Fixtures/DataSet/folder_files.csv');

        $tableDefinitionCollection = $this->get(LoaderFactory::class)->load();
        $tableDefinition = $tableDefinitionCollection->getTable('tt_content');
        $elementDefinition = $tableDefinition->getTypeDefinitionCollection()->getType('foo/bar');
        $fieldDefinition = $tableDefinition->getTcaColumnsDefinition()->getField('foo_bar_folder');
        $dummyRecord = [
            'uid' => 1,
            'foo_bar_folder' => '1:/',
        ];

        $relationResolver = new RelationResolver($tableDefinitionCollection, new FlexFormService());
        $result = $relationResolver->processField($fieldDefinition, $elementDefinition, $dummyRecord, 'tt_content');

        self::assertCount(1, $result);
        self::assertInstanceOf(File::class, $result[0]);
    }

    /**
     * @test
     */
    public function canResolveFilesFromFolderRecursive(): void
    {
        $this->importCSVDataSet('typo3/sysext/content_blocks/Tests/Fixtures/DataSet/folder_files.csv');

        $tableDefinitionCollection = $this->get(LoaderFactory::class)->load();
        $tableDefinition = $tableDefinitionCollection->getTable('tt_content');
        $elementDefinition = $tableDefinition->getTypeDefinitionCollection()->getType('foo/bar');
        $fieldDefinition = $tableDefinition->getTcaColumnsDefinition()->getField('foo_bar_folder_recursive');
        $dummyRecord = [
            'uid' => 1,
            'foo_bar_folder_recursive' => '1:/',
        ];

        $relationResolver = new RelationResolver($tableDefinitionCollection, new FlexFormService());
        $result = $relationResolver->processField($fieldDefinition, $elementDefinition, $dummyRecord, 'tt_content');

        self::assertCount(2, $result);
        self::assertInstanceOf(File::class, $result[0]);
        self::assertInstanceOf(File::class, $result[1]);
    }

    /**
     * @test
     */
    public function canResolveCollections(): void
    {
        $this->importCSVDataSet('typo3/sysext/content_blocks/Tests/Fixtures/DataSet/collections.csv');

        $tableDefinitionCollection = $this->get(LoaderFactory::class)->load();
        $tableDefinition = $tableDefinitionCollection->getTable('tt_content');
        $elementDefinition = $tableDefinition->getTypeDefinitionCollection()->getType('foo/bar');
        $fieldDefinition = $tableDefinition->getTcaColumnsDefinition()->getField('foo_bar_collection');
        $dummyRecord = [
            'uid' => 1,
            'foo_bar_collection' => 2,
        ];

        $relationResolver = new RelationResolver($tableDefinitionCollection, new FlexFormService());
        $result = $relationResolver->processField($fieldDefinition, $elementDefinition, $dummyRecord, 'tt_content');

        self::assertCount(2, $result);
        self::assertSame('lorem foo bar', $result[0]['fieldA']);
        self::assertSame('lorem foo bar 2', $result[1]['fieldA']);
    }

    /**
     * @test
     */
    public function canResolveCollectionsRecursively(): void
    {
        $this->importCSVDataSet('typo3/sysext/content_blocks/Tests/Fixtures/DataSet/collections_recursive.csv');

        $tableDefinitionCollection = $this->get(LoaderFactory::class)->load();
        $tableDefinition = $tableDefinitionCollection->getTable('tt_content');
        $elementDefinition = $tableDefinition->getTypeDefinitionCollection()->getType('foo/bar');
        $fieldDefinition = $tableDefinition->getTcaColumnsDefinition()->getField('foo_bar_collection_recursive');
        $dummyRecord = [
            'uid' => 1,
            'foo_bar_collection_recursive' => 2,
        ];

        $relationResolver = new RelationResolver($tableDefinitionCollection, new FlexFormService());
        $result = $relationResolver->processField($fieldDefinition, $elementDefinition, $dummyRecord, 'tt_content');

        self::assertCount(2, $result);
        self::assertSame('lorem foo bar A', $result[0]['fieldA']);
        self::assertSame('lorem foo bar A2', $result[1]['fieldA']);
        self::assertCount(2, $result[0]['collection_inner']);
        self::assertSame('lorem foo bar B', $result[0]['collection_inner'][0]['fieldB']);
        self::assertSame('lorem foo bar B2', $result[0]['collection_inner'][1]['fieldB']);
    }

    /**
     * @test
     */
    public function canResolveCategoriesManyToMany(): void
    {
        $this->importCSVDataSet('typo3/sysext/content_blocks/Tests/Fixtures/DataSet/category_many_to_many.csv');

        $tableDefinitionCollection = $this->get(LoaderFactory::class)->load();
        $tableDefinition = $tableDefinitionCollection->getTable('tt_content');
        $elementDefinition = $tableDefinition->getTypeDefinitionCollection()->getType('foo/bar');
        $fieldDefinition = $tableDefinition->getTcaColumnsDefinition()->getField('foo_bar_categories_mm');
        $dummyRecord = [
            'uid' => 1,
            'foo_bar_categories_mm' => 2,
        ];

        $relationResolver = new RelationResolver($tableDefinitionCollection, new FlexFormService());
        $result = $relationResolver->processField($fieldDefinition, $elementDefinition, $dummyRecord, 'tt_content');

        self::assertCount(2, $result);
        self::assertSame('Category 1', $result[0]['title']);
        self::assertSame('Category 2', $result[1]['title']);
    }

    /**
     * @test
     */
    public function canResolveCategoriesOneToOne(): void
    {
        $this->importCSVDataSet('typo3/sysext/content_blocks/Tests/Fixtures/DataSet/category_one_to_one.csv');

        $tableDefinitionCollection = $this->get(LoaderFactory::class)->load();
        $tableDefinition = $tableDefinitionCollection->getTable('tt_content');
        $elementDefinition = $tableDefinition->getTypeDefinitionCollection()->getType('foo/bar');
        $fieldDefinition = $tableDefinition->getTcaColumnsDefinition()->getField('foo_bar_categories_11');
        $dummyRecord = [
            'uid' => 1,
            'foo_bar_categories_11' => 7,
        ];

        $relationResolver = new RelationResolver($tableDefinitionCollection, new FlexFormService());
        $result = $relationResolver->processField($fieldDefinition, $elementDefinition, $dummyRecord, 'tt_content');

        self::assertCount(1, $result);
        self::assertSame('Category 1', $result[0]['title']);
    }

    /**
     * @test
     */
    public function canResolveCategoriesOneToMany(): void
    {
        $this->importCSVDataSet('typo3/sysext/content_blocks/Tests/Fixtures/DataSet/category_one_to_many.csv');

        $tableDefinitionCollection = $this->get(LoaderFactory::class)->load();
        $tableDefinition = $tableDefinitionCollection->getTable('tt_content');
        $elementDefinition = $tableDefinition->getTypeDefinitionCollection()->getType('foo/bar');
        $fieldDefinition = $tableDefinition->getTcaColumnsDefinition()->getField('foo_bar_categories_1m');
        $dummyRecord = [
            'uid' => 1,
            'foo_bar_categories_1m' => '7,8',
        ];

        $relationResolver = new RelationResolver($tableDefinitionCollection, new FlexFormService());
        $result = $relationResolver->processField($fieldDefinition, $elementDefinition, $dummyRecord, 'tt_content');

        self::assertCount(2, $result);
        self::assertSame('Category 1', $result[0]['title']);
        self::assertSame('Category 2', $result[1]['title']);
    }

    /**
     * @test
     */
    public function canResolveDbReferences(): void
    {
        $this->importCSVDataSet('typo3/sysext/content_blocks/Tests/Fixtures/DataSet/db_reference.csv');

        $tableDefinitionCollection = $this->get(LoaderFactory::class)->load();
        $tableDefinition = $tableDefinitionCollection->getTable('tt_content');
        $elementDefinition = $tableDefinition->getTypeDefinitionCollection()->getType('foo/bar');
        $fieldDefinition = $tableDefinition->getTcaColumnsDefinition()->getField('foo_bar_pages_reference');
        $dummyRecord = [
            'uid' => 1,
            'foo_bar_pages_reference' => '1,2',
        ];

        $relationResolver = new RelationResolver($tableDefinitionCollection, new FlexFormService());
        $result = $relationResolver->processField($fieldDefinition, $elementDefinition, $dummyRecord, 'tt_content');

        self::assertCount(2, $result);
        self::assertSame('Page 1', $result[0]['title']);
        self::assertSame('Page 2', $result[1]['title']);
    }

    /**
     * @test
     */
    public function canResolveMultipleDbReferences(): void
    {
        $this->importCSVDataSet('typo3/sysext/content_blocks/Tests/Fixtures/DataSet/db_reference_multiple.csv');

        $tableDefinitionCollection = $this->get(LoaderFactory::class)->load();
        $tableDefinition = $tableDefinitionCollection->getTable('tt_content');
        $elementDefinition = $tableDefinition->getTypeDefinitionCollection()->getType('foo/bar');
        $fieldDefinition = $tableDefinition->getTcaColumnsDefinition()->getField('foo_bar_pages_content_reference');
        $dummyRecord = [
            'uid' => 1,
            'foo_bar_pages_content_reference' => 'pages_1,pages_2,tt_content_1,tt_content_2',
        ];

        $relationResolver = new RelationResolver($tableDefinitionCollection, new FlexFormService());
        $result = $relationResolver->processField($fieldDefinition, $elementDefinition, $dummyRecord, 'tt_content');

        self::assertCount(4, $result);
        self::assertSame('Page 1', $result[0]['title']);
        self::assertSame('Page 2', $result[1]['title']);
        self::assertSame('Content 1', $result[2]['header']);
        self::assertSame('Content 2', $result[3]['header']);
    }

    /**
     * @test
     */
    public function canResolveDbReferencesMM(): void
    {
        $this->importCSVDataSet('typo3/sysext/content_blocks/Tests/Fixtures/DataSet/db_reference_mm.csv');

        $tableDefinitionCollection = $this->get(LoaderFactory::class)->load();
        $tableDefinition = $tableDefinitionCollection->getTable('tt_content');
        $elementDefinition = $tableDefinition->getTypeDefinitionCollection()->getType('foo/bar');
        $fieldDefinition = $tableDefinition->getTcaColumnsDefinition()->getField('foo_bar_pages_mm');
        $dummyRecord = [
            'uid' => 1,
            'foo_bar_pages_mm' => 2,
        ];

        $relationResolver = new RelationResolver($tableDefinitionCollection, new FlexFormService());
        $result = $relationResolver->processField($fieldDefinition, $elementDefinition, $dummyRecord, 'tt_content');

        self::assertCount(2, $result);
        self::assertSame('Page 1', $result[0]['title']);
        self::assertSame('Page 2', $result[1]['title']);
    }

    /**
     * @test
     */
    public function selectCheckboxCommaListConvertedToArray(): void
    {
        $tableDefinitionCollection = $this->get(LoaderFactory::class)->load();
        $tableDefinition = $tableDefinitionCollection->getTable('tt_content');
        $elementDefinition = $tableDefinition->getTypeDefinitionCollection()->getType('foo/bar');
        $fieldDefinition = $tableDefinition->getTcaColumnsDefinition()->getField('foo_bar_select_checkbox');
        $dummyRecord = [
            'uid' => 1,
            'foo_bar_select_checkbox' => '1,2,3',
        ];

        $relationResolver = new RelationResolver($tableDefinitionCollection, new FlexFormService());
        $result = $relationResolver->processField($fieldDefinition, $elementDefinition, $dummyRecord, 'tt_content');

        self::assertSame(['1', '2', '3'], $result);
    }

    /**
     * @test
     */
    public function selectSingleBoxCommaListConvertedToArray(): void
    {
        $tableDefinitionCollection = $this->get(LoaderFactory::class)->load();
        $tableDefinition = $tableDefinitionCollection->getTable('tt_content');
        $elementDefinition = $tableDefinition->getTypeDefinitionCollection()->getType('foo/bar');
        $fieldDefinition = $tableDefinition->getTcaColumnsDefinition()->getField('foo_bar_select_single_box');
        $dummyRecord = [
            'uid' => 1,
            'foo_bar_select_single_box' => '1,2,3',
        ];

        $relationResolver = new RelationResolver($tableDefinitionCollection, new FlexFormService());
        $result = $relationResolver->processField($fieldDefinition, $elementDefinition, $dummyRecord, 'tt_content');

        self::assertSame(['1', '2', '3'], $result);
    }

    /**
     * @test
     */
    public function selectMultipleSideBySideCommaListConvertedToArray(): void
    {
        $tableDefinitionCollection = $this->get(LoaderFactory::class)->load();
        $tableDefinition = $tableDefinitionCollection->getTable('tt_content');
        $elementDefinition = $tableDefinition->getTypeDefinitionCollection()->getType('foo/bar');
        $fieldDefinition = $tableDefinition->getTcaColumnsDefinition()->getField('foo_bar_select_multiple');
        $dummyRecord = [
            'uid' => 1,
            'foo_bar_select_multiple' => '1,2,3',
        ];

        $relationResolver = new RelationResolver($tableDefinitionCollection, new FlexFormService());
        $result = $relationResolver->processField($fieldDefinition, $elementDefinition, $dummyRecord, 'tt_content');

        self::assertSame(['1', '2', '3'], $result);
    }

    /**
     * @test
     */
    public function canResolveSelectForeignTable(): void
    {
        $this->importCSVDataSet('typo3/sysext/content_blocks/Tests/Fixtures/DataSet/select_foreign.csv');
        $tableDefinitionCollection = $this->get(LoaderFactory::class)->load();
        $tableDefinition = $tableDefinitionCollection->getTable('tt_content');
        $elementDefinition = $tableDefinition->getTypeDefinitionCollection()->getType('foo/bar');
        $fieldDefinition = $tableDefinition->getTcaColumnsDefinition()->getField('foo_bar_select_foreign');
        $dummyRecord = [
            'uid' => 1,
            'foo_bar_select_foreign' => '1,2',
        ];

        $relationResolver = new RelationResolver($tableDefinitionCollection, new FlexFormService());
        $result = $relationResolver->processField($fieldDefinition, $elementDefinition, $dummyRecord, 'tt_content');

        self::assertCount(2, $result);
        self::assertSame('Page 1', $result[0]['title']);
        self::assertSame('Page 2', $result[1]['title']);
    }

    /**
     * @test
     */
    public function canResolveFlexForm(): void
    {
        $tableDefinitionCollection = $this->get(LoaderFactory::class)->load();
        $tableDefinition = $tableDefinitionCollection->getTable('tt_content');
        $elementDefinition = $tableDefinition->getTypeDefinitionCollection()->getType('foo/bar');
        $fieldDefinition = $tableDefinition->getTcaColumnsDefinition()->getField('foo_bar_flexfield');
        $dummyRecord = [
            'uid' => 1,
            'foo_bar_flexfield' => '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>
<T3FlexForms>
    <data>
        <sheet index="sDEF">
            <language index="lDEF">
                <field index="header">
                    <value index="vDEF">Header in Flex</value>
                </field>
                <field index="textarea">
                    <value index="vDEF">Text in Flex</value>
                </field>
            </language>
        </sheet>
    </data>
</T3FlexForms>',
        ];

        $relationResolver = new RelationResolver($tableDefinitionCollection, new FlexFormService());
        $result = $relationResolver->processField($fieldDefinition, $elementDefinition, $dummyRecord, 'tt_content');

        self::assertSame('Header in Flex', $result['header']);
        self::assertSame('Text in Flex', $result['textarea']);
    }

    /**
     * @test
     */
    public function canResolveFlexFormWithSheetsOtherThanDefault(): void
    {
        $tableDefinitionCollection = $this->get(LoaderFactory::class)->load();
        $tableDefinition = $tableDefinitionCollection->getTable('tt_content');
        $elementDefinition = $tableDefinition->getTypeDefinitionCollection()->getType('foo/bar');
        $fieldDefinition = $tableDefinition->getTcaColumnsDefinition()->getField('foo_bar_flexfield');
        $dummyRecord = [
            'uid' => 1,
            'foo_bar_flexfield' => '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>
<T3FlexForms>
    <data>
        <sheet index="sheet1">
            <language index="lDEF">
                <field index="header">
                    <value index="vDEF">Header in Flex</value>
                </field>
                <field index="textarea">
                    <value index="vDEF">Text in Flex</value>
                </field>
            </language>
        </sheet>
        <sheet index="sheet2">
            <language index="lDEF">
                <field index="link">
                    <value index="vDEF">Link</value>
                </field>
                <field index="number">
                    <value index="vDEF">12</value>
                </field>
            </language>
        </sheet>
    </data>
</T3FlexForms>',
        ];

        $relationResolver = new RelationResolver($tableDefinitionCollection, new FlexFormService());
        $result = $relationResolver->processField($fieldDefinition, $elementDefinition, $dummyRecord, 'tt_content');

        self::assertSame('Header in Flex', $result['header']);
        self::assertSame('Text in Flex', $result['textarea']);
        self::assertSame('Link', $result['link']);
        self::assertSame('12', $result['number']);
    }
}
