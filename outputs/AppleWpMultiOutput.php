<?php
namespace WpMultiOutput;

class AppleNews {
	function __construct($key, $secret) {

	}

	function sendArticle($data) {
		return true;
	}
}

class AppleWpMultiOutput extends WpMultiOutput {

	public $configuration;

	function __construct( $post = null )
	{
		$this->configuration = [
			'slug' => 'apple-news',
			'name' => 'Apple News',
			'description' => 'Converts a WordPress article into Apple News format.',
			'type' => 'push'
		];

		// Don't delete this!
    	parent::__construct( $post );
    }

	function prepare()
	{
		// Example: Setup post ready for Apple News
		$this->anf = [];
	}
	
	function publish()
	{
		// Setup our Apple News class
		$this->appleNews = new AppleNews($key, $secret);

		// Send our article to Apple News
		$this->output = $this->appleNews->sendArticle($this->appleNews);
	}
	
	function teardown()
	{
		// Add the response from Apple News as a meta value
		add_post_meta( $this->post->ID, 'wpmo-' . $this->configuration['slug'] . '-send', $this->output );
		return $this->output;
	}

}

$AppleWpMultiOutput = new AppleWpMultiOutput();