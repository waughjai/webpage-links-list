<?php

use PHPUnit\Framework\TestCase;
use Unirest\Request;
use WaughJ\WebpageLinksList\WebpageLinksList;

class WebpageLinksListTest extends TestCase
{/*
	public function testRequestWorks()
	{
		$response = Request::get( 'https://www.google.com' );
		$this->assertContains( '<html itemscope="" itemtype="http://schema.org/WebPage" lang="en">', $response->body );
	}*/

	public function testBrokenPage()
	{
		$list = new WebpageLinksList( 'https://www.asfsafd.com' );
		$this->assertEquals( [], $list->getList() );
	}

	public function testLinksListRecursive()
	{
		$list = new WebpageLinksList( 'https://www.northwestgoldcoast.com/' );
		$this->assertContains( 'https://www.northwestgoldcoast.com/2009/02/26/best-bites-lives-up-to-its-name/', $list->getList() );
	}
}
