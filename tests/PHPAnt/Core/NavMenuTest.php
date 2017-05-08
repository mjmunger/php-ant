<?php

namespace PHPAnt\Core;
$dependencies = [ 'tests/test_top.php'
                , 'includes/classes/NavMenu.class.php'
                , 'includes/classes/NavMenuItem.class.php'
                , 'includes/classes/NavMenuItemList.class.php'
                , 'includes/classes/NavMenuWriter.class.php'
                , 'includes/classes/HTMLMenuNode.class.php'
                , 'includes/classes/HTMLMenuLeaf.class.php'
                , 'includes/classes/HTMLMenuNodeFactory.class.php'
                ];

foreach($dependencies as $d) {
    require_once($d);
}

use PHPUnit\Framework\TestCase;
use PHPAnt\Core\NavMenuItemList;

//use PHPUnit\DbUnit\TestCaseTrait;
//use \PDO;

class NavMenuTest extends TestCase
{

    function testMenuItem() {
        $title = 'Title Text';
        $uri   = '/path/to/uri/';

        $options['uri'] = $uri;

        $Item = new NavMenuItem($title, $options);
        $this->assertSame($title, $Item->title);
        $this->assertSame($uri  , $Item->uri  );
    }

    function testAddMenuItem() {
        $title = 'Title Text';
        $uri   = '/path/to/uri/';

        $options['uri'] = $uri;

        $Item = new NavMenuItem($title, $options);

        $Menu = new NavMenu();
        $Menu->addMenuItem($Item);

        $this->assertSame($Menu->items->current(), $Item);
    }

    function testAddSubMenuItemToParent() {

        $title    = 'Root Title';
        $RootItem = new NavMenuItem($title);

        $Menu = new NavMenu();
        $Menu->addMenuItem($RootItem);

        $this->assertCount(1, $Menu->items);

        $options= ['uri' => '/path/to/sub/1'];
        $SubItem1 = new NavMenuItem('Sub1', $options);

        $options= ['uri' => '/path/to/sub/2'];
        $SubItem2 = new NavMenuItem('Sub2', $options);

        $Menu->addMenuItem($SubItem1,$RootItem);
        $Menu->addMenuItem($SubItem2,$RootItem);

        $RootMenuItem = $Menu->items->getItem("Root Title");
        $this->assertInstanceOf("PHPAnt\Core\NavMenuItem", $RootItem);
        $this->assertTrue($RootMenuItem->hasChildren());
        $items = $RootMenuItem->getChildren();
        $this->assertInstanceOf('PHPAnt\Core\NavMenuItemList', $items);

        
        $this->assertCount(1, $Menu->items);
        $this->assertInstanceOf('\Traversable', $Menu->items);
        $this->assertInstanceOf('PHPAnt\Core\NavMenuItemList', $Menu->items);

        $pathArray = ['root-title'];

        $SubMenu = $Menu->getMenuItemByPath($pathArray);

        $this->assertNotNull($SubMenu);
        $this->assertSame('Root Title', $SubMenu->title);

        //Let's try again with deeper menus.
        $pathArray = ['root-title','sub1'];

        $SubMenu = $Menu->getMenuItemByPath($pathArray);

        $this->assertNotNull($SubMenu);
        $this->assertSame('Sub1', $SubMenu->title);
        $this->assertSame('/path/to/sub/1',$SubMenu->uri);

        //Let's try again with other menu.
        $pathArray = ['root-title','sub2'];

        $SubMenu = $Menu->getMenuItemByPath($pathArray);

        $this->assertNotNull($SubMenu);
        $this->assertSame('Sub2', $SubMenu->title);
        $this->assertSame('/path/to/sub/2',$SubMenu->uri);

        //Let's look for something that doesn't exist so we can fail.

        $pathArray = ['root-title','sub2','doesnotexist'];

        $SubMenu = $Menu->getMenuItemByPath($pathArray);

        $this->assertNotNull($SubMenu);
        $this->assertFalse($SubMenu);
    }

    function testMenuWriterConstruct() {
        $Menu = new NavMenu();
        $Writer = new NavMenuWriter($Menu);

        $this->assertEquals($Menu->items, $Writer->items);
        
    }

    function testHTMLMenuNode() {
        $Menu = new NavMenu();

        $options         = [];
        $options['uri']  = "/top1/";
        $options['slug'] = 'top1';

        $Top1 = new NavMenuItem('Top1',$options);

        $Node = new HTMLMenuNode($Top1);

        $this->assertSame('Top1', $Node->title);
        $this->assertSame($options['slug'], $Node->slug);
        $this->assertSame($options['uri'], $Node->uri);
    }


    function testHTMLMenuLeaf() {
        $Menu = new NavMenu();

        $options         = [];
        $options['uri']  = "/top1/";
        $options['slug'] = 'top1';

        $Top1 = new NavMenuItem('Top1',$options);

        $Node = new HTMLMenuLeaf($Top1);

        $this->assertSame('Top1', $Node->title);
        $this->assertSame($options['slug'], $Node->slug);
        $this->assertSame($options['uri'], $Node->uri);

        $html = '<a href="/top1/" class="w3-bar-item w3-button ant-leaf">Top1</a>' . PHP_EOL;
        $this->assertSame($html,$Node->getHTML());
    }

    function testHTMLMenuBranch() {
        $options         = [];
        $options['uri']  = "/top1/";
        $options['slug'] = 'top1';

        $Top1 = new NavMenuItem('Top1',$options);

        for($x = 1; $x<=3; $x++) {
            $options= ['uri' => '/path/to/sub/' . $x];
            $SubItem = new NavMenuItem('Sub '. $x, $options);
            $Top1->addMenuItem($SubItem,$Top1);
        }

        $this->assertCount(3, $Top1->childItems);

        $Branch = new HTMLMenuBranch($Top1);

        $this->assertInstanceOf("PHPAnt\\Core\\HTMLMenuBranch", $Branch);
        $this->assertCount(3, $Branch->children);

        $html = <<<EOF
<div class="w3-dropdown-hover">
    <button class="w3-button">Top1</button>
    <div class="w3-dropdown-content w3-bar-block w3-card-4">
<a href="/path/to/sub/1" class="w3-bar-item w3-button ant-leaf">Sub 1</a>
<a href="/path/to/sub/2" class="w3-bar-item w3-button ant-leaf">Sub 2</a>
<a href="/path/to/sub/3" class="w3-bar-item w3-button ant-leaf">Sub 3</a>

    </div>
</div>
EOF;

        $this->assertSame($html,$Branch->getHTML());
    }


//     function testMenuItemTopHTML() {
//         $Menu = new NavMenu();

//         $options         = [];
//         $options['uri']  = '/top2/';
//         $options['slug'] = 'top1';

//         $Top1 = new NavMenuItem('Top1',$options);

//         $options         = [];
//         $options['uri']  = '/top2/';
//         $options['slug'] = 'top2';

//         $Top2 = new NavMenuItem('Top2',$options);

//         $Menu->addMenuItem($Top1);
//         $Menu->addMenuItem($Top2);

//         $Writer = new NavMenuWriter($Menu);

//         $html = <<<EOF
// <nav>

// </nav>
// EOF;
//         $this->assertSame($html, $Writer->getHTML());
//     }
}