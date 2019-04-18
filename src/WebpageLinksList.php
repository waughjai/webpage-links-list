<?php

declare( strict_types = 1 );
namespace WaughJ\WebpageLinksList
{
	use Enrise\Uri;
	use Symfony\Component\DomCrawler\Crawler;
	use Unirest\Request;
	use WaughJ\Directory\Directory;

	class WebpageLinksList
	{
		public function __construct( string $url, int $limit = -1 )
		{
			$this->tried_list = [];
			$this->list = [];
			$this->list_count = 0;
			$this->limit = $limit;
			$uri = new URI( $url );
			$this->generateListFromURL( $url, $uri->getHost() );
		}

		public function getList() : array
		{
			return $this->list;
		}

		private function tryURL( string $url, string $host ) : void
		{
			$url = $this->removeFragmentFromURL( $url );
			if ( $this->testHasReachedLimit() && !in_array( $url, $this->tried_list ) )
			{
				$this->tried_list[] = $url;
				$this->list[] = $url;
				$this->list_count++;
				$this->generateListFromURL( $url, $host );
			}
		}

		private function generateListFromURL( string $url, string $host ) : void
		{
			try
			{
				$response = Request::get( $url );
				$data = new Crawler( $response->body );
				$data->filter( 'a' )->each
				(
					function( Crawler $node, $i ) use ( $host )
					{
						if ( is_string( $node->attr( 'href' ) ) )
						{
							$link_uri = new URI( $node->attr( 'href' ) );
							if ( $link_uri->getHost() && $this->testHostsMatch( $host, $link_uri->getHost() ) )
							{
								if ( $link_uri->isRelative() )
								{
									$parent = new Directory( $url );
									$link_url = new Directory([ $parent->getString([ 'ending-slash' => true, 'starting-slash' => false ]), $node->attr( 'href' ) ]);
									$link_url = $link_url->getString([ 'ending-slash' => false, 'starting-slash' => false ]);
								}
								else
								{
									$link_url = $node->attr( 'href' );
								}
								$this->tryURL( $link_url, $host );
							}
						}
					}
				);
			}
			catch ( \Exception $e )
			{
				// We should just ignore broken links & carry on.
				return;
			}
		}

		private function testHostsMatch( string $host1, string $host2 ) : bool
		{
			return $host1 === $host2 || "www.{$host1}" === $host2 || $host1 === "www.{$host2}";
		}

		private function removeFragmentFromURL( string $url ) : string
		{
			$uri = new URI( $url );
			$uri->setFragment( '' );
			return $uri->getUri();
		}

		private function testHasReachedLimit() : bool
		{
			return $this->limit === -1 || $this->list_count < $this->limit;
		}

		private $list;
		private $tried_list;
		private $list_count;
	}
}
