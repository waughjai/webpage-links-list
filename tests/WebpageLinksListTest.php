<?php

use PHPUnit\Framework\TestCase;
use Unirest\Request;
use WaughJ\WebpageLinksList\WebpageLinksList;

class WebpageLinksListTest extends TestCase
{
	public function testRequestWorks()
	{
		$response = Request::get( 'https://www.google.com' );
		$this->assertContains( '<html itemscope="" itemtype="http://schema.org/WebPage" lang="en">', $response->body );
	}

	public function testBrokenPage()
	{
		$list = new WebpageLinksList( 'https://www.asfsafd.com' );
		$this->assertEquals( [], $list->getList() );
	}

	public function testLinksList()
	{
		$list = new WebpageLinksList( 'https://www.4cesi.com/' );
		$this->assertContains( 'https://www.4cesi.com/services/', $list->getList() );
	}

	public function testLinksListRecursive()
	{
		$list = new WebpageLinksList( 'https://www.4cesi.com/' );
		$this->assertContains( 'https://www.4cesi.com/6-date-ideas-around-seattle-this-valentines-day/', $list->getList() );
	}
}
