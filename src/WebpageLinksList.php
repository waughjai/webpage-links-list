<?php

declare( strict_types = 1 );
namespace WaughJ\WebpageLinksList
{
	use Enrise\Uri;
	use Symfony\Component\DomCrawler\Crawler;
	use Unirest\Request;
	use Unirest\Response;
	use WaughJ\Directory\Directory;

	class WebpageLinksList
	{
		public function __construct( string $url, int $limit = -1 )
		{
			$this->tried_list = [];
			$this->list = [];
			$this->data = [];
			$this->list_count = 0;
			$this->limit = $limit;
			$this->url = $url;
		}

		public function getList() : array
		{
			if ( empty( $this->list ) )
			{
				$this->generateList();
			}
			return $this->list;
		}

		public function getData() : array
		{
			if ( empty( $this->data ) )
			{
				$this->generateList();
			}
			return $this->data;
		}

		private function generateList() : void
		{
			$uri = new URI( $this->url );
			if ( !$uri->getScheme() )
			{
				$uri->setScheme( 'https' );
			}
			$this->url = $uri->getUri();
			if ( !$uri->getHost() )
			{
				$uri->setHost( $this->url );
			}
			try
			{
				$response = Request::get( $this->url );
				$this->generateListFromURL( $uri, $response );
			}
			catch ( \Unirest\Exception $e )
			{
				// Do nothing on error.
			}
		}

		private function tryURL( string $url, URI $host ) : void
		{
			$response = null;
			$url = $this->removeFragmentFromURL( $url );
			if ( $this->testHasReachedLimit() || in_array( $url, $this->tried_list ) )
			{
				return;
			}

			$this->tried_list[] = $url;
			$uri = new URI( $url );
			if ( !$uri->getScheme() )
			{
				$uri->setScheme( $host->getScheme() );
			}
			$url = $uri->getUri();
			try
			{
				$response = Request::get( $url );
				if ( $this->testResponseNotHTML( $response ) )
				{
					return;
				}
				$this->list[] = $url;
				$this->data[ $url ] = $response;
				$this->list_count++;
				$this->generateListFromURL( $host, $response );
			}
			catch ( \Exception $e )
			{
				// We should just ignore broken links & carry on.
				return;
			}
		}

		private function generateListFromURL( URI $host, Response $response ) : void
		{
			$data = new Crawler( $response->body );
			$data->filter( 'a' )->each
			(
				function( Crawler $node, $i ) use ( $host )
				{
					$href = $node->attr( 'href' );
					if ( !is_string( $href ) )
					{
						// Invalid href; ignore
						return;
					}

					$link_uri = new URI( $href );
					if ( $link_uri->isRelative() )
					{
						$parent = new Directory( $this->getRoot( $host ) );
						$link_url = new Directory([ $parent, $node->attr( 'href' ) ]);
						$link_url = $link_url->getStringURL();
						$this->tryURL( $link_url, $host );
					}
					else if ( $link_uri->getHost() && $this->testHostsMatch( $host, $link_uri ) )
					{
						$link_url = $node->attr( 'href' );
						$this->tryURL( $link_url, $host );
					}
					else
					{
						// Ignore this link.
						return;
					}
				}
			);
		}

		private function testHostsMatch( URI $root1, URI $root2 ) : bool
		{
			$host1 = $root1->getHost();
			$host2 = $root2->getHost();
			return $host1 === $host2 ||
				"www.{$host1}" === $host2 ||
				$host1 === "www.{$host2}";
		}

		private function getRoot( URI $root ) : string
		{
			return $root->getScheme() . '://' . $root->getHost();
		}

		private function removeFragmentFromURL( string $url ) : string
		{
			$uri = new URI( $url );
			$uri->setFragment( '' );
			return $uri->getUri();
		}

		private function testHasReachedLimit() : bool
		{
			return $this->limit !== -1 && $this->list_count >= $this->limit;
		}

		private function testResponseNotHTML( Response $response ) : bool
		{
			$content = $response->headers[ 'Content-Type' ];
			if ( is_array( $content ) )
			{
				foreach ( $content as $i )
				{
					if ( strpos( $i, 'text/html' ) !== false )
					{
						return false;
					}
				}
			}
			else if ( is_string( $content ) )
			{
				return strpos( $content, 'text/html' ) === false;
			}
			return true;
		}

		private $url;
		private $list;
		private $data;
		private $tried_list;
		private $list_count;
	}
}
