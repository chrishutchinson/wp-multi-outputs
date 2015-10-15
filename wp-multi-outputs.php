<?php
namespace WpMultiOutput;
/*
Plugin Name: WP Multi-Outputs
Plugin URI:  http://www.github.com/chrishutchinson/wp-multi-outputs
Description: Framework for supporting multiple content outputs
Version:     1.0.0
Author:      Chris Hutchinson
Author URI:  http://www.github.com/chrishutchinson
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wp-multi-outputs
*/

include 'lib/WpMultiOutput.php';

class WpMultiOutputs {

  protected $outputs = [];  

  /**
   * Constructor
   *
   * @return void
   *
   * @since 1.0.0
   *
   * @author Chris Hutchinson <chris_hutchinson@me.com>
   */
  function __construct()
  {
    $this->plugin = new \stdClass;
    $this->plugin->name = 'WP Multi-Outputs';
    $this->plugin->version = '0.0.1';
    $this->plugin->folder = 'wp-multi-putputs';
    $this->plugin->url = plugin_dir_url( __FILE__ ); // Has trailing slash
    $this->plugin->path = plugin_dir_path( __FILE__ ); // Has trailing slash

    // Actions
    add_action( 'plugins_loaded', [ $this, 'afterPluginsLoaded' ] );
    add_action( 'admin_action_wpmo_publish', [ $this, 'triggerPublish' ] );
    add_action( 'admin_notices', [ $this, 'registerAdminNotices' ] );

    // Filters
    add_filter( 'post_row_actions', [ $this, 'postRowActions' ], 10, 2 );
  }

  /**
   * Runs after all plugins are loaded, registers all core files and our bundled outputs
   *
   * @return void
   *
   * @since 1.0.0
   *
   * @author Chris Hutchinson <chris_hutchinson@me.com>
   */
  public function afterPluginsLoaded()
  {
    // Include core files and bundled outputs
    include( $this->plugin->path . 'outputs/AppleWpMultiOutput.php' );
    include( $this->plugin->path . 'outputs/JsonWpMultiOutput.php' );

    // Dump outputs if requested
    if( isset ($_GET['outputs'] ) ) {
      $outputs = $this->getOutputs();
      dd($outputs);
    }
  }

  /**
   * Registers admin notices for errors and success messages
   *
   * @return void
   *
   * @since 1.0.0
   *
   * @author Chris Hutchinson <chris_hutchinson@me.com>
   */
  public function registerAdminNotices()
  {
    if( isset ( $_REQUEST['wpmoErr'] ) ) {
      switch( $_REQUEST['wpmoErr'] ) {
        case '1':
        case 1:
          ?>
          <div class="error">
            <p><strong>WP Multi-Outputs:</strong> No output type specified.</p>
          </div>
          <?php
          break;
        case '2':
        case 2:
          ?>
          <div class="error">
            <p><strong>WP Multi-Outputs:</strong> Specified output is not registered.</p>
          </div>
          <?php
          break;
        case '3':
        case 3:
        case '4':
        case 4:
          ?>
          <div class="error">
            <p><strong>WP Multi-Outputs:</strong> An invalid post was supplied.</p>
          </div>
          <?php
          break;
        case '5':
        case 5:
          ?>
          <div class="error">
            <p><strong>WP Multi-Outputs:</strong> Sorry, you can't do that.</p>
          </div>
          <?php
          break;
        default:
          ?>
          <div class="error">
            <p><strong>WP Multi-Outputs:</strong> An unknown error occurred. Please try again.</p>
          </div>
          <?php
          break;
      }
    } elseif( isset ( $_REQUEST['wpmoComplete'] ) ) {
      ?>
      <div class="updated">
        <p><strong>WP Multi-Outputs:</strong> <?php echo $_REQUEST['wpmoComplete']; ?> published succesfully.</p>
      </div>
      <?php
    }
  }

