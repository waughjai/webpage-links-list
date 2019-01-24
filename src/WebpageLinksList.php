<?php

declare( strict_types = 1 );
namespace WaughJ\WebpageLinksList
{
	use Symfony\Component\DomCrawler\Crawler;
	use Unirest\Request;

	class WebpageLinksList
	{
		public function __construct( string $url )
		{
			$this->tried_list = [];
			$this->loops = 0;
			$this->list = [];
			$this->generateListFromURL( $url );
		}

		public function getList() : array
		{
			return $this->list;
		}

		private function generateListFromURL( string $url )
		{
			if ( !in_array( $url, $this->tried_list ) )
			{
				try
				{
					$response = Request::get( $url );
					$data = new Crawler( $response->body );
					$this->tried_list[] = $url;
					$data->filter( 'a' )->each
					(
						function( Crawler $node, $i )
						{
							$this->list[] = $node->attr( 'href' );
							$this->generateListFromURL( $node->attr( 'href' ) );
						}
					);
				}
				catch ( \Unirest\Exception $e )
				{
				}
			}
			else
			{
			}
		}

		private $list;
		private $tried_list;
		private $loops;
	}
}
