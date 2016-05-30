# Intro

Simple CMS builded upon sonata-admin bundle;
Main idea was to have an almost ready to use CMS.
I another words, with symfony you have bricks, with sonata-admin bundle you have walls,
and this cms will cover all the small things left behind

Main features:
- multi language cms with easy to use translation system;
- easy to implement complex catalogue system
- easy to extend cms


# Installation

Just a simple composer install:

```json
{
    "strix/sonata-cms-bundle": "dev-master"
}
```

```php
<?php
    new Strix\SonataCmsBundle\StrixSonataCmsBundle(),
```

the bundle itself does not require any other bundle so you can pick dependency versions yourself, but here's the adviced way:

### A2Lix translation bundle - needed by translations

a bit obsolete version that actually works with Stof's doctrine extensions

composer:

```json
{
    "a2lix/translation-form-bundle": "1.*@dev",
}
```

kernel:

```php
<?php
    new A2lix\TranslationFormBundle\A2lixTranslationFormBundle(),
```

override template for A2Lix form in your config.yml:

```yaml
a2lix_translation_form:
    locales: [en, ru, lv]
    default_required: true
    manager_registry: doctrine
    templating: "StrixSonataCmsBundle::a2lix_v1x.html.twig"
```

### Stof's doctrine extensions bundle - needed by almost everything

```json
{
        "stof/doctrine-extensions-bundle": "~1.1@dev"
}
```

kernel:

```php
<?php
    new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
```

**make sure** that Doctrine extensions are included **before** A2LixTranslations.

add mapping for Doctrine extensions in ```config.yml```:

```yaml
    orm:
        auto_generate_proxy_classes: "%kernel.debug%"
        #doctrine extensions
        entity_managers:
            default:
                auto_mapping: true
                mappings:
                    gedmo_translatable:
                        type: annotation
                        prefix: Gedmo\Translatable\Entity
                        dir: "%kernel.root_dir%/../vendor/gedmo/doctrine-extensions/lib/Gedmo/Translatable/Entity"
                        alias: GedmoTranslatable # this one is optional and will default to the name set for the mapping
                        is_bundle: false
                    gedmo_translator:
                        type: annotation
                        prefix: Gedmo\Translator\Entity
                        dir: "%kernel.root_dir%/../vendor/gedmo/doctrine-extensions/lib/Gedmo/Translator/Entity"
                        alias: GedmoTranslator # this one is optional and will default to the name set for the mapping
                        is_bundle: false
                    gedmo_loggable:
                        type: annotation
                        prefix: Gedmo\Loggable\Entity
                        dir: "%kernel.root_dir%/../vendor/gedmo/doctrine-extensions/lib/Gedmo/Loggable/Entity"
                        alias: GedmoLoggable # this one is optional and will default to the name set for the mapping
                        is_bundle: false
                    gedmo_tree:
                        type: annotation
                        prefix: Gedmo\Tree\Entity
                        dir: "%kernel.root_dir%/../vendor/gedmo/doctrine-extensions/lib/Gedmo/Tree/Entity"
                        alias: GedmoTree # this one is optional and will default to the name set for the mapping
                        is_bundle: false
```

configure Stof bundle:

```yaml
stof_doctrine_extensions:
    default_locale: en_US
    orm:
        default:
            tree: true
            translatable: true
            sluggable: true
```

# CMS

This section describes how to use this bundle as CMS system. Cool, yes?

### CMS Tree

This is the core and heart of a cms system. One category tree to rule them all - isn't it the dream of a developer? But first, let's dig into concepts:

* context. Contexts are defined separately. Possible contexts are "top menu", "left menu", "footer"... You got the point. You have to apply context to nodes to separate different category trees in your application
* slug. By default, slugs are translatable and form routes for your application
* templates and controllers. Each route has a controller and a template. You can add custom controllers later.
* content blocks. This is a basic textual block in a template - a text block, banners block...you got the point

Since it's very difficult to make translatable entities (that you can extend later ) using inheritance / mapped superclasses, so we've prepared entity stubs for you. This behavior, however, can change later, including generation of entities and / or required interfaces.

Main entity:

```php
<?php
TODO HERE
```

Translations:

```php
<?php
TODO HERE
```

Repository class:

```php
<?php
TODO HERE
```

Apart these entities you should extend AbstractTemplateEntity, AbstractControllerEntity, AbstractCmsContextEntity, and corresponding admins - see examples with languages below.

### Twig functions

TODO

### Languages

add your language entity:

```php
<?php
/**
 * @ORM\Entity(repositoryClass="Gedmo\Sortable\Entity\Repository\SortableRepository")
 * @ORM\Table(name="languages")
 */
class Language extends AbstractLanguageEntity
{
}
```

create admin for language entity:

```php
<?php
class LanguageAdmin extends AbstractLanguageAdmin
{
}
```

and configure the bundle to use this entity as language:

```yaml
strix_sonata_cms:
    language_entity: AwesomeSiteBundle:Language
```

that's all. Now StrixSonataCmsBundle knows where to get list of languages.

### AbstractStrixSonataCmsAdmin features

All instances need to have setContainer call. Sorry about that.