  /**
   * Triggers the publish chain on any registered output
   *
   * @return void
   *
   * @since 1.0.0
   *
   * @author Chris Hutchinson <chris_hutchinson@me.com>
   */
  public function triggerPublish()
  {
    // Can the user publish posts?
    if( !current_user_can( 'publish_posts' ) ) {
      wp_redirect( admin_url( 'edit.php?wpmoErr=5' ) );
      return;
    }

    // Were we supplied an output name?
    if( !isset( $_REQUEST['output'] ) ) {
      wp_redirect( admin_url( 'edit.php?wpmoErr=1' ) );
      return;
    }

    // Were we supplied a post?
    if( !isset( $_REQUEST['post'] ) ) {
      wp_redirect( admin_url( 'edit.php?wpmoErr=3' ) );
      return;
    }

    // Setup the output
    $output = $this->getOutput( $_REQUEST['output'] );

    // Setup the post
    $post = $this->preparePost( $_REQUEST['post'] );

    // Initialise the output class
    $outputClass = new $output['class']( $post );

    // Run the process for this output and grab any response
    $response = $outputClass->run();

    switch( $output['type'] ) {
      case 'render':
        return wp_redirect( $response );
        break;
      case 'output':
        echo $response;
        die();
        break;
      case 'push':
      default:
        // Return with success message
        wp_redirect( admin_url( 'edit.php?wpmoComplete=' . urlencode( $output['name'] ) ) );
        break;
    }

    return;
  }

  /**
   * Adds our post row actions so outputs can be triggered
   * TODO: Figure out a different way of showing these, this will be a mess with > 2 outputs
   *
   * @param array   $actions  The registered post row actions
   * @param object  $post     The current WordPress post
   *
   * @return array  $actions  The registered post row actions 
   *
   * @since 1.0.0
   *
   * @author Chris Hutchinson <chris_hutchinson@me.com>
   */
  public function postRowActions( $actions, $post )
  {
    // Get the outputs
    $outputs = $this->getOutputs();

    // Loop over the outputs
    foreach($outputs as $slug => $output) {
      // Setup actions for each output
      $actions[ $slug ] = '<a href="' . admin_url( 'admin.php' ) . '?action=wpmo_publish&output=' . $slug . '&post=' . $post->ID . '">' . $output['action'] . '</a>';
    }

    // Return actions
    return $actions;
  }

  /**
   * Prepares a post for usage, gets and errors if invalid
   *
   * @since 1.0.0
   *
   * @author Chris Hutchinson <chris_hutchinson@me.com>
   */
  private function preparePost( $postId )
  { 
    // Get the post
    $post = get_post( $postId );

    // Is this a valid post?
    if( !$post ) {
      // Redirect if not
      return wp_redirect( admin_url( 'edit.php?wpmoErr=4' ) );
    }

    // Return the post
    return $post;
  }

  /**
   * Gets all the outputs registered
   *
   * @return array  $outputs  The registered outputs
   *
   * @since 1.0.0
   *
   * @author Chris Hutchinson <chris_hutchinson@me.com>
   */
  private function getOutputs()
  {
    // Run the filter to get the registered outputs (includes those registered in plugins)
    $outputs = apply_filters( 'wpmo/registeredOutputs', $this->outputs );

    // Return the outputs
    return $outputs;
  }

  /**
   * Gets a specific output, and redirects if invalid
   *
   * @param string  $name   The slug of the required output
   *
   * @return array  $output  The specified output
   *
   * @since 1.0.0
   *
   * @author Chris Hutchinson <chris_hutchinson@me.com>
   */
  private function getOutput( $name )
  {
    // Get the outputs
    $outputs = $this->getOutputs();

    // Was this a valid output?
    if( ! isset( $outputs[ $name ] ) ) {
      return wp_redirect( admin_url( 'edit.php?wpmoErr=2' ) );
    }

    // Return the specific output
    return $outputs[ $name ];
  }

}


// Initialise the plugin
$WpMultiOutputs = new WpMultiOutputs;


// Die and dump, here for debugging
if( ! function_exists( 'dd' ) ) {
  function dd($data) {
    if( WP_DEBUG ) {
      echo '<pre>';
      print_r($data);
      die();
    }
  }
}