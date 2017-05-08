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
}