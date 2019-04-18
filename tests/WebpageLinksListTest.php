<?php

use PHPUnit\Framework\TestCase;
use Unirest\Request;
use WaughJ\WebpageLinksList\WebpageLinksList;

class WebpageLinksListTest extends TestCase
{
	/*
	public function testBrokenPage()
	{
		$list = new WebpageLinksList( 'https://www.asfsafd.com' );
		$this->assertEquals( [], $list->getList() );
	}

	public function testLinksListRecursive()
	{
		$list = new WebpageLinksList( 'https://www.jaimeson-waugh.com/' );
		$this->assertContains( 'https://jaimeson-waugh.com/mega-microstories/', $list->getList() );
		$this->assertContains( 'https://jaimeson-waugh.com/boskeopolis-land/', $list->getList() );
	}
	*/

	public function testLinksWithoutImages()
	{
		$list = new WebpageLinksList( 'https://en.wikipedia.org/wiki/PHP', 100 );
		var_dump( $list->getList() );
		$this->assertNotContains( '.svg', $list->getList() );
	}
}