#### Handling Gedmo\Sortable fields

```php
<?php
class Admin extends AbstractStrixSonataCmsAdmin
{
    protected $sortableField = 'position';
    protected $isSortable = true;
    protected function configureListFields(ListMapper $list)
    {
        $this->addSortableControls($list);
    }
}
```

#### Handling boolean fields

```php
<?php
class Admin extends AbstractStrixSonataCmsAdmin
{
    protected function configureListFields(ListMapper $list)
    {
        $this->addBooleanControls($list, 'enabled');
    }
}
```

btw, you can make sure that only one entity can have 'on' value by passing additional argument to this call like

```php
<?php
$this->addBooleanControls($list, 'default', true);
```

nice way to handle 'default' fields, huh?

#### Current list of locales (for translations in admin)

enables you to get default language and current list of langauges, see section CMS -> Languages.

```php
<?php
    $this->getCmsLanguages($fetchDisabled = false); //retuns array of entities
    $this->getDefaultCmsLanguage(); //returns one entity or null
```

feel free to use it in your admin classes like:

```php
<?php
    ->add('translations', 'a2lix_translations_gedmo', array(
        'locales' => array_map(function ($obj) { return $obj->getCode(); }, $this->getCmsLanguages()),
```

# Trees

This bundle relies on nested set trees, so prepare your EntityRepository:

```php
<?php
class CategoryRepository extends NestedTreeRepository
{

}
```

prepare your entity:

```php
<?php
/**
 * @Gedmo\Tree(type="nested")
 * @ORM\Entity(repositoryClass="CategoryRepository")
 */
class Category extends AbstractStrixCmsTreeNode
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var integer
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=128)
     * @var string
     */
    protected $title;

    /**
    * @Gedmo\TreeLeft
    * @ORM\Column(type="integer", name="tree_left")
    */
    protected $left;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(type="integer", name="tree_right")
     */
    protected $right;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="HydroCategory", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $parent;

    /**
     * @Gedmo\TreeRoot
     * @ORM\Column(type="integer", nullable=true, name="tree_root")
     */
    protected $root;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="tree_level", type="integer")
     */
    protected $level;

    /**
     * @ORM\OneToMany(targetEntity="HydroCategory", mappedBy="parent")
     * @ORM\OrderBy({"left" = "ASC"})
     */
    protected $children;
```

Make sure you extend AbstractStrixCmsTreeNode and implement getTitleFieldName. Also, don't forget the ```@ORM\OrderBy({"left" = "ASC"})``` annotation for ```$children```.

Next, in your admin class, do:

```php
<?php
use Strix\SonataCmsBundle\Admin\AbstractTreeAdmin;

class TreeAdmin extends AbstractTreeAdmin
{
    protected $titleField = 'title';
}
```

huh. That was easy. Jus don't forget to add ```setContainer``` call to your admin definition so up/down methods are usable:

```yml
    awasome.tree.admin:
        class: Awesome\TreeAdmin
        tags:
            - { name: sonata.admin, manager_type: orm, group: "Awesome demo", label: "Tree" }
        arguments:
            - ~
            - Awesome\Entity\Category
            - ~
        calls:
            - [ setRequest, [ @service_container ] ]
```

# Translations

Translations in admin are based on StofDoctrineExtensionsBundle and A2LixTranslationFormBundle, a bit old, and not bleeding edge versions.

Add translations to admin like the following:

```php
<?php
    ->add('translations', 'a2lix_translations_gedmo', array(
        'fields' => array(
            'title' => array(
                'required' => false
            ),
            'subtitle' => array(
                'required' => false
            ),
            'description' => array(
                'required' => false,
                'attr' => array('style' => 'width: 800px; height: 100px;')
            ),
            'standardEquipment' => array(
                'required' => false,
                'attr' => array('style' => 'width: 800px; height: 300px;')
            )
        )
    ));
```

beware! for the translations to work you need to update objects in admin class pre-update and pre-persist like the following:

```php
<?php
    public function preUpdate($object)
    {
        foreach ($object->getTranslations() as $translation) {
            $translation->setObject($object);
    }
    public function prePersist($object)
    {
        foreach ($object->getTranslations() as $translation) {
            $translation->setObject($object);
    }
```

don't forget to do the same for embedded OneToMany collections!

at least, define your entities like this:

main entity:

```php
<?php
/**
 * @ORM\Entity
 * @Gedmo\TranslationEntity(class="ProductTranslation")
 */
class Product
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @var integer
     */
    protected $id;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="string", length=128, nullable=true)
     * @var string
     */
    protected $title;

    /**
     * @ORM\OneToMany(
     *   targetEntity="ProductTranslation",
     *   mappedBy="object",
     *   cascade={"persist", "remove"}
     * )
     */
    private $translations;
}
```

entity translation:

```php
<?php
/**
 * @ORM\Entity
 * @ORM\Table(name="product_translations",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="lookup_unique_idx", columns={
 *         "locale", "object_id", "field"
 *     })}
 * )
 */
class ProductTranslation extends AbstractPersonalTranslation
{
    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="translations")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $object;
}
```

**do not** add "convenient" constructor as Gedmo offers!