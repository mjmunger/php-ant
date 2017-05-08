<?php

namespace PHPAnt\Core;
$dependencies = [ 'tests/test_top.php'
                , 'includes/classes/NavMenu.class.php'
                , 'includes/classes/NavMenuItem.class.php'
                , 'includes/classes/NavMenuItemList.class.php'
                ];

foreach($dependencies as $d) {
    require_once($d);
}

use PHPUnit\Framework\TestCase;
use PHPAnt\Core\NavMenuItemList;

//use PHPUnit\DbUnit\TestCaseTrait;
//use \PDO;

class NavMenutest extends TestCase
{

    function testMenuItem() {
        $title = 'Title Text';
        $uri   = '/path/to/uri/';

        $Item = new NavMenuItem($title, $uri);
        $this->assertSame($title, $Item->title);
        $this->assertSame($uri  , $Item->uri  );
    }

    function testAddMenuItem() {
        $title = 'Title Text';
        $uri   = '/path/to/uri/';
        $Item = new NavMenuItem($title, $uri);

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

        $SubItem1 = new NavMenuItem('Sub1', '/path/to/sub/1');
        $SubItem2 = new NavMenuItem('Sub2', '/path/to/sub/2');

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

        $pathArray = ['Root Title'];

        $SubMenu = $Menu->getMenuItemByPath($pathArray);

        $this->assertNotNull($SubMenu);
        $this->assertSame('Root Title', $SubMenu->title);

        //Let's try again with deeper menus.
        $pathArray = ['Root Title','Sub1'];

        $SubMenu = $Menu->getMenuItemByPath($pathArray);

        $this->assertNotNull($SubMenu);
        $this->assertSame('Sub1', $SubMenu->title);
        $this->assertSame('/path/to/sub/1',$SubMenu->uri);

        //Let's try again with other menu.
        $pathArray = ['Root Title','Sub2'];

        $SubMenu = $Menu->getMenuItemByPath($pathArray);

        $this->assertNotNull($SubMenu);
        $this->assertSame('Sub2', $SubMenu->title);
        $this->assertSame('/path/to/sub/2',$SubMenu->uri);
    }
}