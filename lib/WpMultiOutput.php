<?php
namespace WpMultiOutput;

/**
 *
 */
abstract class WpMultiOutput {

	function __construct( $post ) {
		// Register the output
		add_filter( 'wpmo/registeredOutputs', [ $this, 'registerOutput' ] );

		$this->configuration['class'] = get_class( $this );
		$this->configuration['action'] = (isset( $this->configuration['action'] ) ? $this->configuration['action'] : 'Publish to ' . $this->configuration['name']);

		$this->post = $post;
	}

	public function registerOutput( $outputs )
	{	
		$outputs[ $this->configuration['slug'] ] = $this->configuration;

		return $outputs;
	}

	public function run()
	{
		$this->prepare();

		$this->publish();

		return $this->teardown();
	}

	abstract protected function prepare();
	
	abstract protected function publish();
	
	abstract protected function teardown();

}