<?php

namespace WpMultiOutput;

class JsonWpMultiOutput extends WpMultiOutput {

	public $configuration;

	function __construct( $post = null )
	{
		// Configure the output
		$this->configuration = [
			'slug' => 'json',
			'name' => 'JSON',
			'description' => 'Converts a WordPress article into Apple News format.',
			'type' => 'output'
		];

		// Don't delete this!
    	parent::__construct( $post );
    }

	function prepare()
	{
		// Set the JSON header ready for output
		header('Content-Type: application/json');
	}
	
	function publish()
	{
		// Process our json
		$this->json = json_encode( $this->post );
	}
	
	function teardown()
	{
		// Return the JSON
		return $this->json;
	}

}

$JsonWpMultiOutput = new JsonWpMultiOutput();