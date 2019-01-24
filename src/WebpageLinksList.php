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
		public function __construct( string $url )
		{
			$this->tried_list = [];
			$this->loops = 0;
			$this->list = [];
			$uri = new URI( $url );
			$this->generateListFromURL( $url, $uri->getHost() );
			foreach ( $this->list as $item )
			{
				//echo "{$item}\n";
			}
		}

		public function getList() : array
		{
			return $this->list;
		}

		private function generateListFromURL( string $url, string $host )
		{
			if ( count( $this->list ) < 100 && !in_array( $url, $this->tried_list ) )
			{
				echo "$url\n";
				try
				{
					$response = Request::get( $url );
					$data = new Crawler( $response->body );
					$this->tried_list[] = $url;
					$this->list[] = $url;
					$this->loops++;
					$data->filter( 'a' )->each
					(
						function( Crawler $node, $i )
						{
							if ( is_string( $node->attr( 'href' ) ) )
							{
								$link_uri = new URI( $node->attr( 'href' ) );
								if ( $host === $link_uri->getHost() )
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
									$this->generateListFromURL( $link_url, $host );
								}
							}
						}
					);
				}
				catch ( \Exception $e )
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
